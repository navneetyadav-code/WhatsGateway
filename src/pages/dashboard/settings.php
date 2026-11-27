<?php

declare(strict_types=1);

/**
 * Settings & Security View Partial
 *
 * Unified hub for session persistence drivers, auto-sleep lifecycle,
 * account credentials, two-factor authentication, and OAuth single sign-on.
 */
?>

<div class="space-y-5 max-w-5xl">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 mb-5">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Settings & Security</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage session storage, connection sleep, login credentials, and two-factor authentication.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white text-slate-700 border border-slate-200 shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full <?= $deviceConnected ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                <?= $deviceConnected ? 'WhatsApp Connected (' . e($devicePhone ?? 'Online') . ')' : 'WhatsApp Disconnected' ?>
            </span>
            <a href="<?= e(base_url('index.php?page=audit')) ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white text-slate-700 border border-slate-200 shadow-xs hover:bg-slate-50 transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Audit Logs
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6" aria-label="Settings Tabs">
            <a href="<?= e(base_url('index.php?page=settings&tab=session')) ?>" 
               class="settings-tab-btn inline-flex items-center gap-2 py-2.5 px-1 border-b-2 text-xs sm:text-sm font-semibold transition-colors <?= $activeTab === 'session' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                <svg class="w-4 h-4 <?= $activeTab === 'session' ? 'text-slate-900' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 4h16M8 15h.01"></path>
                </svg>
                Session & Storage
                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-full <?= $storageDriver === 'database_encrypted' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' ?>">
                    <?= $storageDriver === 'database_encrypted' ? 'Database' : 'Disk' ?>
                </span>
            </a>

            <a href="<?= e(base_url('index.php?page=settings&tab=security')) ?>" 
               class="settings-tab-btn inline-flex items-center gap-2 py-2.5 px-1 border-b-2 text-xs sm:text-sm font-semibold transition-colors <?= $activeTab === 'security' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                <svg class="w-4 h-4 <?= $activeTab === 'security' ? 'text-slate-900' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Account Security
                <?php if ($isTotpEnabled): ?>
                    <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700">2FA Active</span>
                <?php else: ?>
                    <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700">2FA Off</span>
                <?php endif; ?>
            </a>
        </nav>
    </div>

    <!-- ==================================================================== -->
    <!-- TAB 1: SESSION & STORAGE ENGINE                                     -->
    <!-- ==================================================================== -->
    <?php if ($activeTab === 'session'): ?>
    <div class="space-y-5">
        <!-- Storage Driver Selection Card -->
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="px-4 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 bg-slate-50/50">
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Session Memory Storage</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Choose where WhatsApp authentication credentials are saved.</p>
                </div>
                <span id="activeDriverBadge" class="self-start sm:self-auto px-2 py-0.5 text-[10px] font-semibold rounded-full border shadow-subtle <?= $storageDriver === 'database_encrypted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200' ?>">
                    Active: <?= $storageDriver === 'database_encrypted' ? 'Encrypted Database' : 'Persistent Disk' ?>
                </span>
            </div>

            <form method="post" action="<?= e(base_url('index.php?page=settings-storage-driver')) ?>" class="p-4 sm:p-5 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5" id="storageDriverGroup">
                    <!-- Option 1: Encrypted Database -->
                    <label for="driver_database_encrypted" 
                           class="driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer <?= $storageDriver === 'database_encrypted' ? 'border-emerald-600 bg-emerald-50/20 shadow-sm ring-1 ring-emerald-600/20' : 'border-slate-200 hover:border-slate-300 bg-white' ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" 
                                       id="driver_database_encrypted" 
                                       name="storage_driver" 
                                       value="database_encrypted" 
                                       <?= $storageDriver === 'database_encrypted' ? 'checked' : '' ?> 
                                       class="driver-radio h-4 w-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-bold text-slate-900">Encrypted Database</span>
                                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Recommended</span>
                                </div>
                            </div>
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-2.5 leading-relaxed">
                            Stores encrypted session data in the database. Keeps sessions active across server restarts and updates.
                        </p>
                        <div class="mt-2.5 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Maintains active connection through restarts and updates</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <span>Encrypted at rest using your application key</span>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Persistent Disk -->
                    <label for="driver_persistent_disk" 
                           class="driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer <?= $storageDriver === 'persistent_disk' ? 'border-indigo-600 bg-indigo-50/20 shadow-sm ring-1 ring-indigo-600/20' : 'border-slate-200 hover:border-slate-300 bg-white' ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" 
                                       id="driver_persistent_disk" 
                                       name="storage_driver" 
                                       value="persistent_disk" 
                                       <?= $storageDriver === 'persistent_disk' ? 'checked' : '' ?> 
                                       class="driver-radio h-4 w-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                <div>
                                    <span class="text-[13px] font-bold text-slate-900">Persistent Disk</span>
                                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-800">Local Files</span>
                                </div>
                            </div>
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 4h16M8 15h.01"></path></svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-2.5 leading-relaxed">
                            Stores session files directly on the server's local file storage. Credentials are not saved to the database.
                        </p>
                        <div class="mt-2.5 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Recommended for dedicated servers or mounted disk volumes</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 4h16M8 15h.01"></path></svg>
                                <span>Files remain strictly on the local storage path</span>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400">
                        <?= $storageDriver === 'database_encrypted' && $storedKeyCount > 0 ? "Currently storing {$storedKeyCount} encrypted session keys in database." : 'Select a storage driver and click Save.' ?>
                    </p>
                    <button type="submit" class="self-end sm:self-auto px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all btn-tactile active:scale-[0.98]">
                        Save Storage Driver
                    </button>
                </div>
            </form>
        </section>

        <!-- Auto-Sleep Configuration Card -->
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="px-4 py-3.5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Session Auto-Sleep Settings</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Automatically suspend the connection when inactive to conserve resources.</p>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border shadow-subtle <?= !empty($session['auto_sleep_enabled']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                    <?= !empty($session['auto_sleep_enabled']) ? 'Enabled' : 'Disabled' ?>
                </span>
            </div>
            <form method="post" action="<?= e(base_url('index.php?page=session-auto-sleep')) ?>" class="p-4 sm:p-5 space-y-3.5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="return_to" value="settings">

                <div class="flex items-start gap-2.5">
                    <input type="checkbox" id="autoSleepEnabledSettings" name="auto_sleep_enabled" value="1" <?= !empty($session['auto_sleep_enabled']) ? 'checked' : '' ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer">
                    <div>
                        <label for="autoSleepEnabledSettings" class="text-xs font-semibold text-slate-900 cursor-pointer">Enable Session Auto-Sleep</label>
                        <p class="text-[11px] text-slate-500 mt-0.5">Puts the connection to sleep after a period of inactivity and wakes automatically when sending or receiving.</p>
                    </div>
                </div>

                <div class="max-w-xs">
                    <label for="autoSleepTimeoutSettings" class="block text-[11px] font-semibold text-slate-700 mb-1">Inactivity Timeout (seconds)</label>
                    <input type="number" id="autoSleepTimeoutSettings" name="auto_sleep_timeout_seconds" min="15" max="86400" value="<?= (int) ($session['auto_sleep_timeout_seconds'] ?? 60) ?>" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white shadow-subtle font-mono tabular-nums">
                    <p class="text-[10px] text-slate-400 mt-1">Default: 60s. Allowed range: 15 to 86,400s (24h).</p>
                </div>

                <div class="pt-2 flex justify-end border-t border-slate-100">
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all btn-tactile active:scale-[0.98]">
                        Save Auto-Sleep Settings
                    </button>
                </div>
            </form>
        </section>
    </div>
    <?php endif; ?>

    <!-- ==================================================================== -->
    <!-- TAB 2: ACCOUNT SECURITY                                             -->
    <!-- ==================================================================== -->
    <?php if ($activeTab === 'security'): ?>
    <div class="space-y-5">

        <?php if (!empty($newlyGeneratedBackupCodes)): ?>
        <!-- Backup Codes Banner -->
        <div class="p-4 sm:p-5 bg-amber-50/80 border border-amber-200 rounded-xl shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-bold text-amber-900 tracking-tight">Save Recovery Backup Codes</h2>
                    <p class="text-xs text-amber-800 mt-0.5">These single-use codes let you sign in if you lose access to your authenticator app. Store them safely.</p>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 my-3">
                        <?php foreach ($newlyGeneratedBackupCodes as $bcode): ?>
                            <div class="p-2 bg-white border border-amber-200 rounded-lg font-mono text-center text-xs font-bold tracking-wider text-slate-800 select-all shadow-xs">
                                <?= e($bcode) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                        <button type="button" onclick="copyBackupCodes()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy All
                        </button>
                        <button type="button" onclick="downloadBackupCodes()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download (.txt)
                        </button>
                        <span id="copyFeedback" class="text-xs font-semibold text-emerald-700 hidden">Copied to clipboard!</span>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const backupCodesList = <?= json_encode($newlyGeneratedBackupCodes) ?>;
            function copyBackupCodes() {
                const text = backupCodesList.join('\n');
                navigator.clipboard.writeText(text).then(() => {
                    const fb = document.getElementById('copyFeedback');
                    fb.classList.remove('hidden');
                    setTimeout(() => fb.classList.add('hidden'), 3000);
                });
            }
            function downloadBackupCodes() {
                const text = "Security Backup Codes\nAccount: <?= e($userRecord['email']) ?>\nDate: " + new Date().toISOString() + "\n\n" + backupCodesList.join('\n') + "\n\nEach code can only be used once.";
                const blob = new Blob([text], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'backup-codes.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
        <?php endif; ?>

        <!-- Security Panels Container -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-card divide-y divide-slate-100 overflow-hidden">

            <!-- SECTION 1: TWO-FACTOR AUTHENTICATION -->
            <div class="p-4 sm:p-5">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="max-w-xl">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Two-Factor Authentication (TOTP)</h2>
                            <?php if ($isTotpEnabled): ?>
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Enabled</span>
                            <?php else: ?>
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200">Disabled</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Protect your account with 6-digit verification codes generated by your authenticator app (Google Authenticator, Microsoft Authenticator, or 1Password).
                        </p>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <?php if ($isTotpEnabled): ?>
                            <button type="button" onclick="toggleElement('regenerateCodesDrawer')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors cursor-pointer">
                                Regenerate Backup Codes
                            </button>
                            <button type="button" onclick="toggleElement('disableTotpDrawer')" class="px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer">
                                Disable 2FA
                            </button>
                        <?php elseif (!$showSetup2FA): ?>
                            <a href="<?= e(base_url('index.php?page=settings&tab=security&setup_2fa=1')) ?>" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Enable Authenticator
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($isTotpEnabled): ?>
                <!-- Enabled Details -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-500 gap-2">
                    <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Verification code required on sign in</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700"><?= $existingBackupCodesCount ?></span> recovery backup codes remaining
                    </div>
                </div>

                <!-- Regenerate Backup Codes Form (Collapsible) -->
                <div id="regenerateCodesDrawer" class="hidden mt-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <h3 class="text-xs font-bold text-slate-900 mb-0.5">Regenerate Recovery Backup Codes</h3>
                    <p class="text-[11px] text-slate-500 mb-2.5">Enter your password or a 6-digit authenticator code to generate 8 new single-use backup codes.</p>
                    <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" class="flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="regenerate_backup_codes">
                        <?php if ($hasPassword): ?>
                            <input type="password" name="password" placeholder="Account password" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white w-48 focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <?php else: ?>
                            <input type="text" name="totp_code" placeholder="6-digit code" class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-200 bg-white w-36 focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <?php endif; ?>
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                            Generate Codes
                        </button>
                        <button type="button" onclick="toggleElement('regenerateCodesDrawer')" class="text-xs text-slate-500 hover:text-slate-800 cursor-pointer">
                            Cancel
                        </button>
                    </form>
                </div>

                <!-- Disable 2FA Form (Collapsible) -->
                <div id="disableTotpDrawer" class="hidden mt-3 p-3.5 bg-rose-50/50 border border-rose-200 rounded-xl">
                    <h3 class="text-xs font-bold text-rose-900 mb-0.5">Disable Two-Factor Authentication</h3>
                    <p class="text-[11px] text-rose-700 mb-2.5">Enter your password or a 6-digit authenticator code to turn off 2FA.</p>
                    <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" class="flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="disable_totp">
                        <?php if ($hasPassword): ?>
                            <input type="password" name="password" placeholder="Account password" class="px-2.5 py-1.5 text-xs rounded-lg border border-rose-200 bg-white w-48 focus:outline-none focus:ring-2 focus:ring-rose-300" required>
                        <?php else: ?>
                            <input type="text" name="totp_code" placeholder="6-digit code" class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-rose-200 bg-white w-36 focus:outline-none focus:ring-2 focus:ring-rose-300" required>
                        <?php endif; ?>
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors cursor-pointer">
                            Confirm & Disable
                        </button>
                        <button type="button" onclick="toggleElement('disableTotpDrawer')" class="text-xs text-slate-500 hover:text-slate-800 cursor-pointer">
                            Cancel
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($showSetup2FA): ?>
                <!-- 2FA Interactive Setup Flow -->
                <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 tracking-tight">Step 1: Scan QR Code or Enter Setup Key</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Open your authenticator app and scan the QR code below.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <!-- QR Code Canvas Container -->
                        <div class="p-2.5 bg-white border border-slate-200 rounded-xl shadow-xs shrink-0 flex items-center justify-center">
                            <div id="totpQrcode" class="w-32 h-32"></div>
                        </div>

                        <div class="space-y-2.5 flex-1 w-full">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Manual Setup Key</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <code id="secretKeyText" class="px-2.5 py-1 bg-white border border-slate-200 font-mono text-xs font-bold text-slate-800 rounded-md tracking-wider select-all">
                                        <?= e(chunk_split($pendingSecret, 4, ' ')) ?>
                                    </code>
                                    <button type="button" onclick="copySecretKey()" class="px-2 py-1 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-md shadow-xs transition-colors cursor-pointer">
                                        Copy
                                    </button>
                                    <span id="secretCopiedFb" class="text-xs font-semibold text-emerald-600 hidden">Copied!</span>
                                </div>
                            </div>

                            <!-- Step 2 Confirmation Form -->
                            <div class="pt-1.5 border-t border-slate-200/60">
                                <h3 class="text-xs font-bold text-slate-900 tracking-tight mb-1">Step 2: Enter 6-Digit Code</h3>
                                <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" class="flex flex-wrap items-center gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="confirm_totp">
                                    <input type="text" name="totp_code" 
                                           placeholder="123456" 
                                           class="px-2.5 py-1.5 text-xs font-mono tracking-widest text-center w-28 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500" 
                                           inputmode="numeric" maxlength="6" required autofocus>
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        Verify & Activate
                                    </button>
                                    <a href="<?= e(base_url('index.php?page=settings&tab=security')) ?>" class="text-xs text-slate-500 hover:text-slate-800">
                                        Cancel
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client-side QR Code script -->
                <script src="<?= e(base_url('assets/qrcode.min.js')) ?>"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const container = document.getElementById("totpQrcode");
                        if (container && typeof QRCode !== 'undefined') {
                            new QRCode(container, {
                                text: <?= json_encode($totpUri) ?>,
                                width: 128,
                                height: 128,
                                colorDark: "#0F172A",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.M
                            });
                        }
                    });

                    function copySecretKey() {
                        const raw = <?= json_encode($pendingSecret) ?>;
                        navigator.clipboard.writeText(raw).then(() => {
                            const fb = document.getElementById('secretCopiedFb');
                            fb.classList.remove('hidden');
                            setTimeout(() => fb.classList.add('hidden'), 2500);
                        });
                    }
                </script>
                <?php endif; ?>
            </div>

            <!-- SECTION 2: AUTHENTICATION PROVIDERS -->
            <div class="p-4 sm:p-5">
                <div class="max-w-xl mb-4">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-5 h-5 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">Sign-in Methods</h2>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Manage how you access your account. You can connect Google or use your email and password.
                    </p>
                </div>

                <div class="space-y-3">
                    <!-- Google Account -->
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-4 h-4" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xs font-bold text-slate-900">Google Account</h3>
                                    <?php if ($isGoogleLinked): ?>
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Connected</span>
                                    <?php else: ?>
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500 border border-slate-200">Not Connected</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    <?= $isGoogleLinked ? 'Connected with ' . e($sec['google_email'] ?: $userRecord['email']) : 'Sign in quickly with your Google account.' ?>
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            <?php if ($isGoogleLinked): ?>
                                <?php if ($hasPassword): ?>
                                    <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" onsubmit="return confirm('Disconnect Google sign-in? You will use your email and password to sign in.');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="unlink_google">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-200 hover:bg-rose-50 rounded-lg shadow-xs transition-colors cursor-pointer">
                                            Disconnect
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" disabled title="Set an account password first to prevent lockout." class="px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed">
                                        Disconnect (Set Password First)
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= e(base_url('google-auth?action=link')) ?>" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors flex items-center gap-1.5">
                                    Connect Google
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Email & Password -->
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-xs text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs font-bold text-slate-900">Email & Password</h3>
                                        <?php if ($hasPassword): ?>
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                        <?php else: ?>
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">Not Set</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Sign in with <strong class="text-slate-700"><?= e($userRecord['email']) ?></strong> and your password.
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <?php if ($hasPassword): ?>
                                    <button type="button" onclick="toggleElement('changePasswordForm')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        Change Password
                                    </button>
                                <?php else: ?>
                                    <button type="button" onclick="toggleElement('setPasswordForm')" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        Set Password
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Change Password Inline Form -->
                        <div id="changePasswordForm" class="hidden mt-3 pt-3 border-t border-slate-200">
                            <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" class="max-w-sm space-y-2.5">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="change_password">

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">Current Password</label>
                                    <input type="password" name="current_password" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">New Password (Min 8 characters)</label>
                                    <input type="password" name="new_password" minlength="8" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">Confirm New Password</label>
                                    <input type="password" name="confirm_password" minlength="8" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                                        Save Password
                                    </button>
                                    <button type="button" onclick="toggleElement('changePasswordForm')" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 cursor-pointer">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Set Initial Password Form -->
                        <div id="setPasswordForm" class="<?= !$hasPassword ? '' : 'hidden' ?> mt-3 pt-3 border-t border-slate-200">
                            <div class="p-2.5 mb-2.5 bg-blue-50/70 border border-blue-200/60 rounded-lg text-[11px] text-blue-800 leading-relaxed">
                                Create an account password to sign in using your email address or to disconnect your Google account.
                            </div>
                            <form method="post" action="<?= e(base_url('index.php?page=settings-security')) ?>" class="max-w-sm space-y-2.5">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="set_password">

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">New Password (Min 8 characters)</label>
                                    <input type="password" name="new_password" minlength="8" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="••••••••" required>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">Confirm Password</label>
                                    <input type="password" name="confirm_password" minlength="8" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="••••••••" required>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                                        Create Password
                                    </button>
                                    <?php if ($hasPassword): ?>
                                    <button type="button" onclick="toggleElement('setPasswordForm')" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 cursor-pointer">
                                        Cancel
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: ACTIVE SESSION & SECURITY PARAMETERS -->
            <div class="p-4 sm:p-5 bg-slate-50/40">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 tracking-tight">Session & Security Details</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Current sign-in session and security parameters.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            IP: <?= e(client_ip()) ?>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                            CSRF Protected
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                            Encrypted
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function toggleElement(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.toggle('hidden');
    }
}

