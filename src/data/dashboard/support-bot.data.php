<?php
declare(strict_types=1);

require_once __DIR__ . '/chatbot.data.php';

$engineType = 'support';
$toolsConfig = json_decode((string) ($aiSettings['tools_config'] ?? '{}'), true) ?: [];

$supportPersona = (string) ($aiSettings['system_prompt'] ?? "You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you don't have that information and offer to connect them to our support team.");

$defaultSupportInternal = "1. Base your factual answers STRICTLY on the knowledge base sources provided.
2. DO NOT invent, hallucinate, or assume facts not present in the sources.
3. If the user asks something not covered in the sources, politely inform them that you do not have that specific detail and offer to connect them with human support.
4. Language & Tone: Detect the user's language and mirror their style naturally. If they use Hindi or natural Hinglish, reply in warm, helpful Hinglish. If they use English, reply in professional, encouraging English.
5. Format for WhatsApp: Use bold (*text*), clean bullet points, and appropriate emojis. Avoid long unbroken walls of text.";

$supportInternalPrompt = (string) ($toolsConfig['support_internal_prompt'] ?? $defaultSupportInternal);
