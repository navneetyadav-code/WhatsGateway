/**
 * WhatsGateway Dedicated Customer Support RAG Engine
 * --------------------------------------------------
 * Isolated engine for business customer service, FAQ retrieval,
 * anti-hallucination guardrails, and human escalation detection.
 * 
 * Clean Architecture: Contains ZERO references to personal assistant tasks,
 * owner ACLs, or external tool execution manifests.
 */

import { sanitizeInput, detectJailbreakOrInjection } from './ai-security.mjs';

// Multilingual stop words for knowledge base tokenization
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

export function tokenize(text) {
  if (!text || typeof text !== 'string') return [];
  return text
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .split(/\s+/)
    .filter(token => token.length > 1 && !STOP_WORDS.has(token));
}

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
 * Retrieve top-K relevant knowledge chunks using Cosine Similarity on TF vectors
 */
export async function retrieveSupportKnowledge(db, userId, queryText, topK = 3, threshold = 0.15) {
  if (!db || !userId || !queryText) return [];

  try {
    const [rows] = await db.execute(
      'SELECT id, title, content FROM ai_knowledge_chunks WHERE user_id = ? AND is_active = 1 LIMIT 200',
      [userId]
    );

    if (!rows || rows.length === 0) return [];

    const queryTokens = tokenize(queryText);
    const queryTf = buildTfVector(queryTokens);

    const scored = [];
    for (const chunk of rows) {
      const chunkTokens = tokenize((chunk.title || '') + ' ' + (chunk.content || ''));
      const chunkTf = buildTfVector(chunkTokens);
      let sim = cosineSimilarity(queryTf, chunkTf);

      // Boost score if the query contains exact title terms
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
    console.error('[SupportEngine RAG] Retrieval error:', err.message);
    return [];
  }
}

/**
 * Detect customer frustration, urgency, or explicit request for a human agent
 */
export function detectFrustrationAndEscalation(text) {
  if (!text || typeof text !== 'string') {
    return { isFrustrated: false, score: 0, reason: null, needsHuman: false };
  }

  const humanPatterns = [
    /\b(human|agent|person|representative|support team|executive|real person|manager|operator)\b/i,
    /\b(talk to (someone|human|person|agent))\b/i,
    /\b(call me|give me a call)\b/i,
    /\b(insan se baat|agent se baat|kisi se baat)\b/i
  ];

  for (const pattern of humanPatterns) {
    if (pattern.test(text)) {
      return { isFrustrated: false, score: 1.0, reason: 'Customer explicitly requested a human representative.', needsHuman: true };
    }
  }

  const angryPatterns = [
    /\b(useless|terrible|awful|horrible|worst|pathetic|scam|fraud|cheater|disaster)\b/i,
    /\b(waste of time|not helping|stop wasting|ridiculous|annoying|stupid)\b/i,
    /\b(angry|furious|pissed|unacceptable|lawyer|sue|complaint|consumer court)\b/i,
    /\b(bakwas|ghatiya|bekar|dimag kharab|chup kar)\b/i
  ];

  let matches = 0;
  for (const pattern of angryPatterns) {
    if (pattern.test(text)) matches++;
  }

  const capsRatio = (text.replace(/[^A-Z]/g, '').length) / (text.replace(/[^a-zA-Z]/g, '').length || 1);
  const shouting = text.length > 10 && capsRatio > 0.6;
  const exclamationCount = (text.match(/!/g) || []).length;

  let score = matches * 0.35;
  if (shouting) score += 0.3;
  if (exclamationCount >= 3) score += 0.2;

  score = Math.min(1.0, score);

  return {
    isFrustrated: score >= 0.65,
    score: parseFloat(score.toFixed(2)),
    reason: score >= 0.65 ? 'High negative sentiment or frustrated tone detected.' : null,
    needsHuman: score >= 0.75
  };
}

/**
 * Build clean system prompt for Customer Support
 */
export function buildSupportPrompt(systemPersona, retrievedChunks = [], options = {}) {
  const defaultPersona = 'You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you do not have that information and offer to connect them to our support team.';
  const persona = (systemPersona && systemPersona.trim()) ? systemPersona.trim() : defaultPersona;

  let knowledgeBlock = '';
  if (retrievedChunks && retrievedChunks.length > 0) {
    knowledgeBlock = `\n\n--- OFFICIAL VERIFIED KNOWLEDGE BASE ---\n` +
      retrievedChunks.map((c, i) => `[Source ${i + 1}: ${c.title}]\n${c.content}`).join('\n\n') +
      `\n---------------------------------------\n` +
      `STRICT INSTRUCTIONS FOR THE AI ASSISTANT:
1. Base your factual answers STRICTLY on the knowledge base sources above.
2. DO NOT invent, hallucinate, or assume facts (like prices, phone numbers, or rules) not present in the sources.
3. If the user asks something not covered in the sources, politely inform them that you do not have that specific detail and offer to connect them with our human support team.
4. Language & Tone: Detect the user's language and mirror their style naturally. If they use Hindi or natural Hinglish, reply in warm, helpful Hinglish. If they use English, reply in professional, encouraging English.
5. Format for WhatsApp: Use bold (*text*), clean bullet points, and appropriate emojis. Avoid long unbroken walls of text.`;
  } else {
    knowledgeBlock = `\n\n--- KNOWLEDGE RETRIEVAL NOTE ---\n` +
      `No specific knowledge base records matched the user's query.\n` +
      `STRICT INSTRUCTIONS FOR THE AI ASSISTANT:
1. You are a dedicated customer assistant strictly limited to this organization.
2. If the user asks about out-of-scope topics (e.g. general knowledge, recipes, cooking, coding, competitors, unrelated trivia), DO NOT answer the out-of-scope question. Politely decline and state that you can only assist with our organization's official products, services, and policies.
3. If they are asking a genuine question about our organization or services that isn't in your knowledge base, apologize politely and offer to connect them with our human support team.
4. For general pleasantries/greetings (e.g. 'hi', 'hello', 'good morning', 'namaste'), respond warmly and ask how you can help them with our products or services today.`;
  }

  const temporalAnchor = options.currentDateTime
    ? `\n\n--- CURRENT DATE & TIME ---\nToday is ${options.currentDateTime}. Use this for calculating relative dates.`
    : '';

  let fullPrompt = persona + knowledgeBlock + temporalAnchor;

  if (Array.isArray(options.trainingExamples) && options.trainingExamples.length > 0) {
    const exBlocks = options.trainingExamples.map(e => `[User]: ${e.user_prompt}\n[Assistant]: ${e.assistant_response}`).join('\n\n');
    fullPrompt += `\n\n--- GOLDEN FEW-SHOT EXAMPLES ---\n${exBlocks}`;
  }

  return fullPrompt;
}
