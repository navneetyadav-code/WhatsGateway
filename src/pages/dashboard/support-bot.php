<?php
declare(strict_types=1);

$providerNames = [
    'gemini'     => 'Google Gemini',
    'groq'       => 'Groq Cloud',
    'custom'     => 'Custom API (OpenAI Compatible)',
];
?>

<div class="space-y-4 mb-6 max-w-7xl mx-auto">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Customer Support Studio</span>
                <?php if ($deviceConnected): ?>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Online <?= $devicePhone ? '(' . e($devicePhone) . ')' : '' ?>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        Offline
                    </span>
                <?php endif; ?>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Automate customer inquiries with verified knowledge retrieval and customizable system directives.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=personal-assistant')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Switch to Personal Assistant</span>
            </a>
            <a href="<?= e(base_url('index.php?page=ai-credentials')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>AI Keys</span>
            </a>
            <button type="button" onclick="document.getElementById('supportAgentForm').requestSubmit()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-lg shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form id="supportAgentForm" method="post" action="<?= e(base_url('index.php?page=ai-agent-save')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="agent_mode" value="support">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            <!-- Left Column: Knowledge Base & Provider Configuration (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card 1: Engine Master Toggle & Knowledge Status -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-700 border border-blue-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Support Engine Status</h3>
                                <span class="text-[11px] font-semibold <?= !empty($aiSettings['enabled']) ? 'text-emerald-700' : 'text-slate-400' ?>">
                                    <?= !empty($aiSettings['enabled']) ? 'Active & Auto-Replying' : 'Paused' ?>
                                </span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" <?= !empty($aiSettings['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Knowledge Base Status Card -->
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span>Verified Knowledge Base</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5"><?= $knowledgeCount ?> articles & FAQ chunks indexed</p>
                        </div>
                        <a href="<?= e(base_url('index.php?page=knowledge')) ?>" class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-100/70 hover:bg-blue-200/80 rounded-md transition-colors">
                            Manage Docs →
                        </a>
                    </div>

                    <!-- Escalation & Surveys -->
                    <div class="space-y-2 pt-1">
                        <label class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80 cursor-pointer">
                            <span class="text-[11px] font-semibold text-slate-700">Auto-Escalate on Customer Frustration</span>
                            <input type="checkbox" name="auto_reply_enabled" value="1" <?= !empty($aiSettings['auto_reply_enabled']) ? 'checked' : '' ?> class="w-3.5 h-3.5 rounded text-blue-600 focus:ring-blue-500">
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80 cursor-pointer">
                            <span class="text-[11px] font-semibold text-slate-700">Send Post-Chat Feedback Survey</span>
                            <input type="checkbox" name="feedback_enabled" value="1" <?= !empty($aiSettings['feedback_enabled']) ? 'checked' : '' ?> class="w-3.5 h-3.5 rounded text-blue-600 focus:ring-blue-500">
                        </label>
                    </div>
                </div>

                <!-- Card 2: AI Provider & Model Cascade -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Model & Priority Cascade</span>
                    </h3>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Primary LLM Provider</label>
                        <select name="provider_priority" class="w-full text-xs font-semibold rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 p-2 border">
                            <?php foreach ($providerNames as $pk => $pv): ?>
                                <option value="<?= $pk ?>" <?= ($aiSettings['provider_priority'] ?? '') === $pk ? 'selected' : '' ?>><?= $pv ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Temperature (<?= e((string)$aiSettings['temperature']) ?>)</label>
                            <input type="range" name="temperature" min="0" max="1" step="0.05" value="<?= e((string)$aiSettings['temperature']) ?>" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            <div class="flex justify-between text-[10px] text-slate-400 mt-0.5"><span>Factual</span><span>Creative</span></div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max Tokens</label>
                            <input type="number" name="max_tokens" value="<?= e((string)$aiSettings['max_tokens']) ?>" min="100" max="2000" step="50" class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 bg-slate-50/50 p-1.5 border">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Dedicated Persona & Internal Directives Editors (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Section 1: External Persona / Brand Voice -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>External Persona & Brand Voice</span>
                            </h3>
                            <p class="text-[11px] text-slate-500">Defines who your assistant is, brand voice, and greeting style.</p>
                        </div>
                        <button type="button" onclick="previewSupportPrompt()" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded-md transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Inspect Full Prompt</span>
                        </button>
                    </div>

                    <!-- Presets -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] uppercase font-bold text-slate-400 mr-1">Presets:</span>
                        <button type="button" onclick="loadSupportPreset('general')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">General Support</button>
                        <button type="button" onclick="loadSupportPreset('ecommerce')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">E-Commerce & Orders</button>
                        <button type="button" onclick="loadSupportPreset('clinic')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">Appointments & Clinic</button>
                    </div>

                    <!-- Persona Textarea -->
                    <div>
                        <textarea id="systemPromptTextarea" name="system_prompt" rows="5" class="w-full text-xs font-mono rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 leading-relaxed bg-slate-50/50 border"><?= e($supportPersona) ?></textarea>
                    </div>
                </div>

                <!-- Section 2: Internal Directives & Anti-Hallucination Guardrails -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Internal Operating Directives (System Guardrails)</span>
                            </h3>
                            <p class="text-[11px] text-slate-500">Edit the strict internal instructions for knowledge grounding, language mirror, and out-of-scope queries.</p>
                        </div>
                        <button type="button" onclick="resetSupportInternal()" class="text-[10px] font-semibold text-slate-500 hover:text-slate-800 underline">Reset Default</button>
                    </div>

                    <div>
                        <textarea id="supportInternalTextarea" name="support_internal_prompt" rows="7" class="w-full text-xs font-mono rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 p-2.5 leading-relaxed bg-slate-50/50 border"><?= e($supportInternalPrompt) ?></textarea>
                    </div>
                    <p class="text-[10px] text-slate-400">These instructions are injected by the system to ensure the model never invents details not found in your knowledge base.</p>
                </div>

                <!-- Prompt Inspector Modal -->
                <div id="promptInspectorModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Compiled Full System Prompt</span>
                            </h4>
                            <button type="button" onclick="document.getElementById('promptInspectorModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 font-mono text-[11px] bg-slate-950 text-slate-200 whitespace-pre-wrap leading-relaxed select-all" id="promptInspectorContent">
                        </div>
                        <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button type="button" onclick="document.getElementById('promptInspectorModal').classList.add('hidden')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-100">Close</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function loadSupportPreset(type) {
    const textarea = document.getElementById('systemPromptTextarea');
    if (type === 'general') {
        textarea.value = "You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you don't have that information and offer to connect them to our support team.";
    } else if (type === 'ecommerce') {
        textarea.value = "You are the dedicated customer support assistant for our online store on WhatsApp. Help customers with product inquiries, order tracking questions, return policies, and shipping rates based strictly on our store knowledge base. Always remain warm, concise, and encourage smooth checkout.";
    } else if (type === 'clinic') {
        textarea.value = "You are the patient coordination assistant for our clinic on WhatsApp. Provide accurate information regarding doctor availability, consultation hours, clinic address, and general booking procedures based strictly on our official knowledge base. For medical emergencies, always advise contacting emergency services immediately.";
    }
}

function resetSupportInternal() {
    document.getElementById('supportInternalTextarea').value = 
`1. Base your factual answers STRICTLY on the knowledge base sources provided.
2. DO NOT invent, hallucinate, or assume facts not present in the sources.
3. If the user asks something not covered in the sources, politely inform them that you do not have that specific detail and offer to connect them with human support.
4. Language & Tone: Detect the user's language and mirror their style naturally. If they use Hindi or natural Hinglish, reply in warm, helpful Hinglish. If they use English, reply in professional, encouraging English.
5. Format for WhatsApp: Use bold (*text*), clean bullet points, and appropriate emojis. Avoid long unbroken walls of text.`;
}

function previewSupportPrompt() {
    const persona = document.getElementById('systemPromptTextarea').value.trim();
    const internal = document.getElementById('supportInternalTextarea').value.trim();
    const modal = document.getElementById('promptInspectorModal');
    const content = document.getElementById('promptInspectorContent');

    const samplePrompt = persona + 
`\n\n--- OFFICIAL VERIFIED KNOWLEDGE BASE ---
[Source 1: Company Information & FAQs]
Sample verified business documentation retrieved from your knowledge base...

---------------------------------------
STRICT INSTRUCTIONS FOR THE AI ASSISTANT:
` + internal + `

--- CURRENT DATE & TIME ---
Today is ` + new Date().toLocaleString() + `.`;

    content.textContent = samplePrompt;
    modal.classList.remove('hidden');
}
</script>
