import { ChatMemoryEngine } from './chat-memory.mjs';
import { UniversalToolsRegistry } from './tools-engine.mjs';
import { buildSupportPrompt, retrieveSupportKnowledge, detectFrustrationAndEscalation as detectFrustrationSupport } from './engine-support.mjs';
import { buildPersonalPrompt } from './engine-personal.mjs';
import { dispatchLlmCascade } from './llm-dispatcher.mjs';
import { sanitizeInput, detectJailbreakOrInjection, scrubSecrets, verifyCallerRole, validateToolAcl } from './ai-security.mjs';

export {
  buildSupportPrompt,
  retrieveSupportKnowledge,
  buildPersonalPrompt,
  dispatchLlmCascade,
  sanitizeInput,
  detectJailbreakOrInjection,
  scrubSecrets,
  verifyCallerRole,
  validateToolAcl
};
/**
 * Enterprise Multi-Provider AI Agent & Zero-Cloud Vector RAG Engine
 * ----------------------------------------------------------------
 * Supports Google Gemini, Groq, and OpenRouter with automatic fallback cascade,
 * high-performance in-memory cosine similarity knowledge retrieval, and frustration detection.
 */

// Simple English & multilingual tokenization and stop words
const STOP_WORDS = new Set([
  'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at',
  'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by',
  'can', 'could', 'did', 'do', 'does', 'doing', 'down', 'during',
  'each', 'few', 'for', 'from', 'further',
  'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how',
  'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself',
  'just', 'me', 'more', 'most', 'my', 'myself',
  'no', 'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own',
  'same', 'she', 'should', 'so', 'some', 'such',
  'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too',
  'under', 'until', 'up', 'very',
  'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would',
  'you', 'your', 'yours', 'yourself', 'yourselves'
]);

/**
 * Tokenize string into normalized terms
 */
export function tokenize(text) {
  if (!text || typeof text !== 'string') return [];
  return text
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .split(/\s+/)
    .filter(token => token.length > 1 && !STOP_WORDS.has(token));
}

/**
 * Build Term-Frequency (TF) vector
 */
export function buildTfVector(tokens) {
  const tf = {};
  if (!tokens || tokens.length === 0) return tf;
  for (const t of tokens) {
    tf[t] = (tf[t] || 0) + 1;
  }
  const total = tokens.length;
  for (const t in tf) {
    tf[t] = tf[t] / total;
  }
  return tf;
}

/**
 * Calculate Cosine Similarity between two sparse TF vectors
 */
export function cosineSimilarity(vecA, vecB) {
  let dotProduct = 0;
  let normA = 0;
  let normB = 0;

  for (const key in vecA) {
    const valA = vecA[key];
    normA += valA * valA;
    if (key in vecB) {
      dotProduct += valA * vecB[key];
    }
  }

  for (const key in vecB) {
    const valB = vecB[key];
    normB += valB * valB;
  }

  if (normA === 0 || normB === 0) return 0;
  return dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));
}

/**
 * Calculate Cosine Similarity between two dense numeric arrays
 */
export function denseCosineSimilarity(arrA, arrB) {
  if (!Array.isArray(arrA) || !Array.isArray(arrB) || arrA.length !== arrB.length) return 0;
  let dot = 0;
  let normA = 0;
  let normB = 0;
  for (let i = 0; i < arrA.length; i++) {
    dot += arrA[i] * arrB[i];
    normA += arrA[i] * arrA[i];
    normB += arrB[i] * arrB[i];
  }
  if (normA === 0 || normB === 0) return 0;
  return dot / (Math.sqrt(normA) * Math.sqrt(normB));
}

/**
 * Retrieve top-K relevant knowledge chunks for a customer query
 */
