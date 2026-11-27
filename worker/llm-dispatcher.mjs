/**
 * WhatsGateway Multi-Provider LLM Dispatcher
 * -------------------------------------------
 * Provides robust failover cascade across Gemini, Groq, and OpenRouter,
 * autonomous multi-turn tool execution loop, and output secret scrubbing.
 */

import { ChatMemoryEngine } from './chat-memory.mjs';
import { UniversalToolsRegistry } from './tools-engine.mjs';
import { scrubSecrets, validateToolAcl } from './ai-security.mjs';

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

/**
 * Call Google Gemini API with Autonomous Multi-Turn Function Calling Support
 */
async function callGemini(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = [], toolsContext = null) {
  const modelName = (model && String(model).trim()) || process.env.DEFAULT_GEMINI_MODEL || 'gemini-3.5-flash';
  const contents = ChatMemoryEngine.buildGeminiContents(systemPrompt, conversationHistory, userMessage);

  let toolDeclarations = [];
  let userTools = [];
  if (toolsContext && toolsContext.userId && toolsContext.db) {
    try {
      userTools = toolsContext.scopedTools || await UniversalToolsRegistry.loadUserTools(toolsContext.userId, toolsContext.db);
      const callerRole = toolsContext.callerRole || 'guest';
      toolDeclarations = UniversalToolsRegistry.compileDeclarationsForRole(userTools, callerRole);
    } catch (e) {
      console.error('[LLM Dispatcher] Failed to compile tool declarations:', e.message);
    }
  }

  const maxTurns = 4;
  let turn = 0;

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

      try {
        response = await fetch(activeUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          signal: AbortSignal.timeout(45000)
        });

        if (response.ok) break;

        const errText = await response.text();
        lastErrText = errText;
        console.warn(`[LLM Dispatcher] Gemini model ${activeModel} failed (status ${response.status}): ${errText.slice(0, 200)}. Cascading...`);
        attempts++;
      } catch (fetchErr) {
        lastErrText = fetchErr.message;
        console.warn(`[LLM Dispatcher] Gemini network error for ${activeModel}: ${fetchErr.message}. Cascading...`);
        attempts++;
      }
    }

    if (!response || !response.ok) {
      throw new Error(`Gemini API error across all models: ${lastErrText}`);
    }

    const data = await response.json();
    const candidate = data.candidates?.[0];
    if (!candidate || !candidate.content) {
      throw new Error('Gemini API returned no candidates or content');
    }

    const parts = candidate.content.parts || [];
    const functionCalls = parts.filter(p => p.functionCall);

    if (functionCalls.length > 0 && toolsContext) {
      const functionResponseParts = [];
      for (const call of functionCalls) {
        const fnName = call.functionCall.name;
        const fnArgs = call.functionCall.args || {};

        // Security check: Validate Tool ACL
        const aclCheck = validateToolAcl(fnName, toolsContext.callerRole || 'guest');
        let structuredResponse;
        if (!aclCheck.allowed) {
          structuredResponse = { error: aclCheck.reason };
        } else {
          structuredResponse = await UniversalToolsRegistry.executeTool(fnName, fnArgs, toolsContext);
        }

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
    } else {
      const textParts = parts.filter(p => p.text).map(p => p.text) || [];
      const text = textParts.join('\n').trim();
      if (!text) {
        throw new Error('Gemini API returned empty text');
      }
      return scrubSecrets(text);
    }
  }

  throw new Error('Gemini function calling loop exceeded max turns without generating text');
}

/**
 * Call Groq API (OpenAI compatible with full Function Calling loop)
 */
