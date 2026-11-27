<?php
declare(strict_types=1);

$providerNames = [
    'gemini'     => 'Google Gemini',
    'groq'       => 'Groq Cloud',
    'openrouter' => 'OpenRouter',
];
?>

<div class="space-y-4 mb-6 max-w-7xl mx-auto">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Personal Assistant Studio</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    24/7 Executive Copilot
                </span>
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
            <p class="text-xs text-slate-500 mt-0.5">Your personal executive WhatsApp copilot for scheduling, emails, tool execution, and message screening.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=support-bot')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Switch to Support Bot</span>
            </a>
            <a href="<?= e(base_url('index.php?page=tools')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Tools (<?= $toolsCount ?> Active)</span>
            </a>
            <button type="button" onclick="document.getElementById('personalAgentForm').requestSubmit()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-600 rounded-lg shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- Security & Isolation Banner -->
    <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span><strong>Executive Security Active:</strong> External callers are strictly isolated and cannot trigger tools, send emails, or command tasks.</span>
        </div>
        <span class="inline-flex items-center gap-1 font-semibold text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-md text-[10px]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Strict ACL & 2-Phase Confirmations
        </span>
    </div>

    <!-- Main Settings Form -->
    <form id="personalAgentForm" method="post" action="<?= e(base_url('index.php?page=ai-agent-save')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="agent_mode" value="personal">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            <!-- Left Column: Owner Identity & Caller Authentication (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card 1: Engine Master Toggle & Owner Identity -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Assistant Engine Status</h3>
                                <span class="text-[11px] font-semibold <?= !empty($aiSettings['enabled']) ? 'text-emerald-700' : 'text-slate-400' ?>">
                                    <?= !empty($aiSettings['enabled']) ? 'Active & Standing By (24/7)' : 'Paused' ?>
                                </span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" <?= !empty($aiSettings['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Owner Profile -->
                    <div class="space-y-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Owner Name / Reference</label>
                            <input type="text" name="owner_name" value="<?= e($aiSettings['owner_name'] ?? 'Boss') ?>" placeholder="e.g. Alex, Boss, Sarah" class="w-full text-xs rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2 border">
                            <p class="text-[10px] text-slate-400 mt-0.5">The assistant will address and recognize you by this name.</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Verified Admin Phone Number(s)</label>
                            <input type="text" name="admin_phones" value="<?= e($adminPhones) ?>" placeholder="e.g. 919876543210, 14155552671" class="w-full text-xs font-mono rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2 border">
                            <p class="text-[10px] text-slate-400 mt-0.5">Only messages from these numbers are permitted to command tools, send emails, or run tasks.</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Tools & Autonomous Actions -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Connected Capabilities</span>
                        </h3>
                        <a href="<?= e(base_url('index.php?page=tools')) ?>" class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800">
                            Configure Tools (<?= $toolsCount ?>) →
                        </a>
                    </div>

                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                            <span class="font-medium text-slate-800">Calendar & Scheduling</span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                            <span class="font-medium text-slate-800">Email Dispatch (SMTP)</span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                            <span class="font-medium text-slate-800">Persistent Fact Notebook</span>
                            <a href="<?= e(base_url('index.php?page=memory')) ?>" class="text-[10px] font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded hover:bg-blue-200">
                                View Facts →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card 3: External Caller Screening Policy -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Guest Screening Policy</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">What the assistant tells other people who text this WhatsApp number:</p>
                    <textarea name="screening_policy" rows="2" class="w-full text-xs rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2 border"><?= e($screeningPolicy) ?></textarea>
                </div>

            </div>

            <!-- Right Column: Assistant Persona & Micro-Kernel (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Executive Persona & Operating Directives</span>
                            </h3>
                            <p class="text-[11px] text-slate-500">Configure your personal assistant's voice, speed, and execution style.</p>
                        </div>
                        <button type="button" onclick="previewPersonalPrompt()" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded-md transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Inspect Micro-Kernel</span>
                        </button>
                    </div>

                    <!-- Presets -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] uppercase font-bold text-slate-400 mr-1">Presets:</span>
                        <button type="button" onclick="loadPersonalPreset('chief')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">Chief of Staff</button>
                        <button type="button" onclick="loadPersonalPreset('direct')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">Direct & Action-Oriented</button>
                        <button type="button" onclick="loadPersonalPreset('wingman')" class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded transition-colors">Casual Wingman</button>
                    </div>

                    <!-- Persona Textarea -->
                    <div>
                        <textarea id="personalPromptTextarea" name="personal_system_prompt" rows="9" class="w-full text-xs font-mono rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 p-3 leading-relaxed bg-slate-50/50 border"><?= e($personalPrompt) ?></textarea>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        <strong>Security Guarantee:</strong> This engine operates with zero customer FAQ search, zero feedback surveys, and automatic multi-turn parameter extraction for seamless action execution.
                    </p>
                </div>

                <!-- LLM Provider Parameters -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Model Configuration</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Primary LLM Provider</label>
                            <select name="provider_priority" class="w-full text-xs font-semibold rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2 border">
                                <?php foreach ($providerNames as $pk => $pv): ?>
                                    <option value="<?= $pk ?>" <?= ($aiSettings['provider_priority'] ?? '') === $pk ? 'selected' : '' ?>><?= $pv ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Execution Speed / Tokens</label>
                            <input type="number" name="max_tokens" value="<?= e((string)$aiSettings['max_tokens']) ?>" min="100" max="2000" step="50" class="w-full text-xs rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50 p-2 border">
                        </div>
                    </div>
                </div>

                <!-- Prompt Inspector Modal -->
                <div id="personalPromptInspectorModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Compiled Personal Assistant Micro-Kernel</span>
                            </h4>
                            <button type="button" onclick="document.getElementById('personalPromptInspectorModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 font-mono text-[11px] bg-slate-950 text-slate-200 whitespace-pre-wrap leading-relaxed select-all" id="personalPromptInspectorContent">
                        </div>
                        <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button type="button" onclick="document.getElementById('personalPromptInspectorModal').classList.add('hidden')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-100">Close</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function loadPersonalPreset(type) {
    const textarea = document.getElementById('personalPromptTextarea');
    if (type === 'chief') {
        textarea.value = "You are the Executive Chief of Staff to Boss. Proactively manage daily schedules, triage priority communications, coordinate outbound emails, and execute connected tools with high precision. Keep briefings concise, structured, and action-oriented.";
    } else if (type === 'direct') {
        textarea.value = "You are Boss's direct executive operator. Execute requested tools immediately without unnecessary filler or pleasantries. Always confirm parameters from previous messages and report execution outcomes crisp and clean.";
    } else if (type === 'wingman') {
        textarea.value = "You are Boss's trusted 24/7 personal assistant on WhatsApp. Help with daily tasks, reminders, drafts, and tool actions with a natural, conversational, and energetic tone.";
    }
}

function previewPersonalPrompt() {
    const persona = document.getElementById('personalPromptTextarea').value.trim();
    const modal = document.getElementById('personalPromptInspectorModal');
    const content = document.getElementById('personalPromptInspectorContent');

    const samplePrompt = persona + 
`\n\nOPERATING DIRECTIVES:
1. Strict Grounding: State facts ONLY from verified executed tool results.
2. Autonomous Tool Execution: Proactively execute active connected tools conforming to schemas.
3. Multi-Turn Coreference & Parameter Resolution: Synthesize arguments from recent turns; execute immediately.
4. Silence Feedback: Personal assistant mode active. Never ask for customer reviews or ratings.

--- ACTIVE CONNECTED TOOLS ---
• Google Calendar [slug: gcalendar]: Schedule and inspect events
• SMTP Email [slug: send_email]: Dispatch correspondence
CAPABILITY BOUNDARIES: Execute real tool calls only. Never pretend an action was executed.

--- REAL-TIME TEMPORAL ANCHOR ---
Today is ` + new Date().toLocaleString() + `.

--- CALLER CONTEXT ---
You are talking directly with Boss. External callers are screened and restricted from tool commands.`;

    content.textContent = samplePrompt;
    modal.classList.remove('hidden');
}
</script>