export async function retrieveRelevantKnowledge(db, userId, queryText, topK = 3, threshold = 0.15) {
  try {
    const [rows] = await db.execute(
      'SELECT id, title, content, embedding, embedding_model FROM ai_knowledge_chunks WHERE user_id = ? AND is_active = 1 LIMIT 200',
      [userId]
    );

    if (!rows || rows.length === 0) return [];

    const queryTokens = tokenize(queryText);
    const queryTf = buildTfVector(queryTokens);

    const scored = [];
    for (const chunk of rows) {
      let sim = 0;
      // Check if dense embedding is stored
      if (chunk.embedding) {
        try {
          const parsed = typeof chunk.embedding === 'string' ? JSON.parse(chunk.embedding) : chunk.embedding;
          if (Array.isArray(parsed)) {
            // Dense vector available
            // If query vector not available, fallback to TF-IDF
          }
        } catch (_) {}
      }

      // Compute TF-IDF sparse similarity
      const chunkTokens = tokenize((chunk.title || '') + ' ' + (chunk.content || ''));
      const chunkTf = buildTfVector(chunkTokens);
      sim = cosineSimilarity(queryTf, chunkTf);

      // Exact phrase / title boost
      const lowerQuery = queryText.toLowerCase();
      const lowerTitle = (chunk.title || '').toLowerCase();
      if (lowerTitle && lowerQuery.includes(lowerTitle)) {
        sim += 0.3;
      }

      if (sim >= threshold) {
        scored.push({
          id: chunk.id,
          title: chunk.title,
          content: chunk.content,
          score: sim
        });
      }
    }

    scored.sort((a, b) => b.score - a.score);
    return scored.slice(0, topK);
  } catch (err) {
    console.error('[AI Agent RAG] Retrieval error:', err.message);
    return [];
  }
}

/**
 * Detect customer frustration, urgency, or explicit request for a human
 */
export function detectFrustrationAndEscalation(text) {
  if (!text || typeof text !== 'string') {
    return { shouldEscalate: false, score: 0, reason: null, signals: [] };
  }

  const raw = text.trim();
  const lower = raw.toLowerCase();
  const signals = [];
  let score = 0;

  // 1. Explicit human handoff intent phrases (requires explicit handoff intent, not bare generic words like "person" or "agent")
  const HUMAN_KEYWORDS = [
    'talk to human', 'speak to human', 'human agent', 'real human', 'need a human', 'want a human', 'human support',
    'talk to a person', 'speak to a person', 'real person', 'actual person', 'need a person', 'live person',
    'talk to agent', 'speak to agent', 'connect to agent', 'connect me to an agent', 'live agent', 'human representative',
    'support team', 'customer service representative', 'customer care',
    'talk to someone', 'speak to someone', 'talk to operator', 'speak to operator',
    'transfer to human', 'call transfer', 'transfer me', 'talk to manager', 'speak to manager',
    'insaan se baat', 'kisi se baat', 'baat karao', 'call lagao', 'phone pe baat'
  ];
  for (const kw of HUMAN_KEYWORDS) {
    const regex = new RegExp(`\\b${kw}\\b`, 'i');
    if (regex.test(lower)) {
      signals.push(`explicit_human_request:${kw}`);
      score += 0.9;
      break;
    }
  }

  // 2. High severity complaint / fraud / legal keywords
  const SEVERE_KEYWORDS = [
    'fraud', 'scam', 'cheater', 'stolen', 'police', 'lawyer', 'consumer court',
    'sue', 'court', 'chargeback', 'loot', 'chor', 'fir', 'fake service', 'worst company'
  ];
  for (const kw of SEVERE_KEYWORDS) {
    const regex = new RegExp(`\\b${kw}\\b`, 'i');
    if (regex.test(lower)) {
      signals.push(`severe_risk:${kw}`);
      score += 0.85;
      break;
    }
  }

  // 3. Profanity & Severe Abuse cues
  const PROFANITY_KEYWORDS = [
    'fuck', 'chutiya', 'harami', 'kutta', 'kamina', 'useless bot', 'bakwas', 'fraud company'
  ];
  for (const kw of PROFANITY_KEYWORDS) {
    if (lower.includes(kw)) {
      signals.push(`profanity_abuse:${kw}`);
      score += 0.75;
      break;
    }
  }

  // 4. Frustration / Anger cues
  const ANGER_KEYWORDS = [
    'terrible', 'pathetic', 'idiot', 'stupid', 'waste of time',
    'annoying', 'angry', 'hate this', 'disgusted', 'pagal', 'faltu', 'rubbish', 'nonsense', 'useless'
  ];
  let angerCount = 0;
  for (const kw of ANGER_KEYWORDS) {
    if (lower.includes(kw)) {
      signals.push(`negative_sentiment:${kw}`);
      score += 0.35;
      angerCount++;
      if (angerCount >= 2) break;
    }
  }

  // 4. Shouting heuristic (ALL CAPS)
  const letters = raw.replace(/[^a-zA-Z]/g, '');
  if (letters.length >= 8) {
    const upperCount = (raw.match(/[A-Z]/g) || []).length;
    const upperRatio = upperCount / letters.length;
    if (upperRatio >= 0.70) {
      signals.push('shouting_all_caps');
      score += 0.35;
    }
  }

  // 5. Punctuation intensity
  if (/[!?]{3,}/.test(raw)) {
    signals.push('excessive_punctuation');
    score += 0.25;
  }

  const shouldEscalate = score >= 0.65;
  let primaryReason = 'frustration_detected';
  if (signals.some(s => s.startsWith('explicit_human_request'))) {
    primaryReason = 'explicit_human_request';
  } else if (signals.some(s => s.startsWith('severe_risk'))) {
    primaryReason = 'severe_risk_flag';
  }

  return {
    shouldEscalate,
    score: Math.min(1.0, score),
    reason: shouldEscalate ? primaryReason : null,
    signals
  };
}

