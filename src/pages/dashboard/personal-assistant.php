<?php
declare(strict_types=1);

$providerNames = [
    'gemini'     => 'Google Gemini',
    'groq'       => 'Groq Cloud',
    'custom'     => 'Custom API (OpenAI Compatible)',
];

$toolsCount = (int) ($toolsCount ?? 0);
$deviceConnected = (bool) ($deviceConnected ?? false);
$devicePhone = (string) ($devicePhone ?? '');
$adminPhones = (string) ($adminPhones ?? '');
$ownerName = (string) ($ownerName ?? 'Boss');
$personalPersona = (string) ($personalPersona ?? '');
$personalInternalPrompt = (string) ($personalInternalPrompt ?? '');
$screeningPolicy = (string) ($screeningPolicy ?? '');
$activeToolsList = (array) ($activeToolsList ?? []);
$aiSettings = (array) ($aiSettings ?? []);
$temperature = (float) ($aiSettings['temperature'] ?? 0.40);
$maxTokens = (int) ($aiSettings['max_tokens'] ?? 500);
$isAssistantEnabled = !empty($aiSettings['enabled']);
$currentProvider = (string) ($aiSettings['provider_priority'] ?? 'gemini');
?>

<div class="space-y-5 mb-8 max-w-7xl mx-auto">
    <!-- Top Studio Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-purple-100 text-purple-700 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Personal Assistant Studio</span>
                        <?php if ($deviceConnected): ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                WhatsApp Linked <?= $devicePhone !== '' ? '(' . e($devicePhone) . ')' : '' ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                WhatsApp Unpaired
                            </span>
                        <?php endif; ?>
                    </h1>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 pl-10.5">Autonomous executive companion for task delegation, calendar scheduling, email dispatch, and screening.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 shrink-0 pl-10.5 sm:pl-0">
            <a href="<?= e(base_url('index.php?page=tools')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a4 4 0 0 0-5.6 5.6l-6.5 6.5a2 2 0 0 0 2.8 2.8l6.5-6.5a4 4 0 0 0 5.6-5.6l-3 3-3-3z"/></svg>
                <span>Installed Tools (<?= $toolsCount ?>)</span>
            </a>
            <button type="button" onclick="document.getElementById('personalAgentForm').requestSubmit()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-subtle transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Changes</span>
                <kbd class="hidden md:inline-block ml-1 px-1.5 py-0.2 text-[9px] font-mono text-slate-400 bg-slate-800 rounded border border-slate-700">Ctrl+S</kbd>
            </button>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form id="personalAgentForm" method="post" action="<?= e(base_url('index.php?page=ai-agent-save')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="agent_mode" value="personal">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- LEFT COLUMN: Identity, Screening & Intelligence Config (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card 1: Engine Status & Owner Identification -->
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg <?= $isAssistantEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-500' ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Assistant State</h2>
                                <span class="text-[11px] font-medium <?= $isAssistantEnabled ? 'text-emerald-700' : 'text-slate-400' ?>">
                                    <?= $isAssistantEnabled ? 'Active & Processing Messages' : 'Paused (Manual WhatsApp Only)' ?>
                                </span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" <?= $isAssistantEnabled ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Owner Profile -->
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="ownerNameInput" class="block text-xs font-semibold text-slate-700">Owner / Principal Name</label>
                                <span class="text-[10px] text-slate-400 font-mono">Dynamic tag: {{owner}}</span>
                            </div>
                            <input type="text"
                                   id="ownerNameInput"
                                   name="owner_name"
                                   value="<?= e($ownerName) ?>"
                                   placeholder="e.g. Navneet, Alex, Sarah"
                                   oninput="updateCompiledPreview()"
                                   class="w-full text-xs font-medium rounded-lg border border-slate-200 bg-slate-50/50 p-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all">
                            <p class="text-[10px] text-slate-400 mt-1">The assistant recognizes you as its direct principal and addresses you accordingly.</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">Authorized Admin WhatsApp Number(s)</label>
                                <span class="text-[10px] font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200/60">Tool Access</span>
                            </div>
                            <input type="text"
                                   name="admin_phones"
                                   value="<?= e($adminPhones) ?>"
                                   placeholder="e.g. 919876543210, 14155552671"
                                   class="w-full text-xs font-mono rounded-lg border border-slate-200 bg-slate-50/50 p-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all">
                            <p class="text-[10px] text-slate-400 mt-1">Comma-separated international numbers. <strong>Only these numbers</strong> are authorized to trigger autonomous tools (Calendar, Email, Memory).</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Contact & Guest Screening Policy -->
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-card p-4 sm:p-5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Guest Screening Policy</h2>
                        </div>
                        <span class="text-[10px] font-medium text-slate-400">Non-admin callers</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">What the assistant replies when external contacts or unverified numbers message your personal WhatsApp:</p>
                    <textarea name="screening_policy"
                              id="screeningPolicyInput"
                              rows="2"
                              oninput="updateCompiledPreview()"
                              class="w-full text-xs font-normal rounded-lg border border-slate-200 bg-slate-50/50 p-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all leading-relaxed"><?= e($screeningPolicy) ?></textarea>
                </div>

                <!-- Card 3: Model & Provider Parameters -->
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-card p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Model Parameters</h2>
                        </div>
                        <a href="<?= e(base_url('index.php?page=ai-credentials')) ?>" class="text-[11px] font-semibold text-purple-700 hover:text-purple-900 transition-colors">
                            API Keys &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Primary LLM Provider</label>
                            <select name="provider_priority" class="w-full text-xs font-semibold rounded-lg border border-slate-200 bg-slate-50/50 p-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600">
                                <?php foreach ($providerNames as $pk => $pv): ?>
                                    <option value="<?= $pk ?>" <?= $currentProvider === $pk ? 'selected' : '' ?>><?= $pv ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Tokens</label>
                            <input type="number"
                                   name="max_tokens"
                                   value="<?= e((string)$maxTokens) ?>"
                                   min="100"
                                   max="2000"
                                   step="50"
                                   class="w-full text-xs font-mono rounded-lg border border-slate-200 bg-slate-50/50 p-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600">
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-700 mb-1.5">
                            <span>Sampling Temperature</span>
                            <span id="tempValueDisplay" class="font-mono text-purple-700"><?= number_format($temperature, 2) ?></span>
                        </div>
                        <input type="range"
                               name="temperature"
                               id="tempRangeInput"
                               min="0.0"
                               max="1.0"
                               step="0.05"
                               value="<?= e((string)$temperature) ?>"
                               oninput="document.getElementById('tempValueDisplay').textContent = parseFloat(this.value).toFixed(2)"
                               class="w-full accent-purple-600 cursor-pointer">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                            <span>0.0 (Strict & Determinate)</span>
                            <span>0.5 (Balanced)</span>
                            <span>1.0 (Creative)</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: The Unified Prompt & Directives Studio (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Main Studio Card with Segmented Switcher -->
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-card overflow-hidden">
                    
                    <!-- Top Segmented Tab Switcher Header -->
                    <div class="p-3.5 bg-slate-50/70 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <!-- Segmented Switcher Controls -->
                        <div class="inline-flex p-1 bg-slate-200/70 rounded-lg text-xs font-semibold" role="tablist">
                            <button type="button"
                                    id="tabBtnPersona"
                                    onclick="switchStudioTab('persona')"
                                    class="studio-tab-btn active px-3 py-1.5 rounded-md transition-all cursor-pointer bg-white text-slate-900 shadow-2xs font-bold inline-flex items-center gap-1.5"
                                    role="tab"
                                    aria-selected="true">
                                <svg class="w-3.5 h-3.5 shrink-0 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Persona & Tone</span>
                            </button>
                            <button type="button"
                                    id="tabBtnProtocol"
                                    onclick="switchStudioTab('protocol')"
                                    class="studio-tab-btn px-3 py-1.5 rounded-md transition-all cursor-pointer text-slate-600 hover:text-slate-900 font-medium inline-flex items-center gap-1.5"
                                    role="tab"
                                    aria-selected="false">
                                <svg class="w-3.5 h-3.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Execution Protocol</span>
                            </button>
                            <button type="button"
                                    id="tabBtnPreview"
                                    onclick="switchStudioTab('preview')"
                                    class="studio-tab-btn px-3 py-1.5 rounded-md transition-all cursor-pointer text-slate-600 hover:text-slate-900 font-medium inline-flex items-center gap-1.5"
                                    role="tab"
                                    aria-selected="false">
                                <svg class="w-3.5 h-3.5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Live Compiled Prompt</span>
                            </button>
                        </div>

                        <!-- Secondary Right Action (Context-sensitive per tab) -->
                        <div id="tabActionContainer" class="flex items-center gap-2">
                            <!-- Reset button shown on Protocol tab -->
                            <div id="protocolResetBar" class="hidden">
                                <button type="button" onclick="resetPersonalInternal()" class="text-[11px] font-semibold text-slate-500 hover:text-purple-700 flex items-center gap-1 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reset to Default Protocol</span>
                                </button>
                            </div>

                            <!-- Copy button shown on Preview tab -->
                            <div id="previewCopyBar" class="hidden">
                                <button type="button" onclick="copyCompiledPrompt()" id="copyPromptBtn" class="text-[11px] font-semibold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-2.5 py-1 rounded-md border border-purple-200/60 transition-colors flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span id="copyPromptBtnLabel">Copy Compiled</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 1 PANEL: Persona & Tone -->
                    <div id="panelPersona" class="p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Assistant Persona & Tone</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Establishes voice, personality, rapport with the owner, and general conversational style.</p>
                            </div>
                            <span id="personaCharCount" class="text-[10px] font-mono text-slate-400">0 chars</span>
                        </div>

                        <!-- Presets Bar (Positioned directly under header and above prompt) -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                            <span class="text-[10px] uppercase font-bold text-slate-400 mr-1">Presets:</span>
                            <button type="button" onclick="loadPersonalPreset('chief')" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 border border-slate-200 rounded-md shadow-3xs transition-colors cursor-pointer">Chief of Staff</button>
                            <button type="button" onclick="loadPersonalPreset('direct')" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 border border-slate-200 rounded-md shadow-3xs transition-colors cursor-pointer">Direct Operator</button>
                            <button type="button" onclick="loadPersonalPreset('wingman')" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 border border-slate-200 rounded-md shadow-3xs transition-colors cursor-pointer">Wingman</button>
                        </div>

                        <div class="relative">
                            <textarea id="personalPromptTextarea"
                                      name="personal_system_prompt"
                                      rows="12"
                                      oninput="updateCounts(); updateCompiledPreview();"
                                      class="w-full text-xs font-mono leading-relaxed rounded-xl border border-slate-200 bg-slate-50/40 p-3.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all text-slate-800 select-text"><?= e($personalPersona) ?></textarea>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>Use presets above or customize your assistant's identity freely.</span>
                            <span class="font-mono text-[10px] text-purple-700 font-semibold">Active in Personal Mode</span>
                        </div>
                    </div>

                    <!-- TAB 2 PANEL: Internal Operating Directives (Execution Protocol) -->
                    <div id="panelProtocol" class="hidden p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Internal Operating Directives (Execution Protocol)</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Strict system rules governing parameter extraction, autonomous tool syntax, and factual grounding.</p>
                            </div>
                            <span id="protocolCharCount" class="text-[10px] font-mono text-slate-400">0 chars</span>
                        </div>

                        <div class="relative">
                            <textarea id="personalInternalTextarea"
                                      name="personal_internal_prompt"
                                      rows="12"
                                      oninput="updateCounts(); updateCompiledPreview();"
                                      class="w-full text-xs font-mono leading-relaxed rounded-xl border border-slate-200 bg-slate-50/40 p-3.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all text-slate-800 select-text"><?= e($personalInternalPrompt) ?></textarea>
                        </div>
                        <p class="text-[10px] text-slate-400">These instructions prevent customer support surveys, suppress hallucinated outcomes, and enforce strict execution of tool calls.</p>
                    </div>

                    <!-- TAB 3 PANEL: Live Compiled Prompt Preview -->
                    <div id="panelPreview" class="hidden p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Live Assembled System Prompt</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Exact prompt compiled with persona, protocol, temporal anchor, and active connected tools.</p>
                            </div>
                            <span id="previewEstTokens" class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200/60">~0 tokens</span>
                        </div>

                        <div class="rounded-xl bg-slate-950 p-4 border border-slate-800 shadow-inner overflow-hidden">
                            <pre id="compiledPromptDisplay" class="font-mono text-[11px] text-slate-300 whitespace-pre-wrap leading-relaxed max-h-[380px] overflow-y-auto custom-scrollbar select-all"></pre>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Dynamic variables ({{owner}}, active tools, real-time date) update automatically in the worker.</span>
                            <span class="font-mono text-emerald-400">● Live preview ready</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </form>
</div>

<script>
(function() {
    // 1. Keyboard Shortcut: Ctrl+S / Cmd+S to submit form
    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            var activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (activeTag === 'textarea' || activeTag === 'input') {
                e.preventDefault();
                var form = document.getElementById('personalAgentForm');
                if (form) form.requestSubmit();
            }
        }
    });

    // 2. Studio Tab Switching
    window.switchStudioTab = function(tabName) {
        var panels = {
            persona: document.getElementById('panelPersona'),
            protocol: document.getElementById('panelProtocol'),
            preview: document.getElementById('panelPreview')
        };
        var buttons = {
            persona: document.getElementById('tabBtnPersona'),
            protocol: document.getElementById('tabBtnProtocol'),
            preview: document.getElementById('tabBtnPreview')
        };
        var actionBars = {
            protocol: document.getElementById('protocolResetBar'),
            preview: document.getElementById('previewCopyBar')
        };

        for (var key in panels) {
            if (!panels[key] || !buttons[key]) continue;
            var isActive = (key === tabName);
            panels[key].classList.toggle('hidden', !isActive);

            if (isActive) {
                buttons[key].className = 'studio-tab-btn active px-3 py-1.5 rounded-md transition-all cursor-pointer bg-white text-slate-900 shadow-2xs font-bold inline-flex items-center gap-1.5';
                buttons[key].setAttribute('aria-selected', 'true');
            } else {
                buttons[key].className = 'studio-tab-btn px-3 py-1.5 rounded-md transition-all cursor-pointer text-slate-600 hover:text-slate-900 font-medium inline-flex items-center gap-1.5';
                buttons[key].setAttribute('aria-selected', 'false');
            }

            if (actionBars[key]) {
                actionBars[key].classList.toggle('hidden', !isActive);
            }
        }

        if (tabName === 'preview') {
            updateCompiledPreview();
        }
    };

    // 3. Owner Name Helper
    window.getOwnerRef = function() {
        var input = document.getElementById('ownerNameInput');
        return (input && input.value.trim()) ? input.value.trim() : 'the owner';
    };

    // 4. Presets Loader
    window.loadPersonalPreset = function(type) {
        var textarea = document.getElementById('personalPromptTextarea');
        if (!textarea) return;
        var owner = window.getOwnerRef();

        if (type === 'chief') {
            textarea.value = "You are the Executive Chief of Staff to " + owner + ". Proactively manage daily schedules, triage priority communications, coordinate outbound correspondence, and execute connected tools with high precision. Keep briefings concise, structured, and action-oriented.";
        } else if (type === 'direct') {
            textarea.value = "You are " + owner + "'s direct executive operator. Execute requested tools immediately without unnecessary filler or pleasantries. Always confirm parameters from previous messages and report execution outcomes clearly.";
        } else if (type === 'wingman') {
            textarea.value = "You are " + owner + "'s trusted personal assistant on WhatsApp. Help with daily tasks, reminders, drafts, and tool actions with a natural, conversational, and energetic tone.";
        }
        updateCounts();
        updateCompiledPreview();
    };

    // 5. Reset Protocol
    window.resetPersonalInternal = function() {
        var textarea = document.getElementById('personalInternalTextarea');
        if (!textarea) return;
        textarea.value = 
`1. Strict Grounding: State facts ONLY from verified executed tool results. Never invent records, prices, phone numbers, or dates.
2. Autonomous Tool Execution: Proactively execute your active connected tools whenever requested, strictly conforming to each tool's argument schema.
3. Multi-Turn Coreference & Parameter Resolution: When instructed to perform an action on previously mentioned content, autonomously extract all required arguments and execute immediately.
4. Formatting: Crisp, proactive, structured. Use *bold* highlights, clean bullet points, and appropriate emojis.
5. Silence Feedback: You are a personal assistant, NOT a customer service line. NEVER ask for customer reviews, 5-star ratings, or feedback surveys.`;
        updateCounts();
        updateCompiledPreview();
    };

    // 6. Character & Token Counting
    window.updateCounts = function() {
        var pText = document.getElementById('personalPromptTextarea');
        var iText = document.getElementById('personalInternalTextarea');
        var pCount = document.getElementById('personaCharCount');
        var iCount = document.getElementById('protocolCharCount');

        if (pText && pCount) {
            pCount.textContent = pText.value.length + ' chars';
        }
        if (iText && iCount) {
            iCount.textContent = iText.value.length + ' chars';
        }
    };

    // 7. Live Compiled Prompt Assembly
    window.updateCompiledPreview = function() {
        var pText = document.getElementById('personalPromptTextarea');
        var iText = document.getElementById('personalInternalTextarea');
        var sInput = document.getElementById('screeningPolicyInput');
        var display = document.getElementById('compiledPromptDisplay');
        var tokenBadge = document.getElementById('previewEstTokens');

        var persona = pText ? pText.value.trim() : '';
        var protocol = iText ? iText.value.trim() : '';
        var screening = sInput ? sInput.value.trim() : '';
        var owner = window.getOwnerRef();

        var activeTools = <?= json_encode($activeToolsList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        var toolListText = (Array.isArray(activeTools) && activeTools.length > 0)
            ? activeTools.map(function(t) { return '• ' + (t.display_name || t.tool_slug) + ' [slug: ' + t.tool_slug + ']: ' + (t.description || 'Active utility'); }).join('\n')
            : '• NONE (You have NO connected function tools)';

        var toolGrounding = "\n\n=== ACTIVE CONNECTED TOOLS ===\n" + toolListText + "\n\n" +
            "CAPABILITY BOUNDARIES (ZERO HALLUCINATION):\n" +
            "1. You have ONLY the active connected tools listed above. List only these if asked.\n" +
            "2. If asked about any unsupported capability, state explicitly that you do not have that tool.\n" +
            "3. NEVER simulate or roleplay actions: Execute real tool calls only. Never pretend an action was executed.";

        var compiled = persona + "\n\n" +
            "=== OPERATING DIRECTIVES & EXECUTION PROTOCOL ===\n" +
            protocol +
            toolGrounding + "\n\n" +
            "=== REAL-TIME TEMPORAL ANCHOR ===\n" +
            "Today is " + new Date().toLocaleString() + ". Calculate all relative dates from this anchor.\n\n" +
            "=== CALLER CONTEXT & AUTHORIZATION ===\n" +
            "You are talking directly with " + owner + ". External callers are screened and restricted from executing tools.";

        if (screening !== '') {
            compiled += "\n\n=== GUEST SCREENING POLICY ===\n" + screening;
        }

        if (display) {
            display.textContent = compiled;
        }

        if (tokenBadge) {
            // Rough estimate: ~4 chars per token
            var estTokens = Math.round(compiled.length / 4);
            tokenBadge.textContent = '~' + estTokens.toLocaleString() + ' tokens';
        }
    };

    // 8. Copy to Clipboard
    window.copyCompiledPrompt = function() {
        var display = document.getElementById('compiledPromptDisplay');
        if (!display) return;
        navigator.clipboard.writeText(display.textContent).then(function() {
            var label = document.getElementById('copyPromptBtnLabel');
            if (label) {
                var oldText = label.textContent;
                label.textContent = 'Copied!';
                setTimeout(function() {
                    label.textContent = oldText;
                }, 2000);
            }
        });
    };

    // Initial run on mount
    updateCounts();
    updateCompiledPreview();
})();
</script>
