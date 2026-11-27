<?php
/**
 * AI Provider Keys & User SMTP Credentials
 * Linear/Stripe-inspired clean developer settings layout:
 * - Ultra-low cognitive load: 2 simple tabs (AI Model Keys vs SMTP Mailers)
 * - Clean high-density tables instead of bloated card grids
 * - Zero visual clutter, zero redundant KPI cards
 * - 1-click test and copy actions
 */

$totalAiKeys = count($allKeys);
$activeAiKeys = count(array_filter($allKeys, fn($k) => (int) ($k['is_active'] ?? 0) === 1));
$totalSmtp = count($smtpServers);
$activeSmtp = count(array_filter($smtpServers, fn($s) => (int) ($s['is_active'] ?? 0) === 1));
$providerPriority = strtoupper((string) ($aiSettings['provider_priority'] ?? 'GEMINI'));
?>

<div class="space-y-5 mb-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                AI Keys &amp; SMTP Outbound
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage credentials for Google Gemini, Groq, Custom API (OpenAI Compatible), and SMTP relays.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddAiKeyModal('gemini')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add AI Key</span>
            </button>
            <button type="button" onclick="openAddSmtpModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Add SMTP</span>
            </button>
        </div>
    </div>

    <!-- Clean Two-Tab Navigation -->
    <div class="flex items-center justify-between border-b border-slate-200 gap-4">
        <nav class="flex space-x-6 text-xs font-semibold" aria-label="Tabs">
            <button type="button" onclick="switchMainTab('ai')" id="mainTabBtn-ai" class="main-tab-btn active pb-2.5 text-slate-900 border-b-2 border-slate-900 flex items-center gap-2 cursor-pointer font-bold">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>AI Provider Keys</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600 border border-slate-200 font-mono"><?= $totalAiKeys ?></span>
            </button>
            <button type="button" onclick="switchMainTab('smtp')" id="mainTabBtn-smtp" class="main-tab-btn pb-2.5 text-slate-500 hover:text-slate-800 border-b-2 border-transparent flex items-center gap-2 cursor-pointer font-medium transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>SMTP Outbound Relays</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-600 border border-slate-200 font-mono"><?= $totalSmtp ?></span>
            </button>
        </nav>

        <span class="text-[11px] font-mono text-slate-400 hidden sm:inline-block">
            Cascade Priority: <strong class="text-slate-700 font-semibold"><?= e($providerPriority) ?></strong>
        </span>
    </div>

    <!-- TAB 1: AI PROVIDER KEYS TABLE -->
    <div id="panel-ai" class="space-y-3">
        <?php if (empty($allKeys)): ?>
            <div class="py-12 text-center text-xs text-slate-400 bg-white rounded-xl border border-dashed border-slate-200 p-6">
                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <p class="font-bold text-slate-700 text-sm">No AI keys configured yet</p>
                <p class="text-slate-400 mt-1 max-w-sm mx-auto">Add your Google Gemini, Groq, or Custom API key to activate autonomous capabilities.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button type="button" onclick="openAddAiKeyModal('gemini')" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs cursor-pointer">
                        + Add Gemini Key
                    </button>
                    <button type="button" onclick="openAddAiKeyModal('groq')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs cursor-pointer">
                        + Add Groq Key
                    </button>
                    <button type="button" onclick="openAddAiKeyModal('custom')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs cursor-pointer">
                        + Add Custom Key
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Mobile Card View (sm:hidden) -->
            <div class="sm:hidden space-y-3">
                <?php foreach ($allKeys as $idx => $k): ?>
                    <?php
                    $prov = $k['provider'];
                    $badgeClass = match($prov) {
                        'gemini'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'groq'       => 'bg-amber-50 text-amber-700 border-amber-200',
                        'custom', 'openrouter' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        default      => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                    $provLabel = match($prov) {
                        'gemini'     => 'Gemini',
                        'groq'       => 'Groq',
                        'custom', 'openrouter' => 'Custom API',
                        default      => strtoupper($prov),
                    };
                    $rawKey = (string)$k['api_key'];
                    $cleanKey = str_starts_with($rawKey, 'enc:v1:') ? decrypt_credential($rawKey) : $rawKey;
                    $maskedKey = (strlen($cleanKey) > 11) ? (substr($cleanKey, 0, 7) . '••••••••' . substr($cleanKey, -4)) : (strlen($cleanKey) > 4 ? substr($cleanKey, 0, 3) . '••••' : '••••••••');
                    ?>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border <?= $badgeClass ?>">
                                    <?= e($provLabel) ?>
                                </span>
                                <span class="font-mono text-[10px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60">
                                    #<?= e((string)$k['priority']) ?><?= (int)$k['priority'] === 1 ? ' Primary' : '' ?>
                                </span>
                            </div>
                            <div>
                                <?php if ((int)$k['is_active'] === 0): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Paused
                                    </span>
                                <?php elseif ($k['status'] === 'rate_limited'): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 429
                                    </span>
                                <?php elseif ($k['status'] === 'error'): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200" title="<?= e($k['last_error'] ?? '') ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Error
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900 text-sm leading-snug"><?= e($k['label']) ?></h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                <?= !empty($k['last_used_at']) ? 'Last used ' . e(date('M j, Y H:i', strtotime($k['last_used_at']))) : 'Never used' ?>
                            </p>
                        </div>

                        <div class="space-y-1.5 bg-slate-50/70 p-2.5 rounded-lg border border-slate-100 font-mono text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Key</span>
                                <div class="inline-flex items-center gap-1.5 text-slate-700 text-[11px]">
                                    <span><?= e($maskedKey) ?></span>
                                    <button type="button" onclick="copyKeyText('<?= e($cleanKey) ?>', this)" class="text-slate-400 hover:text-slate-700 p-0.5 rounded cursor-pointer" title="Copy Key">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Model</span>
                                <span class="text-slate-800 font-semibold text-[11px]"><?= e($k['model'] ?: 'default') ?></span>
                            </div>
                            <?php if (!empty($k['endpoint'])): ?>
                                <div class="flex flex-col pt-1 border-t border-slate-200/50">
                                    <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-0.5">Endpoint</span>
                                    <span class="text-slate-600 text-[10px] break-all"><?= e($k['endpoint']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="relative inline-block text-left w-full" data-dropdown-scope>
                                <button type="button" onclick="toggleActionMenu(this, event)" class="action-menu-btn w-full inline-flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg shadow-2xs transition-all cursor-pointer">
                                    <span>Actions</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dropdown-caret transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="action-dropdown hidden absolute left-0 right-0 z-40 mt-1.5 w-full rounded-xl bg-white border border-slate-200 shadow-lg py-1 focus:outline-none text-left">
                                    <button type="button" onclick="testAiKey(<?= $k['id'] ?>, '<?= e($k['provider']) ?>'); closeAllActionMenus();" id="btn-test-m-<?= $k['id'] ?>" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Test Authentication</span>
                                    </button>
                                    <button type="button" onclick="toggleKeyActive(<?= $k['id'] ?>, <?= (int)$k['is_active'] === 1 ? 0 : 1 ?>); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <?php if ((int)$k['is_active'] === 1): ?>
                                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Pause Key</span>
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Activate Key</span>
                                        <?php endif; ?>
                                    </button>
                                    <button type="button" onclick="editAiKey(<?= htmlspecialchars(json_encode(array_merge($k, ['api_key' => $cleanKey])), ENT_QUOTES, 'UTF-8') ?>); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit Configuration</span>
                                    </button>
                                    <button type="button" onclick="copyKeyText('<?= e($cleanKey) ?>', this); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>Copy API Key</span>
                                    </button>
                                    <div class="my-1 border-t border-slate-100"></div>
                                    <button type="button" onclick="deleteAiKey(<?= $k['id'] ?>, '<?= e($k['label']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete Key</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop Table View (hidden sm:block) -->
            <div class="hidden sm:block bg-white rounded-xl border border-slate-200 shadow-2xs overflow-x-auto">
                <div>
                    <table class="w-full min-w-[760px] text-left text-xs text-slate-600 table-fixed">
                        <colgroup>
                            <col class="w-[28%]">
                            <col class="w-[22%]">
                            <col class="w-[22%]">
                            <col class="w-[10%]">
                            <col class="w-[9%]">
                            <col style="width: 110px;">
                        </colgroup>
                        <thead class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200 tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Provider &amp; Label</th>
                                <th class="px-4 py-3">API Key</th>
                                <th class="px-4 py-3">Model &amp; Endpoint</th>
                                <th class="px-4 py-3">Priority</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            <?php foreach ($allKeys as $idx => $k): ?>
                                <?php
                                $prov = $k['provider'];
                                $badgeClass = match($prov) {
                                    'gemini'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'groq'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'custom', 'openrouter' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default      => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                                $provLabel = match($prov) {
                                    'gemini'     => 'Gemini',
                                    'groq'       => 'Groq',
                                    'custom', 'openrouter' => 'Custom API',
                                    default      => strtoupper($prov),
                                };
                                $rawKey = (string)$k['api_key'];
                                $cleanKey = str_starts_with($rawKey, 'enc:v1:') ? decrypt_credential($rawKey) : $rawKey;
                                $maskedKey = (strlen($cleanKey) > 11) ? (substr($cleanKey, 0, 7) . '••••••••' . substr($cleanKey, -4)) : (strlen($cleanKey) > 4 ? substr($cleanKey, 0, 3) . '••••' : '••••••••');
                                ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- Provider & Label -->
                                    <td class="px-4 py-3.5 align-middle">
                                        <div class="flex items-center gap-2.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border <?= $badgeClass ?> shrink-0">
                                                <?= e($provLabel) ?>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-900 block truncate" title="<?= e($k['label']) ?>"><?= e($k['label']) ?></span>
                                                <span class="text-[10px] text-slate-400 block">
                                                    <?= !empty($k['last_used_at']) ? 'Used ' . e(date('M j, H:i', strtotime($k['last_used_at']))) : 'Never used' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Masked Key + Copy -->
                                    <td class="px-4 py-3.5 align-middle">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-50 border border-slate-200/80 font-mono text-[11px] text-slate-700 max-w-full">
                                            <span class="truncate"><?= e($maskedKey) ?></span>
                                            <button type="button" onclick="copyKeyText('<?= e($cleanKey) ?>', this)" class="text-slate-400 hover:text-slate-700 p-0.5 rounded cursor-pointer shrink-0" title="Copy Key">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Model & Endpoint -->
                                    <td class="px-4 py-3.5 align-middle">
                                        <div class="flex flex-col gap-0.5 min-w-0">
                                            <span class="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200/60 w-fit truncate max-w-full">
                                                <?= e($k['model'] ?: 'default') ?>
                                            </span>
                                            <?php if (!empty($k['endpoint'])): ?>
                                                <span class="text-[10px] font-mono text-slate-400 truncate max-w-full" title="<?= e($k['endpoint']) ?>">
                                                    <?= e($k['endpoint']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Priority -->
                                    <td class="px-4 py-3.5 align-middle">
                                        <span class="font-mono text-[11px] font-bold text-slate-700">
                                            #<?= e((string)$k['priority']) ?>
                                            <?php if ((int)$k['priority'] === 1): ?>
                                                <span class="text-[9px] font-semibold text-emerald-600 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 ml-1">Primary</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3.5 align-middle whitespace-nowrap">
                                        <?php if ((int)$k['is_active'] === 0): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Paused
                                            </span>
                                        <?php elseif ($k['status'] === 'rate_limited'): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 429
                                            </span>
                                        <?php elseif ($k['status'] === 'error'): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200" title="<?= e($k['last_error'] ?? '') ?>">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Error
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap align-middle" style="width: 110px;">
                                        <div class="relative inline-block text-left" data-dropdown-scope>
                                            <button type="button" onclick="toggleActionMenu(this, event)" class="action-menu-btn inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-lg shadow-2xs transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                                                <span>Actions</span>
                                                <svg class="w-3.5 h-3.5 text-slate-400 dropdown-caret transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div class="action-dropdown hidden absolute right-0 z-40 mt-1.5 w-48 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg py-1 focus:outline-none text-left">
                                                <button type="button" onclick="testAiKey(<?= $k['id'] ?>, '<?= e($k['provider']) ?>'); closeAllActionMenus();" id="btn-test-<?= $k['id'] ?>" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    <span>Test Authentication</span>
                                                </button>
                                                <button type="button" onclick="toggleKeyActive(<?= $k['id'] ?>, <?= (int)$k['is_active'] === 1 ? 0 : 1 ?>); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <?php if ((int)$k['is_active'] === 1): ?>
                                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Pause Key</span>
                                                    <?php else: ?>
                                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Activate Key</span>
                                                    <?php endif; ?>
                                                </button>
                                                <button type="button" onclick="editAiKey(<?= htmlspecialchars(json_encode(array_merge($k, ['api_key' => $cleanKey])), ENT_QUOTES, 'UTF-8') ?>); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    <span>Edit Configuration</span>
                                                </button>
                                                <button type="button" onclick="copyKeyText('<?= e($cleanKey) ?>', this); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    <span>Copy API Key</span>
                                                </button>
                                                <div class="my-1 border-t border-slate-100"></div>
                                                <button type="button" onclick="deleteAiKey(<?= $k['id'] ?>, '<?= e($k['label']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Delete Key</span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB 2: SMTP OUTBOUND RELAYS TABLE -->
    <div id="panel-smtp" class="hidden space-y-3">
        <?php if (empty($smtpServers)): ?>
            <div class="py-12 text-center text-xs text-slate-400 bg-white rounded-xl border border-dashed border-slate-200 p-6">
                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="font-bold text-slate-700 text-sm">No SMTP mailers connected</p>
                <p class="text-slate-400 mt-1 max-w-sm mx-auto">Connect Google Workspace, Brevo, SendGrid, or custom SMTP to enable autonomous email dispatching.</p>
                <button type="button" onclick="openAddSmtpModal()" class="mt-4 px-3 py-1.5 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow-2xs cursor-pointer inline-flex items-center gap-1.5">
                    + Add First SMTP Server
                </button>
            </div>
        <?php else: ?>
            <!-- Mobile Card View (sm:hidden) -->
            <div class="sm:hidden space-y-3">
                <?php foreach ($smtpServers as $idx => $s): ?>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-purple-50 text-purple-700 border-purple-200">
                                    SMTP
                                </span>
                                <span class="font-mono text-[10px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60">
                                    #<?= e((string)$s['priority']) ?><?= $idx === 0 ? ' Default' : '' ?>
                                </span>
                            </div>
                            <div>
                                <?php if ((int)$s['is_active'] === 0): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Paused
                                    </span>
                                <?php elseif ($s['status'] === 'auth_failed'): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Auth Failed
                                    </span>
                                <?php elseif ($s['status'] === 'connection_failed'): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Conn Failed
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Connected
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900 text-sm leading-snug"><?= e($s['label']) ?></h3>
                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                                <?= e($s['host']) ?>:<?= e((string)$s['port']) ?> (<?= e(strtoupper($s['encryption'])) ?>)
                            </p>
                        </div>

                        <div class="bg-slate-50/70 p-2.5 rounded-lg border border-slate-100 text-xs">
                            <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block mb-0.5">From Address</span>
                            <span class="font-medium text-slate-800 text-[11px] block"><?= e($s['from_email']) ?></span>
                            <?php if (!empty($s['from_name'])): ?>
                                <span class="text-[10px] text-slate-400 block"><?= e($s['from_name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="relative inline-block text-left w-full" data-dropdown-scope>
                                <button type="button" onclick="toggleActionMenu(this, event)" class="action-menu-btn w-full inline-flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg shadow-2xs transition-all cursor-pointer">
                                    <span>Actions</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 dropdown-caret transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="action-dropdown hidden absolute left-0 right-0 z-40 mt-1.5 w-full rounded-xl bg-white border border-slate-200 shadow-lg py-1 focus:outline-none text-left">
                                    <button type="button" onclick="testSmtp(<?= $s['id'] ?>); closeAllActionMenus();" id="btn-test-smtp-m-<?= $s['id'] ?>" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Test Connection Handshake</span>
                                    </button>
                                    <button type="button" onclick="promptSendTestEmail(<?= $s['id'] ?>, '<?= e($s['from_email']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>Send Live Test Email</span>
                                    </button>
                                    <button type="button" onclick="toggleSmtpActive(<?= $s['id'] ?>, <?= (int)$s['is_active'] === 1 ? 0 : 1 ?>); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <?php if ((int)$s['is_active'] === 1): ?>
                                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Pause Mailer</span>
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Activate Mailer</span>
                                        <?php endif; ?>
                                    </button>
                                    <button type="button" onclick="editSmtp(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit Server Details</span>
                                    </button>
                                    <div class="my-1 border-t border-slate-100"></div>
                                    <button type="button" onclick="deleteSmtp(<?= $s['id'] ?>, '<?= e($s['label']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete Server</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop Table View (hidden sm:block) -->
            <div class="hidden sm:block bg-white rounded-xl border border-slate-200 shadow-2xs overflow-x-auto">
                <div>
                    <table class="w-full min-w-[760px] text-left text-xs text-slate-600 table-fixed">
                        <colgroup>
                            <col class="w-[25%]">
                            <col class="w-[22%]">
                            <col class="w-[23%]">
                            <col class="w-[11%]">
                            <col class="w-[9%]">
                            <col style="width: 110px;">
                        </colgroup>
                        <thead class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200 tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Server Label</th>
                                <th class="px-4 py-3">Host &amp; Port</th>
                                <th class="px-4 py-3">From Address</th>
                                <th class="px-4 py-3">Priority</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            <?php foreach ($smtpServers as $idx => $s): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- Label -->
                                    <td class="px-4 py-3.5 font-bold text-slate-900 align-middle truncate" title="<?= e($s['label']) ?>">
                                        <?= e($s['label']) ?>
                                    </td>

                                    <!-- Host & Encryption -->
                                    <td class="px-4 py-3.5 font-mono text-[11px] text-slate-800 align-middle">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span><?= e($s['host']) ?>:<?= e((string)$s['port']) ?></span>
                                            <span class="text-[9px] uppercase font-bold text-purple-700 bg-purple-50 px-1 py-0.2 rounded border border-purple-200"><?= e(strtoupper($s['encryption'])) ?></span>
                                        </div>
                                    </td>

                                    <!-- From Address -->
                                    <td class="px-4 py-3.5 align-middle min-w-0">
                                        <span class="font-medium text-slate-800 block truncate" title="<?= e($s['from_email']) ?>"><?= e($s['from_email']) ?></span>
                                        <span class="text-[10px] text-slate-400 block truncate"><?= e($s['from_name']) ?></span>
                                    </td>

                                    <!-- Priority -->
                                    <td class="px-4 py-3.5 font-mono text-[11px] font-bold text-slate-700 align-middle">
                                        #<?= e((string)$s['priority']) ?>
                                        <?php if ($idx === 0): ?>
                                            <span class="text-[9px] font-semibold text-purple-700 bg-purple-50 px-1 py-0.2 rounded border border-purple-200 ml-1">Default Outbound</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3.5 align-middle whitespace-nowrap">
                                        <?php if ((int)$s['is_active'] === 0): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Paused
                                            </span>
                                        <?php elseif ($s['status'] === 'auth_failed'): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Auth Failed
                                            </span>
                                        <?php elseif ($s['status'] === 'connection_failed'): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Conn Failed
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Connected
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap align-middle" style="width: 110px;">
                                        <div class="relative inline-block text-left" data-dropdown-scope>
                                            <button type="button" onclick="toggleActionMenu(this, event)" class="action-menu-btn inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-lg shadow-2xs transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                                                <span>Actions</span>
                                                <svg class="w-3.5 h-3.5 text-slate-400 dropdown-caret transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div class="action-dropdown hidden absolute right-0 z-40 mt-1.5 w-52 origin-top-right rounded-xl bg-white border border-slate-200 shadow-lg py-1 focus:outline-none text-left">
                                                <button type="button" onclick="testSmtp(<?= $s['id'] ?>); closeAllActionMenus();" id="btn-test-smtp-<?= $s['id'] ?>" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    <span>Test Connection Handshake</span>
                                                </button>
                                                <button type="button" onclick="promptSendTestEmail(<?= $s['id'] ?>, '<?= e($s['from_email']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    <span>Send Live Test Email</span>
                                                </button>
                                                <button type="button" onclick="toggleSmtpActive(<?= $s['id'] ?>, <?= (int)$s['is_active'] === 1 ? 0 : 1 ?>); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <?php if ((int)$s['is_active'] === 1): ?>
                                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Pause Mailer</span>
                                                    <?php else: ?>
                                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Activate Mailer</span>
                                                    <?php endif; ?>
                                                </button>
                                                <button type="button" onclick="editSmtp(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    <span>Edit Server Details</span>
                                                </button>
                                                <div class="my-1 border-t border-slate-100"></div>
                                                <button type="button" onclick="deleteSmtp(<?= $s['id'] ?>, '<?= e($s['label']) ?>'); closeAllActionMenus();" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Delete Server</span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Add / Edit AI Key Modal -->
<div id="aiKeyModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/70">
            <h3 id="modalKeyTitle" class="text-xs font-bold uppercase tracking-wider text-slate-900">Add AI API Key</h3>
            <button type="button" onclick="closeAiKeyModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <form id="aiKeyForm" onsubmit="handleSaveAiKey(event)" class="p-5 space-y-3.5">
            <input type="hidden" id="keyRecordId" name="id" value="">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Provider</label>
                    <select id="keyProvider" name="provider" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 cursor-pointer">
                        <option value="gemini">Google Gemini</option>
                        <option value="groq">Groq LPU</option>
                        <option value="custom">Custom API (OpenAI Compatible)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cascade Priority</label>
                    <input type="number" id="keyPriority" name="priority" value="1" min="1" max="99" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900">
                </div>
            </div>

            <div id="endpointContainer" class="hidden">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-semibold text-slate-700">API Endpoint / Base URL</label>
                    <span class="text-[10px] text-slate-400 font-mono">OpenAI Compatible</span>
                </div>
                <input type="text" id="keyEndpoint" name="endpoint" placeholder="https://api.openai.com/v1" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900">
                <p class="text-[10px] text-slate-400 mt-1">Works with OpenAI, DeepSeek, Together, Ollama, vLLM, or any custom provider.</p>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Configuration Label</label>
                <input type="text" id="keyLabel" name="label" required placeholder="e.g. Primary Flash Key, Team Groq" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-semibold text-slate-700">API Key String</label>
                    <span id="keyOptionalNotice" class="text-[10px] text-slate-400 hidden">(Keep unchanged if blank)</span>
                </div>
                <div class="relative">
                    <input type="password" id="keyString" name="api_key" placeholder="AIzaSy... or gsk_..." class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 pr-9 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900">
                    <button type="button" onclick="togglePasswordVisibility('keyString')" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-semibold text-slate-700">Model Identifier</label>
                    <span class="text-[10px] text-slate-400 font-mono">Optional</span>
                </div>
                <input type="text" id="keyModel" name="model" list="modelSuggestions" placeholder="Leave empty for backend default" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900">
                <datalist id="modelSuggestions">
                    <option value="gemini-2.5-flash">
                    <option value="gemini-2.5-pro">
                    <option value="gemini-3.5-flash">
                    <option value="openai/gpt-oss-120b">
                    <option value="llama-3.3-70b-versatile">
                    <option value="gpt-4o">
                    <option value="gpt-4o-mini">
                    <option value="deepseek-chat">
                    <option value="deepseek-reasoner">
                </datalist>
            </div>

            <div class="pt-1">
                <button type="button" onclick="testModalAiKey()" id="btnModalTestAi" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Test Authentication</span>
                </button>
            </div>

            <div id="modalAiTestResult" class="hidden p-2.5 rounded-lg text-[11px] font-mono leading-relaxed"></div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAiKeyModal()" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" id="btnSaveAiKey" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs transition-all cursor-pointer">Save Key</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Add / Edit SMTP Modal -->
<div id="smtpModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 shrink-0">
            <h3 id="modalSmtpTitle" class="text-xs font-bold uppercase tracking-wider text-slate-900">Configure SMTP Relay</h3>
            <button type="button" onclick="closeSmtpModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <form id="smtpForm" onsubmit="handleSaveSmtp(event)" class="p-5 space-y-3.5 overflow-y-auto flex-1">
            <input type="hidden" id="smtpRecordId" name="id" value="">

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Configuration Label</label>
                <input type="text" id="smtpLabel" name="label" required placeholder="e.g. Work Google Workspace, Brevo Relay" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Host</label>
                    <input type="text" id="smtpHost" name="host" required placeholder="smtp.gmail.com" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Port</label>
                    <input type="number" id="smtpPort" name="port" value="587" required class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Encryption Mode</label>
                    <select id="smtpEncryption" name="encryption" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 cursor-pointer">
                        <option value="tls">TLS / STARTTLS (587)</option>
                        <option value="ssl">SSL (465)</option>
                        <option value="none">None (25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Outbound Priority</label>
                    <input type="number" id="smtpPriority" name="priority" value="1" min="1" max="99" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Username / Account</label>
                    <input type="text" id="smtpUsername" name="username" required placeholder="user@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-semibold text-slate-700">Password / App Key</label>
                        <span id="smtpOptionalNotice" class="text-[10px] text-slate-400 hidden">(Unchanged)</span>
                    </div>
                    <div class="relative">
                        <input type="password" id="smtpPassword" name="password" placeholder="••••••••" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 pr-9 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <button type="button" onclick="togglePasswordVisibility('smtpPassword')" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">From Sender Address</label>
                    <input type="email" id="smtpFromEmail" name="from_email" required placeholder="assistant@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">From Display Name</label>
                    <input type="text" id="smtpFromName" name="from_name" value="<?= e($aiSettings['owner_name'] ?? 'AI Assistant') ?> Assistant" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="pt-1">
                <button type="button" onclick="testModalSmtpConn()" id="btnModalTestSmtp" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Test Socket Handshake</span>
                </button>
            </div>

            <div id="modalSmtpTestResult" class="hidden p-2.5 rounded-lg text-[11px] font-mono leading-relaxed"></div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeSmtpModal()" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" id="btnSaveSmtp" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white shadow-2xs transition-all cursor-pointer">Save SMTP Relay</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Send Live Test Email Modal -->
<div id="sendTestEmailModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/70">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Send Live Test Message</h3>
            <button type="button" onclick="closeSendTestEmailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        <form id="sendTestEmailForm" onsubmit="handleSendRealTestEmail(event)" class="p-5 space-y-3.5">
            <input type="hidden" id="sendTestSmtpId" value="">
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Recipient Email Address</label>
                <input type="email" id="testEmailRecipient" required placeholder="recipient@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>
            <div id="sendTestEmailResult" class="hidden p-2.5 rounded-lg text-[11px] font-mono leading-relaxed"></div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeSendTestEmailModal()" class="px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" id="btnSubmitSendTestEmail" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white shadow-2xs transition-all cursor-pointer">Send Real Email</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT CONTROLLERS ================= -->
<script>
(function() {
    const CSRF_TOKEN = '<?= e(csrf_token()) ?>';

    function apiFetch(url, options = {}) {
        options.headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        };
        return fetch(url, options);
    }

    // 1. Two-Tab Switcher
    window.switchMainTab = function(tab) {
        const btnAi = document.getElementById('mainTabBtn-ai');
        const btnSmtp = document.getElementById('mainTabBtn-smtp');
        const panelAi = document.getElementById('panel-ai');
        const panelSmtp = document.getElementById('panel-smtp');

        if (tab === 'ai') {
            btnAi.className = 'main-tab-btn active pb-2.5 text-slate-900 border-b-2 border-slate-900 flex items-center gap-2 cursor-pointer font-bold';
            btnSmtp.className = 'main-tab-btn pb-2.5 text-slate-500 hover:text-slate-800 border-b-2 border-transparent flex items-center gap-2 cursor-pointer font-medium transition-colors';
            panelAi.classList.remove('hidden');
            panelSmtp.classList.add('hidden');
        } else {
            btnSmtp.className = 'main-tab-btn active pb-2.5 text-slate-900 border-b-2 border-slate-900 flex items-center gap-2 cursor-pointer font-bold';
            btnAi.className = 'main-tab-btn pb-2.5 text-slate-500 hover:text-slate-800 border-b-2 border-transparent flex items-center gap-2 cursor-pointer font-medium transition-colors';
            panelSmtp.classList.remove('hidden');
            panelAi.classList.add('hidden');
        }
    };

    // 2. Clipboard Copy Helper
    window.copyKeyText = function(text, btn) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(function() {
            const orig = btn.innerHTML;
            btn.innerHTML = '<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            setTimeout(function() { btn.innerHTML = orig; }, 1800);
        });
    };

    // 2.5 Action Menu Dropdown Controller
    window.toggleActionMenu = function(btn, event) {
        if (event) {
            event.stopPropagation();
        }
        const scope = btn.closest('[data-dropdown-scope]');
        if (!scope) return;
        const menu = scope.querySelector('.action-dropdown');
        const caret = scope.querySelector('.dropdown-caret');
        if (!menu) return;

        const isCurrentlyOpen = !menu.classList.contains('hidden');

        // Close all open menus first
        window.closeAllActionMenus();

        if (!isCurrentlyOpen) {
            menu.classList.remove('hidden');
            if (caret) caret.classList.add('rotate-180');
        }
    };

    window.closeAllActionMenus = function() {
        document.querySelectorAll('.action-dropdown').forEach(function(m) {
            m.classList.add('hidden');
        });
        document.querySelectorAll('.dropdown-caret').forEach(function(c) {
            c.classList.remove('rotate-180');
        });
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-dropdown-scope]')) {
            window.closeAllActionMenus();
        }
    });

    // 3. Password Visibility Toggle
    window.togglePasswordVisibility = function(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.type = field.type === 'password' ? 'text' : 'password';
    };

    // 4. Default Models
    const defaultBackendModels = {
        gemini: <?= json_encode($aiSettings['gemini_model'] ?? 'gemini-3.5-flash') ?>,
        groq: <?= json_encode($aiSettings['groq_model'] ?? 'openai/gpt-oss-120b') ?>,
        custom: <?= json_encode($aiSettings['custom_model'] ?? ($aiSettings['openrouter_model'] ?? 'gpt-4o')) ?>
    };

    function onKeyProviderChange() {
        const prov = document.getElementById('keyProvider').value;
        const modelInput = document.getElementById('keyModel');
        if (modelInput) {
            modelInput.value = defaultBackendModels[prov] || '';
        }
        const endpointCont = document.getElementById('endpointContainer');
        if (endpointCont) {
            if (prov === 'custom') {
                endpointCont.classList.remove('hidden');
            } else {
                endpointCont.classList.add('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const providerSelect = document.getElementById('keyProvider');
        if (providerSelect) {
            providerSelect.addEventListener('change', onKeyProviderChange);
        }
    });

    // 5. AI Key Modal Controls
    window.openAddAiKeyModal = function(provider = 'gemini') {
        document.getElementById('keyRecordId').value = '';
        document.getElementById('keyProvider').value = provider;
        document.getElementById('keyLabel').value = '';
        document.getElementById('keyString').value = '';
        document.getElementById('keyModel').value = defaultBackendModels[provider] || '';
        document.getElementById('keyPriority').value = '1';
        const endpointInput = document.getElementById('keyEndpoint');
        if (endpointInput) {
            endpointInput.value = '<?= e($aiSettings['custom_endpoint'] ?? 'https://api.openai.com/v1') ?>';
        }
        onKeyProviderChange();
        document.getElementById('modalKeyTitle').innerText = 'Add ' + (provider === 'custom' ? 'Custom API' : provider.toUpperCase()) + ' Key';
        document.getElementById('keyOptionalNotice').classList.add('hidden');
        document.getElementById('keyString').required = true;
        document.getElementById('modalAiTestResult').classList.add('hidden');
        document.getElementById('aiKeyModal').classList.remove('hidden');
    };

    window.editAiKey = function(keyData) {
        document.getElementById('keyRecordId').value = keyData.id || '';
        const prov = keyData.provider === 'openrouter' ? 'custom' : (keyData.provider || 'gemini');
        document.getElementById('keyProvider').value = prov;
        document.getElementById('keyLabel').value = keyData.label || '';
        document.getElementById('keyString').value = '';
        document.getElementById('keyModel').value = keyData.model || defaultBackendModels[prov] || '';
        document.getElementById('keyPriority').value = keyData.priority || '1';
        const endpointInput = document.getElementById('keyEndpoint');
        if (endpointInput) {
            endpointInput.value = keyData.endpoint || '<?= e($aiSettings['custom_endpoint'] ?? 'https://api.openai.com/v1') ?>';
        }
        onKeyProviderChange();
        document.getElementById('modalKeyTitle').innerText = 'Edit ' + (prov === 'custom' ? 'Custom API' : prov.toUpperCase()) + ' Key';
        document.getElementById('keyOptionalNotice').classList.remove('hidden');
        document.getElementById('keyString').required = false;
        document.getElementById('modalAiTestResult').classList.add('hidden');
        document.getElementById('aiKeyModal').classList.remove('hidden');
    };

    window.closeAiKeyModal = function() {
        document.getElementById('aiKeyModal').classList.add('hidden');
    };

    window.handleSaveAiKey = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveAiKey');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        const payload = {
            id: document.getElementById('keyRecordId').value || null,
            provider: document.getElementById('keyProvider').value,
            label: document.getElementById('keyLabel').value.trim(),
            api_key: document.getElementById('keyString').value.trim(),
            model: document.getElementById('keyModel').value.trim(),
            endpoint: document.getElementById('keyEndpoint') ? document.getElementById('keyEndpoint').value.trim() : '',
            priority: parseInt(document.getElementById('keyPriority').value, 10) || 1
        };

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/save-ai-key.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to save key'));
                btn.disabled = false;
                btn.innerText = 'Save Key';
            }
        } catch (err) {
            alert('Network error: ' + err.message);
            btn.disabled = false;
            btn.innerText = 'Save Key';
        }
    };

    window.testModalAiKey = async function() {
        const btn = document.getElementById('btnModalTestAi');
        const resultBox = document.getElementById('modalAiTestResult');
        const apiKey = document.getElementById('keyString').value.trim();
        const provider = document.getElementById('keyProvider').value;
        const model = document.getElementById('keyModel').value.trim();
        const endpoint = document.getElementById('keyEndpoint') ? document.getElementById('keyEndpoint').value.trim() : '';
        const keyId = document.getElementById('keyRecordId').value || null;

        if (!apiKey && !keyId) {
            alert('Please enter an API key to test.');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Testing...';
        resultBox.classList.remove('hidden');
        resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
        resultBox.innerText = `Connecting to ${provider.toUpperCase()}...`;

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/test-ai-key.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: keyId, provider, api_key: apiKey, model, endpoint })
            });
            const data = await res.json();
            if (data.ok) {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerText = data.message || `✓ Success (${data.latency_ms}ms)`;
            } else {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
                resultBox.innerText = `✗ Failed: ${data.error || 'Unknown error'}`;
            }
        } catch (err) {
            resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Connection error: ${err.message}`;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Test Authentication';
        }
    };

    window.testAiKey = async function(id, provider) {
        const btn = document.getElementById('btn-test-' + id);
        const btnM = document.getElementById('btn-test-m-' + id);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Testing...';
        }
        if (btnM) {
            btnM.disabled = true;
            btnM.innerHTML = 'Testing...';
        }

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/test-ai-key.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, provider })
            });
            const data = await res.json();
            if (data.ok) {
                alert(data.message || `✓ ${provider} key is active and healthy (${data.latency_ms}ms)`);
                window.location.reload();
            } else {
                alert(`✗ Test Failed: ${data.error || 'Unable to authenticate'}`);
                window.location.reload();
            }
        } catch (err) {
            alert('Network test error: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Test';
            }
            if (btnM) {
                btnM.disabled = false;
                btnM.innerHTML = 'Test';
            }
        }
    };

    window.toggleKeyActive = async function(id, newActive) {
        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/toggle-ai-key.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, is_active: newActive })
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to toggle status'));
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    };

    window.deleteAiKey = async function(id, label) {
        if (!confirm(`Delete key "${label}"?`)) return;

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/delete-ai-key.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete key'));
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    };

    // 6. SMTP Modal Controls
    window.openAddSmtpModal = function() {
        document.getElementById('smtpRecordId').value = '';
        document.getElementById('smtpLabel').value = '';
        document.getElementById('smtpHost').value = 'smtp.gmail.com';
        document.getElementById('smtpPort').value = '587';
        document.getElementById('smtpEncryption').value = 'tls';
        document.getElementById('smtpUsername').value = '';
        document.getElementById('smtpPassword').value = '';
        document.getElementById('smtpFromEmail').value = '';
        document.getElementById('smtpFromName').value = '<?= e($aiSettings['owner_name'] ?? 'AI Assistant') ?> Assistant';
        document.getElementById('smtpPriority').value = '1';
        document.getElementById('modalSmtpTitle').innerText = 'Configure SMTP Relay';
        document.getElementById('smtpOptionalNotice').classList.add('hidden');
        document.getElementById('smtpPassword').required = true;
        document.getElementById('modalSmtpTestResult').classList.add('hidden');
        document.getElementById('smtpModal').classList.remove('hidden');
    };

    window.editSmtp = function(smtpData) {
        document.getElementById('smtpRecordId').value = smtpData.id || '';
        document.getElementById('smtpLabel').value = smtpData.label || '';
        document.getElementById('smtpHost').value = smtpData.host || '';
        document.getElementById('smtpPort').value = smtpData.port || 587;
        document.getElementById('smtpEncryption').value = smtpData.encryption || 'tls';
        document.getElementById('smtpUsername').value = smtpData.username || '';
        document.getElementById('smtpPassword').value = '';
        document.getElementById('smtpFromEmail').value = smtpData.from_email || '';
        document.getElementById('smtpFromName').value = smtpData.from_name || 'AI Assistant';
        document.getElementById('smtpPriority').value = smtpData.priority || 1;
        document.getElementById('modalSmtpTitle').innerText = 'Edit SMTP Relay Configuration';
        document.getElementById('smtpOptionalNotice').classList.remove('hidden');
        document.getElementById('smtpPassword').required = false;
        document.getElementById('modalSmtpTestResult').classList.add('hidden');
        document.getElementById('smtpModal').classList.remove('hidden');
    };

    window.closeSmtpModal = function() {
        document.getElementById('smtpModal').classList.add('hidden');
    };

    window.handleSaveSmtp = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveSmtp');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        const payload = {
            id: document.getElementById('smtpRecordId').value || null,
            label: document.getElementById('smtpLabel').value.trim(),
            host: document.getElementById('smtpHost').value.trim(),
            port: parseInt(document.getElementById('smtpPort').value, 10) || 587,
            encryption: document.getElementById('smtpEncryption').value,
            username: document.getElementById('smtpUsername').value.trim(),
            password: document.getElementById('smtpPassword').value,
            from_email: document.getElementById('smtpFromEmail').value.trim(),
            from_name: document.getElementById('smtpFromName').value.trim(),
            priority: parseInt(document.getElementById('smtpPriority').value, 10) || 1
        };

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/save-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to save SMTP'));
                btn.disabled = false;
                btn.innerText = 'Save SMTP Relay';
            }
        } catch (err) {
            alert('Network error: ' + err.message);
            btn.disabled = false;
            btn.innerText = 'Save SMTP Relay';
        }
    };

    window.testModalSmtpConn = async function() {
        const btn = document.getElementById('btnModalTestSmtp');
        const resultBox = document.getElementById('modalSmtpTestResult');
        const host = document.getElementById('smtpHost').value.trim();
        const port = parseInt(document.getElementById('smtpPort').value, 10) || 587;
        const encryption = document.getElementById('smtpEncryption').value;
        const username = document.getElementById('smtpUsername').value.trim();
        const password = document.getElementById('smtpPassword').value;
        const id = document.getElementById('smtpRecordId').value || null;

        if (!host || !username) {
            alert('Please fill Host and Username first.');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Connecting...';
        resultBox.classList.remove('hidden');
        resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
        resultBox.innerText = `Connecting to ${host}:${port}...`;

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, host, port, encryption, username, password })
            });
            const data = await res.json();
            if (data.ok) {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerText = data.message || `✓ SMTP Handshake succeeded in ${data.latency_ms}ms`;
            } else {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
                resultBox.innerText = `✗ Failed: ${data.error || 'Connection rejected'}`;
            }
        } catch (err) {
            resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Socket error: ${err.message}`;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Test Socket Handshake';
        }
    };

    window.testSmtp = async function(id) {
        const btn = document.getElementById('btn-test-smtp-' + id);
        const btnM = document.getElementById('btn-test-smtp-m-' + id);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Testing...';
        }
        if (btnM) {
            btnM.disabled = true;
            btnM.innerHTML = 'Testing...';
        }

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.ok) {
                alert(data.message || `✓ Connection verified (${data.latency_ms}ms)`);
                window.location.reload();
            } else {
                alert(`✗ Test Failed: ${data.error || 'Authentication rejected'}`);
                window.location.reload();
            }
        } catch (err) {
            alert('Network test error: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Test Conn';
            }
            if (btnM) {
                btnM.disabled = false;
                btnM.innerHTML = 'Test Conn';
            }
        }
    };

    window.promptSendTestEmail = function(id, fromEmail) {
        document.getElementById('sendTestSmtpId').value = id;
        document.getElementById('testEmailRecipient').value = fromEmail || '';
        document.getElementById('sendTestEmailResult').classList.add('hidden');
        document.getElementById('sendTestEmailModal').classList.remove('hidden');
    };

    window.closeSendTestEmailModal = function() {
        document.getElementById('sendTestEmailModal').classList.add('hidden');
    };

    window.handleSendRealTestEmail = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitSendTestEmail');
        const resultBox = document.getElementById('sendTestEmailResult');
        const id = document.getElementById('sendTestSmtpId').value;
        const recipient = document.getElementById('testEmailRecipient').value.trim();

        if (!recipient) {
            alert('Please enter a recipient email.');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Sending...';
        resultBox.classList.remove('hidden');
        resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
        resultBox.innerText = `Dispatching test email to ${recipient}...`;

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, test_recipient: recipient })
            });
            const data = await res.json();
            if (data.ok) {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerText = data.message || `✓ Test email delivered to ${recipient}`;
                setTimeout(function() { closeSendTestEmailModal(); }, 2000);
            } else {
                resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
                resultBox.innerText = `✗ Send Failed: ${data.error || 'Delivery rejected'}`;
            }
        } catch (err) {
            resultBox.className = 'p-2.5 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Error: ${err.message}`;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Send Real Email';
        }
    };

    window.toggleSmtpActive = async function(id, newActive) {
        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/toggle-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, is_active: newActive })
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to toggle status'));
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    };

    window.deleteSmtp = async function(id, label) {
        if (!confirm(`Delete SMTP server "${label}"?`)) return;

        try {
            const res = await apiFetch('<?= e(base_url('api/credentials/delete-smtp.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete SMTP server'));
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    };

    // 7. Escape key closes modals and dropdowns
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeAllActionMenus();
            closeAiKeyModal();
            closeSmtpModal();
            closeSendTestEmailModal();
        }
    });
})();
</script>