/**
 * Format system prompt by delegating directly to the dedicated engine
 */
export function buildPromptWithRag(systemPersona, retrievedChunks, customerMessage, options = {}) {
  const isPersonal = options.agentMode === 'personal';
  let systemPrompt;
  if (isPersonal) {
    systemPrompt = buildPersonalPrompt(systemPersona, options);
  } else {
    systemPrompt = buildSupportPrompt(systemPersona, retrievedChunks, options);
  }

  return {
    systemPrompt,
    userMessage: customerMessage
  };
}

/**
 * Call Google Gemini API with Autonomous Multi-Turn Function Calling Support
 */
async function callGemini(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = [], toolsContext = null) {
  // Use the exact model chosen by the user without hardcoded overrides
  const modelName = (model && String(model).trim()) || process.env.DEFAULT_GEMINI_MODEL || 'gemini-3.5-flash';
  const contents = ChatMemoryEngine.buildGeminiContents(systemPrompt, conversationHistory, userMessage);

  // Dynamically load active custom & built-in tools for this user from ai_custom_tools
  let toolDeclarations = [];
  let userTools = [];
  if (toolsContext && toolsContext.userId && toolsContext.db) {
    try {
      userTools = toolsContext.scopedTools || await UniversalToolsRegistry.loadUserTools(toolsContext.userId, toolsContext.db);
      const callerRole = toolsContext.callerRole || 'guest';
      toolDeclarations = UniversalToolsRegistry.compileDeclarationsForRole(userTools, callerRole);
    } catch (e) {
      console.error('[AI Agent] Failed to compile tool declarations:', e.message);
    }
  }

  const maxTurns = 4; // Up to 3 autonomous tool turns + final synthesis
  let turn = 0;

  // Strictly respect the user's choice; fallback models are only added if explicitly configured via GEMINI_FALLBACK_MODELS env
  const fallbackList = process.env.GEMINI_FALLBACK_MODELS
    ? process.env.GEMINI_FALLBACK_MODELS.split(',').map(m => m.trim()).filter(Boolean)
    : [];
  const candidateModels = Array.from(new Set([modelName, ...fallbackList]));

  while (turn < maxTurns) {
    turn++;
    const payload = {
      contents,
      generationConfig: {
        temperature: Number(temperature) || 0.4,
        maxOutputTokens: Math.max(Number(maxTokens) || 1200, 1000)
      }
    };

    if (systemPrompt && systemPrompt.trim()) {
      payload.systemInstruction = {
        parts: [{ text: systemPrompt.trim() }]
      };
    }

    // Supply tools on turns < maxTurns; omit on final turn to guarantee text synthesis
    if (toolDeclarations.length > 0 && turn < maxTurns) {
      payload.tools = [{ functionDeclarations: toolDeclarations }];
      payload.tool_config = { function_calling_config: { mode: 'AUTO' } };
    }

    let response;
    let attempts = 0;
    let lastErrText = '';
    while (attempts < candidateModels.length) {
      const activeModel = candidateModels[attempts];
      const activeUrl = `https://generativelanguage.googleapis.com/v1beta/models/${activeModel}:generateContent?key=${apiKey}`;
      attempts++;

      try {
        response = await fetch(activeUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          signal: AbortSignal.timeout(35000)
        });

        // If Google returns 503 (High Demand) or 429 (Rate Limit), failover to next model in cascade
        if (!response.ok && (response.status === 503 || response.status === 429 || response.status === 500)) {
          lastErrText = await response.text();
          const errPreview = lastErrText.slice(0, 120);
          console.warn(`[AI Agent] Gemini (${activeModel}) status ${response.status}: ${errPreview}. Cascading model...`);
          if (attempts < candidateModels.length) {
            await new Promise(r => setTimeout(r, 800));
            continue;
          }
        }
        break;
      } catch (fetchErr) {
        if (attempts >= candidateModels.length) throw fetchErr;
        const reason = fetchErr.cause?.message || fetchErr.message;
        console.warn(`[AI Agent] Gemini (${activeModel}) fetch attempt failed: ${reason}. Retrying next model in 1s...`);
        await new Promise(r => setTimeout(r, 1000));
      }
    }

    if (!response.ok) {
      const errText = lastErrText || (await response.text().catch(() => ''));
      throw new Error(`Gemini API error [${response.status}]: ${errText.slice(0, 200)}`);
    }

    const data = await response.json();
    const candidate = data?.candidates?.[0];
    const parts = candidate?.content?.parts || [];

    const functionCalls = parts.filter(p => p.functionCall);

    if (functionCalls.length > 0 && turn < maxTurns) {
      const functionResponseParts = [];
      for (const call of functionCalls) {
        const fnName = call.functionCall.name;
        const fnArgs = call.functionCall.args || {};
        const result = await UniversalToolsRegistry.executeTool(fnName, fnArgs, userTools, toolsContext);
        const structuredResponse = (typeof result === 'object' && result !== null) ? result : { result: String(result ?? '') };
        const respPart = {
          functionResponse: {
            name: fnName,
            response: structuredResponse
          }
        };
        if (call.functionCall.id || call.id) {
          respPart.functionResponse.id = call.functionCall.id || call.id;
        }
        functionResponseParts.push(respPart);
      }

      contents.push(candidate.content);
      contents.push({
        role: 'user',
        parts: functionResponseParts
      });
      // Continue to next turn
    } else {
      const textParts = parts.filter(p => p.text).map(p => p.text) || [];
      const text = textParts.join('\n').trim();
      if (!text) {
        throw new Error('Gemini API returned empty text');
      }
      return text;
    }
  }

  throw new Error('Gemini function calling loop exceeded max turns without generating text');
}

