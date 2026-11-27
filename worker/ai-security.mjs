/**
 * WhatsGateway AI Security & Guardrails Layer
 * -------------------------------------------
 * Provides enterprise-grade input/output security, prompt injection defenses,
 * caller ACL verification, and secret data scrubbing for all AI engines.
 */

// Common prompt injection, jailbreak, and system prompt exfiltration signatures
const INJECTION_PATTERNS = [
  /\b(?:ignore|disregard|forget|override)\s+(?:all\s+)?(?:previous|prior|above|system)\s+(?:instructions|rules|prompts|commands|directives)\b/i,
  /\b(?:reveal|print|output|display|show|leak|echo)\s+(?:your\s+)?(?:full\s+)?(?:system|initial|base|internal|developer)\s+(?:prompt|instructions|rules)\b/i,
  /\byou\s+are\s+now\s+(?:dan|developer\s+mode|unfiltered|jailbroken|unchained|unrestricted)\b/i,
  /\bstart\s+acting\s+as\s+(?:an?\s+unrestricted|an?\s+evil|dan)\b/i,
  /\bwhat\s+(?:is|was)\s+(?:the|your)\s+(?:system\s+prompt|initial\s+prompt|hidden\s+instruction)\b/i,
  /\bshow\s+me\s+the\s+instructions\s+you\s+were\s+given\b/i
];

// Patterns for sensitive secrets that must never appear in bot responses
const SECRET_PATTERNS = [
  /AIzaSy[A-Za-z0-9_-]{33}/g,                   // Google Gemini API key
  /gsk_[A-Za-z0-9]{48,}/g,                      // Groq API key
  /sk-or-[A-Za-z0-9]{48,}/g,                    // OpenRouter API key
  /sk-[A-Za-z0-9]{32,}/g,                       // OpenAI / Generic secret key
  /enc:v1:[A-Za-z0-9+/=]+/g,                     // Internal encrypted credential
  /(?:password|api_key|secret_key|system_token)\s*[:=]\s*['"][^'"]+['"]/gi
];

/**
 * Sanitize and truncate raw input text from WhatsApp
 * @param {string} text 
 * @param {number} maxLen 
 * @returns {string}
 */
export function sanitizeInput(text, maxLen = 2500) {
  if (!text || typeof text !== 'string') return '';
  
  // Strip control chars except newlines and tabs
  let sanitized = text.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
  
  if (sanitized.length > maxLen) {
    sanitized = sanitized.slice(0, maxLen);
  }
  return sanitized.trim();
}

/**
 * Check if the input message attempts prompt injection or jailbreak
 * @param {string} text 
 * @returns {{ isJailbreak: boolean, reason?: string }}
 */
export function detectJailbreakOrInjection(text) {
  if (!text || typeof text !== 'string') return { isJailbreak: false };

  for (const pattern of INJECTION_PATTERNS) {
    if (pattern.test(text)) {
      return {
        isJailbreak: true,
        reason: 'Detected unauthorized prompt override or system instruction exfiltration attempt.'
      };
    }
  }

  return { isJailbreak: false };
}

/**
 * Normalize a phone number to canonical digits-only format
 * @param {string} phone 
 * @returns {string}
 */
export function normalizePhone(phone) {
  if (!phone || typeof phone !== 'string') return '';
  return phone.replace(/[^\d]/g, '');
}

/**
 * Verify whether caller phone number has admin privileges
 * @param {string} senderPhone 
 * @param {Array<string>} adminPhones 
 * @returns {{ isAdmin: boolean, role: 'admin' | 'guest' }}
 */
export function verifyCallerRole(senderPhone, adminPhones = []) {
  const cleanSender = normalizePhone(senderPhone);
  if (!cleanSender) return { isAdmin: false, role: 'guest' };

  if (Array.isArray(adminPhones) && adminPhones.length > 0) {
    for (const admin of adminPhones) {
      const cleanAdmin = normalizePhone(admin);
      if (cleanAdmin && (cleanSender === cleanAdmin || cleanSender.slice(-10) === cleanAdmin.slice(-10))) {
        return { isAdmin: true, role: 'admin' };
      }
    }
  }

  return { isAdmin: false, role: 'guest' };
}

/**
 * Scrub API keys, passwords, and sensitive tokens from outgoing LLM responses
 * @param {string} responseText 
 * @returns {string}
 */
export function scrubSecrets(responseText) {
  if (!responseText || typeof responseText !== 'string') return '';

  let cleaned = responseText;
  for (const pattern of SECRET_PATTERNS) {
    cleaned = cleaned.replace(pattern, '[PROTECTED_CREDENTIAL]');
  }

  return cleaned;
}

/**
 * Enforce tool execution ACL permissions
 * External guest callers are forbidden from executing mutating/administrative tools.
 * @param {string} toolSlug 
 * @param {'admin'|'guest'|'customer'} callerRole 
 * @returns {{ allowed: boolean, reason?: string }}
 */
export function validateToolAcl(toolSlug, callerRole) {
  // Public/read-only tools that any caller may run if connected
  const GUEST_ALLOWED_TOOLS = new Set([
    'check_availability',
    'search_catalog',
    'get_business_hours',
    'track_order'
  ]);

  if (callerRole === 'admin') {
    return { allowed: true };
  }

  if (GUEST_ALLOWED_TOOLS.has(toolSlug)) {
    return { allowed: true };
  }

  return {
    allowed: false,
    reason: `Access Denied: Caller with role "${callerRole}" is not permitted to execute tool "${toolSlug}".`
  };
}