async function callGroq(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = [], toolsContext = null) {
  const modelName = model || process.env.DEFAULT_GROQ_MODEL || 'openai/gpt-oss-120b';
  const url = 'https://api.groq.com/openai/v1/chat/completions';

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  let openAiTools = [];
  if (toolsContext && toolsContext.userId && toolsContext.db) {
    try {
      const userTools = toolsContext.scopedTools || await UniversalToolsRegistry.loadUserTools(toolsContext.userId, toolsContext.db);
      const callerRole = toolsContext.callerRole || 'guest';
      const geminiDecls = UniversalToolsRegistry.compileDeclarationsForRole(userTools, callerRole);

      openAiTools = geminiDecls.map(decl => ({
        type: 'function',
        function: {
          name: decl.name,
          description: decl.description || '',
          parameters: sanitizeJsonSchemaForOpenAi(decl.parameters)
        }
      }));
    } catch (e) {
      console.error('[LLM Dispatcher] Failed to compile Groq tools:', e.message);
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
      max_tokens: Math.max(Number(maxTokens) || 1200, 1000)
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
      signal: AbortSignal.timeout(45000)
    });

    if (!response.ok) {
      const errText = await response.text();
      throw new Error(`Groq API error (${response.status}): ${errText}`);
    }

    const data = await response.json();
    const choice = data.choices?.[0];
    if (!choice || !choice.message) {
      throw new Error('Groq API returned no valid message choices');
    }

    const assistantMsg = choice.message;
    messages.push(assistantMsg);

    if (assistantMsg.tool_calls && assistantMsg.tool_calls.length > 0 && toolsContext) {
      for (const tCall of assistantMsg.tool_calls) {
        const fnName = tCall.function.name;
        let fnArgs = {};
        try {
          fnArgs = JSON.parse(tCall.function.arguments || '{}');
        } catch (_) {}

        // Security check: Validate Tool ACL
        const aclCheck = validateToolAcl(fnName, toolsContext.callerRole || 'guest');
        let structuredResult;
        if (!aclCheck.allowed) {
          structuredResult = { error: aclCheck.reason };
        } else {
          structuredResult = await UniversalToolsRegistry.executeTool(fnName, fnArgs, toolsContext);
        }

        messages.push({
          role: 'tool',
          tool_call_id: tCall.id,
          name: fnName,
          content: JSON.stringify(structuredResult)
        });
      }
    } else {
      const text = (assistantMsg.content || '').trim();
      if (!text) {
        throw new Error('Groq API returned empty text');
      }
      return scrubSecrets(text);
    }
  }

  throw new Error('Groq function calling loop exceeded max turns');
}

/**
 * Call OpenRouter API (OpenAI compatible fallback)
 */
async function callOpenRouter(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = []) {
  const modelName = model || process.env.DEFAULT_OPENROUTER_MODEL || 'meta-llama/llama-3.3-70b-instruct:free';
  const url = 'https://openrouter.ai/api/v1/chat/completions';

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  const payload = {
    model: modelName,
    messages,
    temperature: Number(temperature) || 0.4,
    max_tokens: Math.max(Number(maxTokens) || 1200, 1000)
  };

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${apiKey}`,
      'HTTP-Referer': 'https://whatsgateway.app',
      'X-Title': 'WhatsGateway'
    },
    body: JSON.stringify(payload),
    signal: AbortSignal.timeout(45000)
  });

  if (!response.ok) {
    const errText = await response.text();
    throw new Error(`OpenRouter API error (${response.status}): ${errText}`);
  }

  const data = await response.json();
  const text = (data.choices?.[0]?.message?.content || '').trim();
  if (!text) {
    throw new Error('OpenRouter API returned empty text');
  }
  return scrubSecrets(text);
}

/**
 * Multi-Provider Cascade Dispatcher
 */
export async function dispatchLlmCascade(settings, systemPrompt, customerMessage, conversationHistory = [], toolsContext = null) {
  const priority = settings.provider_priority || 'gemini';
  const providers = [];

  if (priority === 'gemini') {
    providers.push('gemini', 'groq', 'openrouter');
  } else if (priority === 'groq') {
    providers.push('groq', 'gemini', 'openrouter');
  } else {
    providers.push('openrouter', 'gemini', 'groq');
  }

  let lastError = null;
  for (const prov of providers) {
    try {
      if (prov === 'gemini' && settings.gemini_api_key) {
        return await callGemini(
          settings.gemini_api_key,
          settings.gemini_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory,
          toolsContext
        );
      }
      if (prov === 'groq' && settings.groq_api_key) {
        return await callGroq(
          settings.groq_api_key,
          settings.groq_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory,
          toolsContext
        );
      }
      if (prov === 'openrouter' && settings.openrouter_api_key) {
        return await callOpenRouter(
          settings.openrouter_api_key,
          settings.openrouter_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory
        );
      }
    } catch (err) {
      const errDetail = err.cause?.message ? `${err.message} (${err.cause.message})` : err.message;
      console.warn(`[LLM Dispatcher] Provider '${prov}' failed: ${errDetail}. Cascading to next provider...`);
      lastError = err;
    }
  }

  if (lastError) {
    throw lastError;
  }
  return null;
}