// Interactive Radio Button Card Highlight logic
(function() {
    const radios = document.querySelectorAll('.driver-radio');
    if (!radios.length) return;

    function updateDriverCards() {
        const activeRadio = document.querySelector('.driver-radio:checked');
        const badge = document.getElementById('activeDriverBadge');
        
        document.querySelectorAll('.driver-card').forEach(function(card) {
            const radio = card.querySelector('.driver-radio');
            if (radio && radio.checked) {
                const isDb = radio.value === 'database_encrypted';
                card.className = 'driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer ' +
                    (isDb ? 'border-emerald-600 bg-emerald-50/20 shadow-sm ring-1 ring-emerald-600/20' : 'border-indigo-600 bg-indigo-50/20 shadow-sm ring-1 ring-indigo-600/20');
            } else {
                card.className = 'driver-card relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
            }
        });

        if (badge && activeRadio) {
            const isDb = activeRadio.value === 'database_encrypted';
            badge.textContent = isDb ? 'Selected: Encrypted Database' : 'Selected: Persistent Disk';
            badge.className = 'self-start sm:self-auto px-2 py-0.5 text-[10px] font-semibold rounded-full border shadow-subtle ' +
                (isDb ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200');
        }
    }

    radios.forEach(function(r) {
        r.addEventListener('change', updateDriverCards);
    });

    // Also support clicking anywhere on the driver-card to trigger selection
    document.querySelectorAll('.driver-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            const radio = card.querySelector('.driver-radio');
            if (radio && !radio.checked) {
                radio.checked = true;
                updateDriverCards();
            }
        });
    });
})();
</script>
