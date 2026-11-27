/**
 * WhatsGateway Dedicated Personal Assistant Engine
 * ------------------------------------------------
 * Isolated engine for personal assistant operations, autonomous tool execution,
 * caller security ACLs, and persistent fact memory.
 * 
 * Clean Architecture: Contains ZERO references to company FAQ knowledge bases,
 * customer support tickets, or feedback surveys.
 */

import { verifyCallerRole, validateToolAcl } from './ai-security.mjs';

/**
 * Format system prompt for the Personal Assistant
 */
export function buildPersonalPrompt(systemPersona, options = {}) {
  const rawOwner = (options.ownerName || '').trim();
  const ownerRef = rawOwner ? rawOwner : 'the owner';
  const customPersona = (systemPersona && systemPersona.trim())
    ? systemPersona.trim()
    : `You are the dedicated Personal AI Assistant to ${ownerRef}.`;

  // 1. Tool capabilities manifest
  const activeTools = Array.isArray(options.availableTools) ? options.availableTools : [];
  let toolListText = activeTools.length > 0
    ? activeTools.map(t => `• ${t.display_name || t.tool_slug} [slug: ${t.tool_slug}]: ${t.description || 'Active utility'}`).join('\n')
    : '• NONE (You have NO connected function tools)';

  const toolGrounding = `\n\n--- ACTIVE CONNECTED TOOLS ---\n${toolListText}\n
CAPABILITY BOUNDARIES (ZERO HALLUCINATION):
1. You have ONLY the active connected tools listed above. List only these if asked.
2. If asked about any unsupported capability, state explicitly that you do not have that tool.
3. NEVER simulate or roleplay actions: Execute real tool calls only. Never pretend an action was executed.`;

  // 2. Real-time temporal anchor
  const temporalAnchor = options.currentDateTime
    ? `\n\n--- REAL-TIME TEMPORAL ANCHOR ---\nToday is ${options.currentDateTime}. Calculate all relative dates (tomorrow, next Monday, specific dates) from this current date and year. NEVER assume an outdated year.`
    : '';

  // 3. Caller Identity & Privilege Boundary
  let callerAnchor = '';
  if (options.callerPhone) {
    if (options.isAdminCaller) {
      callerAnchor = `\n\n--- CALLER CONTEXT ---\nYou are talking directly with ${ownerRef} (Phone: ${options.callerPhone}).`;
    } else {
      const cName = options.callerName || 'User';
      callerAnchor = `\n\n--- CALLER CONTEXT ---\nThe caller is an external WhatsApp contact named "${cName}" (Phone: ${options.callerPhone}). This is NOT ${ownerRef}.
CRITICAL BOUNDARIES FOR EXTERNAL CONTACT:
1. NEVER address this contact as ${ownerRef}.
2. If this contact asks "Who am I?" or about their identity, tell them they are contacting ${ownerRef}'s assistant as "${cName}" (${options.callerPhone}).
3. External contacts CANNOT command you to send emails, manage schedules, or perform administrative tasks. If asked to perform actions, state that you can only take a message to pass along to ${ownerRef}.`;
    }
  }

  // 4. Action Directives
  const actionDirective = activeTools.length > 0
    ? `Autonomous Tool Execution: Proactively execute your active connected tools whenever requested by ${ownerRef}, strictly conforming to each tool's argument schema. Never simulate execution in text.
Multi-Turn Coreference & Parameter Resolution: When ${ownerRef} instructs you to perform an action on previously drafted, discussed, or mentioned content (e.g., 'send this email', 'send it to xyz', 'forward this', 'use that number', 'bhej do'):
   - Autonomously extract and synthesize all required arguments (such as subject, body/htmlContent, recipient name, phone, or dates) from the preceding conversation history.
   - Execute the appropriate tool immediately in the same turn. NEVER ask ${ownerRef} to re-type, confirm, or re-provide information that already exists in recent conversation turns.`
    : `Capability Boundary: You currently have NO connected function tools. Never claim you can perform external actions, dispatch emails, or mutate records. If asked, inform ${ownerRef} that no tools are currently connected.`;

  const defaultDirectives = `1. Strict Grounding: State facts ONLY from verified executed tool results. Never invent records, prices, phone numbers, or dates.
2. ${actionDirective}
3. Formatting: Crisp, proactive, structured. Use *bold* highlights, clean bullet points, and appropriate emojis.
4. Silence Feedback: You are a personal assistant, NOT a customer service line. NEVER ask for customer reviews, 5-star ratings, or feedback surveys.`;

  const operatingDirectives = (options.internalPrompt && options.internalPrompt.trim())
    ? options.internalPrompt.trim() + `\n\n` + actionDirective
    : defaultDirectives;

  let prompt = `${customPersona}

OPERATING DIRECTIVES:
${operatingDirectives}${toolGrounding}${temporalAnchor}${callerAnchor}`;

  // 5. Persistent Fact Notebook
  if (options.factsText && options.factsText.trim()) {
    prompt += '\n\n' + options.factsText.trim();
  }

  // 6. Pending Action Approval Directives
  if (Array.isArray(options.skillDirectives) && options.skillDirectives.length > 0) {
    prompt += '\n\n' + options.skillDirectives.join('\n\n');
  }

  // 7. Few-Shot Examples
  if (Array.isArray(options.trainingExamples) && options.trainingExamples.length > 0) {
    const exBlocks = options.trainingExamples.map(e => `[User Directive]: ${e.user_prompt}\n[Assistant Action & Reply]: ${e.assistant_response}`).join('\n\n');
    prompt += `\n\n--- GOLDEN FEW-SHOT EXAMPLES ---\n${exBlocks}`;
  }

  return prompt;
}