/**
 * Call Groq API (OpenAI compatible with full Function Calling loop)
 */
/**
 * Recursively sanitizes JSON schema for Groq / OpenAI compatibility:
 * - Lowercases schema types ('STRING' -> 'string', 'OBJECT' -> 'object')
 * - Adds default items for arrays
 * - Preserves nested properties, enums, descriptions, and required arrays
 */
function sanitizeJsonSchemaForOpenAi(schema) {
  if (!schema || typeof schema !== 'object') {
    return { type: 'object', properties: {} };
  }
  const clean = Array.isArray(schema) ? [] : {};
  for (const [k, v] of Object.entries(schema)) {
    if (k === 'type' && typeof v === 'string') {
      const lower = v.toLowerCase();
      clean[k] = ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'].includes(lower) ? lower : 'string';
    } else if (v && typeof v === 'object') {
      clean[k] = sanitizeJsonSchemaForOpenAi(v);
    } else {
      clean[k] = v;
    }
  }
  if (clean.type === 'array' && !clean.items) {
    clean.items = { type: 'string' };
  }
  if (clean.type === 'object' && !clean.properties) {
    clean.properties = {};
  }
  return clean;
}

async function callGroq(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = [], toolsContext = null) {
  const modelName = model || process.env.DEFAULT_GROQ_MODEL || 'openai/gpt-oss-120b';
  const url = 'https://api.groq.com/openai/v1/chat/completions';

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  // Compile OpenAI-compatible tools
  let openAiTools = [];
  let userTools = [];
  if (toolsContext && toolsContext.userId && toolsContext.db) {
    try {
      userTools = toolsContext.scopedTools || await UniversalToolsRegistry.loadUserTools(toolsContext.userId, toolsContext.db);
      const callerRole = toolsContext.callerRole || 'guest';
      const declarations = UniversalToolsRegistry.compileDeclarationsForRole(userTools, callerRole);
      openAiTools = declarations.map(d => ({
        type: 'function',
        function: {
          name: d.name,
          description: d.description || d.name,
          parameters: sanitizeJsonSchemaForOpenAi(d.parameters)
        }
      }));
    } catch (e) {
      console.error('[AI Agent] Failed to compile Groq tools:', e.message);
    }
  }

  const maxTurns = 4;
  let turn = 0;

  while (turn < maxTurns) {
    turn++;
    const payload = {
      model: modelName,
      messages,
      temperature: Number(temperature) || 0.4,
      max_tokens: Math.max(Number(maxTokens) || 1200, 800)
    };

    if (openAiTools.length > 0 && turn < maxTurns) {
      payload.tools = openAiTools;
      payload.tool_choice = 'auto';
    }

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`
      },
      body: JSON.stringify(payload),
      signal: AbortSignal.timeout(20000)
    });

    if (!response.ok) {
      const errText = await response.text();
      throw new Error(`Groq API error [${response.status}]: ${errText.slice(0, 200)}`);
    }

    const data = await response.json();
    const assistantMsg = data?.choices?.[0]?.message;
    if (!assistantMsg) {
      throw new Error('Groq API returned empty message');
    }

    // Check for tool calls
    if (assistantMsg.tool_calls && assistantMsg.tool_calls.length > 0 && turn < maxTurns) {
      messages.push(assistantMsg);

      for (const call of assistantMsg.tool_calls) {
        const fnName = call.function?.name;
        let fnArgs = {};
        try {
          fnArgs = JSON.parse(call.function?.arguments || '{}');
        } catch (e) {}

        const result = await UniversalToolsRegistry.executeTool(fnName, fnArgs, userTools, toolsContext);
        messages.push({
          role: 'tool',
          tool_call_id: call.id,
          content: typeof result === 'string' ? result : JSON.stringify(result)
        });
      }
      // Continue next turn to synthesize final answer
    } else {
      const text = assistantMsg.content || '';
      if (!text && !assistantMsg.tool_calls) {
        throw new Error('Groq API returned empty text');
      }
      return text.trim();
    }
  }
}

/**
 * Call Custom API (OpenAI Compatible)
 */
async function callCustomApi(apiKey, model, endpoint, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = []) {
  const modelName = model || process.env.DEFAULT_CUSTOM_MODEL || 'gpt-4o';
  let baseEndpoint = (endpoint && String(endpoint).trim()) || process.env.DEFAULT_CUSTOM_ENDPOINT || 'https://api.openai.com/v1';
  baseEndpoint = baseEndpoint.replace(/\/+$/, '');
  const url = baseEndpoint.endsWith('/chat/completions') ? baseEndpoint : `${baseEndpoint}/chat/completions`;

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  const payload = {
    model: modelName,
    messages,
    temperature: Number(temperature),
    max_tokens: Math.max(Number(maxTokens) || 1200, 800)
  };

  const headers = {
    'Content-Type': 'application/json'
  };
  if (apiKey && apiKey.trim()) {
    headers['Authorization'] = `Bearer ${apiKey.trim()}`;
  }

  const response = await fetch(url, {
    method: 'POST',
    headers,
    body: JSON.stringify(payload),
    signal: AbortSignal.timeout(20000)
  });

  if (!response.ok) {
    const errText = await response.text();
    throw new Error(`Custom API error [${response.status}]: ${errText.slice(0, 200)}`);
  }

  const data = await response.json();
  const text = data?.choices?.[0]?.message?.content;
  if (!text) {
    throw new Error('Custom API returned empty text');
  }
  return text.trim();
}

/**
 * Execute Multi-Provider Generation Cascade via Dedicated LLM Dispatcher
 */
export async function generateAiReply(settings, systemPrompt, customerMessage, conversationHistory = [], toolsContext = null) {
  return await dispatchLlmCascade(settings, systemPrompt, customerMessage, conversationHistory, toolsContext);
}
