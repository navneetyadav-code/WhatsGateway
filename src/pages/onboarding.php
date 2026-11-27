<?php

declare(strict_types=1);

$user = $user ?? require_auth();

if ((int) ($user['onboarding_completed'] ?? 0) === 1 && empty($_GET['preview'])) {
    redirect_to('index.php?page=dashboard');
}

$defaultName = trim((string) ($user['name'] ?? ''));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Workspace Setup - WhatsGateway</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'monospace'],
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.02)',
                        'subtle': '0 1px 2px 0 rgba(15, 23, 42, 0.03)',
                    }
                }
            }
        };
    </script>
    <style>
        body {
            background-color: #FBFBFC;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans text-[13px]">

    <!-- Minimal Top Header -->
    <header class="w-full bg-white border-b border-slate-200/80 px-4 sm:px-6 h-14 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2.5">
            <img src="<?= e(base_url('assets/favicon-32x32.png')) ?>" alt="WhatsGateway" class="w-6 h-6 rounded-md">
            <span class="text-sm font-bold tracking-tight text-slate-900">WhatsGateway</span>
            <span class="hidden sm:inline-block text-slate-300">/</span>
            <span class="hidden sm:inline-block text-xs font-medium text-slate-500">Initial Setup</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden md:inline-block text-xs text-slate-500 font-mono truncate max-w-[220px]"><?= e((string) ($user['email'] ?? '')) ?></span>

            <form method="post" action="<?= e(base_url('index.php?page=onboarding-save')) ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="skip" value="1">
                <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                    Skip setup
                </button>
            </form>

            <form method="post" action="<?= e(base_url('index.php?page=logout')) ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                    Sign out
                </button>
            </form>
        </div>
    </header>

    <!-- Main Centered Setup Container -->
    <main class="flex-1 flex items-start sm:items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-2xl space-y-5">

            <?php foreach (flashes() as $flash): ?>
                <div class="p-3 text-xs font-medium rounded-lg border flex items-center gap-2 <?= $flash['type'] === 'success' ? 'bg-emerald-50/80 border-emerald-200 text-emerald-800' : 'bg-rose-50/80 border-rose-200 text-rose-800' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $flash['type'] === 'success' ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endforeach; ?>

            <!-- Stepper Progress Bar -->
            <div class="flex items-center justify-between gap-2 px-1" aria-label="Setup progress">
                <button type="button" data-step-pill="1" class="step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-slate-900 bg-white shadow-subtle">
                    <span class="step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-slate-900 text-white">1</span>
                    <span class="text-xs font-semibold text-slate-900 truncate">Workspace Mode</span>
                </button>

                <div class="w-4 h-px bg-slate-200 shrink-0 hidden sm:block"></div>

                <button type="button" data-step-pill="2" class="step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-slate-200 bg-slate-50/70">
                    <span class="step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-slate-200 text-slate-600">2</span>
                    <span class="text-xs font-medium text-slate-500 truncate">Session Storage</span>
                </button>

                <div class="w-4 h-px bg-slate-200 shrink-0 hidden sm:block"></div>

                <button type="button" data-step-pill="3" class="step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-slate-200 bg-slate-50/70">
                    <span class="step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-slate-200 text-slate-600">3</span>
                    <span class="text-xs font-medium text-slate-500 truncate">AI Provider</span>
                </button>
            </div>

            <!-- Onboarding Card -->
            <form id="onboardingForm" method="post" action="<?= e(base_url('index.php?page=onboarding-save')) ?>" class="bg-white rounded-xl border border-slate-200/90 shadow-card overflow-hidden">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <!-- ========================================================= -->
                <!-- STEP 1: WORKSPACE MODE                                    -->
                <!-- ========================================================= -->
                <div class="step-panel p-5 sm:p-6 space-y-5" data-step="1">
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">How do you plan to use this workspace?</h1>
                        <p class="text-xs text-slate-500 mt-1">Select your primary workflow. You can adjust this later in workspace settings.</p>
                    </div>

                    <div class="space-y-3" id="workspaceModeGroup">
                        <!-- Option 1: Personal Assistant -->
                        <label for="mode_personal" class="mode-card relative flex items-start gap-3.5 p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white">
                            <input type="radio" id="mode_personal" name="workspace_mode" value="personal" class="mode-radio mt-0.5 h-4 w-4 text-slate-900 border-slate-300 focus:ring-slate-900 cursor-pointer shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] font-bold text-slate-900">Personal Assistant</span>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600">Private Number</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Automate replies, reminders, and personal tools on your own number. Hides customer support queues from navigation.
                                </p>
                            </div>
                        </label>

                        <!-- Option 2: Customer Support Agent -->
                        <label for="mode_support" class="mode-card relative flex items-start gap-3.5 p-4 rounded-xl border-2 transition-all cursor-pointer border-emerald-600 bg-emerald-50/20 ring-1 ring-emerald-600/20">
                            <input type="radio" id="mode_support" name="workspace_mode" value="support" checked class="mode-radio mt-0.5 h-4 w-4 text-emerald-600 border-slate-300 focus:ring-emerald-600 cursor-pointer shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] font-bold text-slate-900">Customer Support Agent</span>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-emerald-100 text-emerald-800">Default</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Handle inbound customer conversations using a knowledge base, live chat handoff, and escalation alerts.
                                </p>
                            </div>
                        </label>

                        <!-- Option 3: REST API & Webhooks Only -->
                        <label for="mode_api_only" class="mode-card relative flex items-start gap-3.5 p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white">
                            <input type="radio" id="mode_api_only" name="workspace_mode" value="api_only" class="mode-radio mt-0.5 h-4 w-4 text-slate-900 border-slate-300 focus:ring-slate-900 cursor-pointer shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] font-bold text-slate-900">REST API & Webhooks Only</span>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600">No Auto-Reply</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Use the HTTP messaging API and webhook callbacks without automated AI replies.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div id="ownerNameField" class="pt-1">
                        <label for="ownerNameInput" class="block text-xs font-semibold text-slate-700 mb-1">Display or Organization Name</label>
                        <input type="text"
                               id="ownerNameInput"
                               name="owner_name"
                               maxlength="100"
                               value="<?= e($defaultName) ?>"
                               placeholder="Your name or organization name"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-400">
                        <p class="text-[11px] text-slate-400 mt-1">Used to identify you or your organization in automated conversations.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button type="button" onclick="goToStep(2)" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-subtle transition-colors cursor-pointer">
                            Continue
                        </button>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- STEP 2: SESSION MEMORY STORAGE                            -->
                <!-- ========================================================= -->
                <div class="step-panel hidden p-5 sm:p-6 space-y-5" data-step="2">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Choose session memory storage</h2>
                        <p class="text-xs text-slate-500 mt-1">Select where WhatsApp authentication credentials should be persisted.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" id="storageDriverGroup">
                        <!-- Encrypted Database -->
                        <label for="ob_driver_db" class="ob-driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer border-emerald-600 bg-emerald-50/20 ring-1 ring-emerald-600/20">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" id="ob_driver_db" name="storage_driver" value="database_encrypted" checked class="ob-driver-radio h-4 w-4 text-emerald-600 border-slate-300 focus:ring-emerald-600 cursor-pointer">
                                    <span class="text-[13px] font-bold text-slate-900">Encrypted Database</span>
                                </div>
                                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-emerald-100 text-emerald-800 shrink-0">Recommended</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-2.5 leading-relaxed">
                                Stores encrypted session data in the database. Keeps sessions active across server restarts and updates.
                            </p>
                        </label>

                        <!-- Persistent Disk -->
                        <label for="ob_driver_disk" class="ob-driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" id="ob_driver_disk" name="storage_driver" value="persistent_disk" class="ob-driver-radio h-4 w-4 text-indigo-600 border-slate-300 focus:ring-indigo-600 cursor-pointer">
                                    <span class="text-[13px] font-bold text-slate-900">Persistent Disk</span>
                                </div>
                                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 shrink-0">Local Files</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-2.5 leading-relaxed">
                                Stores session files directly on the server's local file storage. Credentials are not saved to the database.
                            </p>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" onclick="goToStep(1)" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                            Back
                        </button>
                        <button type="button" onclick="goToStep(3)" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-subtle transition-colors cursor-pointer">
                            Continue
                        </button>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- STEP 3: OPTIONAL AI PROVIDER KEY                          -->
                <!-- ========================================================= -->
                <div class="step-panel hidden p-5 sm:p-6 space-y-5" data-step="3">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Configure an AI provider (optional)</h2>
                        <p class="text-xs text-slate-500 mt-1">Add an API key now to enable automated responses, or skip to configure it later.</p>
                    </div>

                    <div id="apiOnlyHint" class="hidden p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Ready for REST API & Webhooks</span>
                        </div>
                        You selected <strong>REST API & Webhooks Only</strong>. AI agents and auto-replies are disabled by default. Your gateway is streamlined for programmatic messaging and webhooks. You can proceed directly to generate your API keys.
                    </div>

                    <div id="aiKeyFieldsContainer" class="space-y-4">
                        <div>
                            <label for="aiProviderSelect" class="block text-xs font-semibold text-slate-700 mb-1">Provider</label>
                            <select id="aiProviderSelect" name="ai_provider" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-400">
                                <option value="gemini">Google Gemini</option>
                                <option value="groq">Groq</option>
                                <option value="openrouter">OpenRouter</option>
                            </select>
                        </div>

                        <div>
                            <label for="aiApiKeyInput" class="block text-xs font-semibold text-slate-700 mb-1">API Key <span class="font-normal text-slate-400">(optional)</span></label>
                            <div class="relative">
                                <input type="password"
                                       id="aiApiKeyInput"
                                       name="ai_api_key"
                                       autocomplete="off"
                                       placeholder="Paste API key or leave blank to skip"
                                       class="w-full px-3 py-2 pr-16 text-xs font-mono rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/15 focus:border-slate-400">
                                <button type="button" id="toggleKeyVisibility" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-0.5 text-[11px] font-medium text-slate-500 hover:text-slate-800 cursor-pointer">
                                    Show
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Keys are encrypted before storage. Messaging APIs, webhooks, and WhatsApp pairing work without an AI key.</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <button type="button" onclick="goToStep(2)" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                            Back
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="submit" id="completeBtn" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-subtle transition-colors cursor-pointer">
                                Complete Setup
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <script>
        (function() {
            var currentStep = 1;

            function syncModeCards() {
                var selectedMode = 'support';
                document.querySelectorAll('.mode-card').forEach(function(card) {
                    var radio = card.querySelector('.mode-radio');
                    if (radio && radio.checked) {
                        selectedMode = radio.value;
                        card.className = 'mode-card relative flex items-start gap-3.5 p-4 rounded-xl border-2 transition-all cursor-pointer border-emerald-600 bg-emerald-50/20 ring-1 ring-emerald-600/20';
                    } else {
                        card.className = 'mode-card relative flex items-start gap-3.5 p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
                    }
                });

                var ownerField = document.getElementById('ownerNameField');
                var apiHint = document.getElementById('apiOnlyHint');
                var aiFields = document.getElementById('aiKeyFieldsContainer');
                var completeBtn = document.getElementById('completeBtn');

                if (ownerField) {
                    ownerField.classList.toggle('hidden', selectedMode === 'api_only');
                }
                if (apiHint) {
                    apiHint.classList.toggle('hidden', selectedMode !== 'api_only');
                }
                if (aiFields) {
                    aiFields.classList.toggle('hidden', selectedMode === 'api_only');
                }
                if (completeBtn) {
                    completeBtn.textContent = (selectedMode === 'api_only') ? 'Complete Setup (API Gateway)' : 'Complete Setup';
                }
            }

            function syncDriverCards() {
                document.querySelectorAll('.ob-driver-card').forEach(function(card) {
                    var radio = card.querySelector('.ob-driver-radio');
                    if (radio && radio.checked) {
                        var isDb = radio.value === 'database_encrypted';
                        card.className = 'ob-driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer ' +
                            (isDb ? 'border-emerald-600 bg-emerald-50/20 ring-1 ring-emerald-600/20' : 'border-indigo-600 bg-indigo-50/20 ring-1 ring-indigo-600/20');
                    } else {
                        card.className = 'ob-driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
                    }
                });
            }

            window.goToStep = function(step) {
                currentStep = step;
                document.querySelectorAll('.step-panel').forEach(function(panel) {
                    panel.classList.toggle('hidden', Number(panel.getAttribute('data-step')) !== step);
                });

                document.querySelectorAll('.step-pill').forEach(function(pill) {
                    var pillStep = Number(pill.getAttribute('data-step-pill'));
                    var num = pill.querySelector('.step-num');
                    var label = pill.querySelector('span:last-child');
                    if (pillStep === step) {
                        pill.className = 'step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-slate-900 bg-white shadow-subtle';
                        if (num) num.className = 'step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-slate-900 text-white';
                        if (label) label.className = 'text-xs font-semibold text-slate-900 truncate';
                    } else if (pillStep < step) {
                        pill.className = 'step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-emerald-200 bg-emerald-50/40';
                        if (num) num.className = 'step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-emerald-600 text-white';
                        if (label) label.className = 'text-xs font-medium text-emerald-800 truncate';
                    } else {
                        pill.className = 'step-pill flex-1 flex items-center gap-2 py-2 px-3 rounded-lg border text-left transition-all cursor-pointer border-slate-200 bg-slate-50/70';
                        if (num) num.className = 'step-num w-5 h-5 rounded-full text-[11px] font-bold flex items-center justify-center shrink-0 bg-slate-200 text-slate-600';
                        if (label) label.className = 'text-xs font-medium text-slate-500 truncate';
                    }
                });
            };

            document.querySelectorAll('.step-pill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    var target = Number(pill.getAttribute('data-step-pill'));
                    if (target >= 1 && target <= 3) {
                        window.goToStep(target);
                    }
                });
            });

            document.querySelectorAll('.mode-radio').forEach(function(r) {
                r.addEventListener('change', syncModeCards);
            });
            document.querySelectorAll('.ob-driver-radio').forEach(function(r) {
                r.addEventListener('change', syncDriverCards);
            });

            var toggleBtn = document.getElementById('toggleKeyVisibility');
            var keyInput = document.getElementById('aiApiKeyInput');
            if (toggleBtn && keyInput) {
                toggleBtn.addEventListener('click', function() {
                    var isHidden = keyInput.type === 'password';
                    keyInput.type = isHidden ? 'text' : 'password';
                    toggleBtn.textContent = isHidden ? 'Hide' : 'Show';
                });
            }

            syncModeCards();
            syncDriverCards();
        })();
    </script>
</body>
</html>
