    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Compose Message</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Queue outbound dispatches, notifications, and rich media via the Baileys WebSocket gateway.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200/80 text-slate-700 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full <?= $deviceConnected ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                <span><?= $deviceConnected ? ($deviceSleeping ? 'Standby (Auto-Wake)' : 'Gateway Active') : 'Gateway Offline' ?></span>
            </span>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Target Audience Pool</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($contactOptions) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?= $optedInCount ?> opted-in &bull; <?= count($campaigns) ?> campaigns</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Anti-Ban Dispatch Engine</div>
                    <div class="text-xl font-bold text-emerald-600 tracking-tight mt-0.5">Humanized</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Randomized inter-packet delay active</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Payload Capabilities</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">Multi-Format Media</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Images, Audio, PDF, MP4 &le; 15MB</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Message Composer Card -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/40 rounded-t-xl">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">New Dispatch Composer</h2>
                    <p class="text-[11px] text-slate-500">Configure target channel, attach media, and format your outbound message.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white text-slate-600 border border-slate-200/80 shadow-subtle flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Pipeline Ready
            </span>
        </div>
        
        <div class="p-5 sm:p-6">
            <form method="post" action="<?= e(base_url('index.php?page=message-send')) ?>" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <!-- Target Selection -->
                    <div class="flex flex-col justify-between">
                        <div>
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Target Recipient</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <div class="relative">
                                        <select name="recipient_mode" id="recipientMode" data-message-mode-select class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle font-medium text-slate-700 cursor-pointer">
                                            <option value="contact">Saved Contact</option>
                                            <option value="campaign">Campaign Audience</option>
                                            <option value="number">Direct Number</option>
                                            <option value="group" <?= (isset($_GET['group']) ? 'selected' : '') ?>>WhatsApp Group</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Target Inputs -->
                                <div>
                                    <!-- Contact -->
                                    <div data-message-panel="contact" <?= (isset($_GET['group']) ? 'hidden' : '') ?>>
                                        <div class="relative">
                                            <select name="contact_id" class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle text-slate-700 font-medium cursor-pointer">
                                                <option value="">-- Choose recipient --</option>
                                                <?php foreach ($contactOptions as $contact): ?>
                                                    <option value="<?= (int) $contact['id'] ?>"><?= e($contact['name']) ?> &middot; <?= e($contact['phone']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campaign -->
                                    <div data-message-panel="campaign" hidden>
                                        <div class="relative">
                                            <select name="campaign_id" class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle text-slate-700 font-medium cursor-pointer">
                                                <option value="">-- Choose campaign --</option>
                                                <?php foreach ($campaigns as $campaign): ?>
                                                    <option value="<?= (int) $campaign['id'] ?>">#<?= (int) $campaign['id'] ?> - <?= e($campaign['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Number -->
                                    <!-- WhatsApp Group -->
                                    <div data-message-panel="group" <?= (!isset($_GET['group']) ? 'hidden' : '') ?>>
                                        <div class="relative">
                                            <select name="group_id" id="composerGroupSelect" class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle text-slate-700 font-medium cursor-pointer">
                                                <option value="">-- Choose WhatsApp Group --</option>
                                                <?php foreach ($groups as $grp): 
                                                    $gJid = (string) ($grp['group_jid'] ?? $grp['id']);
                                                    $gName = (string) ($grp['subject'] ?? 'Unnamed Group');
                                                    $isSelected = isset($_GET['group']) && $_GET['group'] === $gJid;
                                                ?>
                                                    <option value="<?= e($gJid) ?>" <?= $isSelected ? 'selected' : '' ?>><?= e($gName) ?> &middot; <?= e($gJid) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div data-message-panel="number" hidden>
                                        <input name="manual_number" type="text" placeholder="+1234567890 (E.164)" class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white transition-all shadow-subtle font-mono tabular-nums text-slate-800">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Route instantly to contacts, dynamic campaign lists, or manual phone numbers.</p>
                    </div>

                    <!-- Attachment Upload -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[12px] font-semibold text-slate-700">Media Attachments <span class="text-slate-400 font-normal">(Optional, Multiple supported)</span></label>
                            <span class="text-[11px] text-slate-400 font-mono">Max 15MB each</span>
                        </div>
                        <div class="relative group border border-dashed border-slate-300 hover:border-emerald-500 rounded-lg px-3 py-2 bg-slate-50/50 hover:bg-emerald-50/20 transition-all cursor-pointer flex items-center gap-3">
                            <input id="messageAttachment" name="attachment[]" type="file" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.3gp,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.rtf,.zip,image/*,video/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation">
                            <div class="w-7 h-7 rounded-md bg-white border border-slate-200 group-hover:border-emerald-300 group-hover:text-emerald-600 shadow-subtle flex items-center justify-center text-slate-400 shrink-0 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1 flex items-center justify-between gap-2">
                                <span class="text-[12px] font-medium text-slate-700 group-hover:text-emerald-700 transition-colors truncate" id="attachmentStatus">No file selected</span>
                                <span class="shrink-0 text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-500 group-hover:border-emerald-200 group-hover:text-emerald-600 shadow-subtle">Browse</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Supported: Images, MP4, PDF, DOCX, PPT, PPTX, TXT. (Select multiple files if needed)</p>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[12px] font-semibold text-slate-700">Message Content <span class="text-slate-400 font-normal">(Optional if files attached)</span></label>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono whitespace-nowrap">
                            <span>WYSIWYG</span>
                            <span class="text-slate-300" aria-hidden="true">|</span>
                            <span>Live WA Preview</span>
                        </div>
                    </div>
                    <textarea name="message" data-presets='["{name}", "{phone}"]' rows="5" class="wa-editor block w-full px-3.5 py-3 text-[15px] sm:text-[16px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white transition-all shadow-subtle placeholder:text-slate-300 font-sans leading-relaxed" placeholder="Type your WhatsApp message here..."></textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" name="humanize" value="1" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 transition-colors cursor-pointer">
                        <span class="text-[12px] font-semibold text-slate-700 group-hover:text-slate-900 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Humanized Delivery Mode (Prevents spam detection)
                        </span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="reset" class="px-3.5 py-2 text-[12px] font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 rounded-lg transition-colors btn-tactile active:scale-[0.98]">Clear form</button>
                        <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Queue Message</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <!-- WhatsApp Groups Section Below Composer -->
    <section class="mt-8 bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Your WhatsApp Groups</h2>
                        <span id="msgGroupsCountBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><?= count($groups) ?> Groups</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Discover your group IDs (<code class="text-emerald-700 font-mono font-medium">@g.us</code>) for non-technical team members and developers.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative w-48 sm:w-56">
                    <input type="text" id="msgGroupSearchInput" oninput="filterMsgGroups(this.value)" placeholder="Search groups..." class="block w-full pl-7 pr-2.5 py-1 text-[11px] border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white shadow-subtle text-slate-800">
                    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <button type="button" id="msgRefreshGroupsBtn" onclick="refreshMsgGroupsList(true)" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-subtle transition-colors flex items-center gap-1.5 btn-tactile">
                    <svg id="msgRefreshSpinner" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Sync</span>
                </button>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <div class="mb-4 p-3 bg-slate-50 border border-slate-200/80 rounded-lg flex items-center justify-between gap-3 text-[11px] text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>To message a group, click <strong>Use in Composer</strong> or copy its <strong>Group ID</strong> into the "to" field of the API.</span>
                </div>
                <a href="<?= e(base_url('index.php?page=groups')) ?>" class="text-emerald-700 font-semibold hover:underline shrink-0">Full Groups Page &rarr;</a>
            </div>

            <div id="msgGroupsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <?php if (empty($groups)): ?>
                    <div class="col-span-full py-8 text-center text-slate-400 text-[12px]">
                        No WhatsApp groups found in cache. Ensure device is connected and click <strong>Sync</strong> above.
                    </div>
                <?php else: ?>
                    <?php foreach ($groups as $grp): 
                        $jid = (string) ($grp['group_jid'] ?? $grp['id']);
                        $subject = (string) ($grp['subject'] ?? 'Unnamed Group');
                        $members = (int) ($grp['participants_count'] ?? 0);
                        $isAdmin = !empty($grp['is_admin']);
                        $initial = mb_strtoupper(mb_substr($subject, 0, 1, 'UTF-8'), 'UTF-8');
                    ?>
                        <div class="msg-group-card bg-slate-50/50 hover:bg-white border border-slate-200/80 hover:border-emerald-300 rounded-xl p-3.5 transition-all shadow-subtle flex flex-col justify-between" data-name="<?= e(strtolower($subject)) ?>" data-jid="<?= e(strtolower($jid)) ?>">
                            <div>
                                <div class="flex items-center gap-2.5 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold text-[12px] flex items-center justify-center shrink-0">
                                        <?= e($initial ?: 'G') ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[12px] font-bold text-slate-900 truncate" title="<?= e($subject) ?>"><?= e($subject) ?></div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-400">
                                            <span><?= $members > 0 ? e((string)$members) . ' members' : 'Group' ?></span>
                                            <?php if ($isAdmin): ?>
                                                &bull; <span class="text-amber-600 font-semibold">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="flex items-center justify-between gap-1 bg-white border border-slate-200 rounded-md px-2 py-1">
                                        <span class="font-mono text-[10px] text-slate-700 truncate select-all" title="<?= e($jid) ?>"><?= e($jid) ?></span>
                                        <button type="button" onclick="copyText('<?= e($jid) ?>', this)" class="shrink-0 text-slate-400 hover:text-slate-800 p-0.5 rounded" title="Copy ID">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                                <button type="button" onclick="selectGroupInComposer('<?= e(addslashes($jid)) ?>')" class="flex-1 text-center py-1 text-[10px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 rounded-md transition-colors shadow-subtle btn-tactile">
                                    Use in Composer
                                </button>
                                <button type="button" onclick="openMsgQuickCode('<?= e(addslashes($jid)) ?>', '<?= e(addslashes($subject)) ?>')" class="flex-1 text-center py-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition-colors btn-tactile flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    <span>Quick Code</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Quick Code Modal for Message Page -->
    <div id="msgQuickCodeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" onclick="if(event.target===this) closeMsgQuickCode()">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-bold text-slate-900">API Quick Code</h2>
                        <p class="text-[11px] text-slate-500">Group: <span id="msgModalSubject" class="font-semibold text-slate-800"></span> (<span id="msgModalJid" class="font-mono text-emerald-700"></span>)</p>
                    </div>
                </div>
                <button type="button" onclick="closeMsgQuickCode()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="px-6 pt-3 border-b border-slate-100 flex items-center gap-2 bg-white">
                <button type="button" onclick="switchMsgCodeTab('curl')" id="msgTab_curl" class="msg-code-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-emerald-600 text-emerald-700">cURL</button>
                <button type="button" onclick="switchMsgCodeTab('php')" id="msgTab_php" class="msg-code-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">PHP</button>
                <button type="button" onclick="switchMsgCodeTab('js')" id="msgTab_js" class="msg-code-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">JavaScript</button>
                <button type="button" onclick="switchMsgCodeTab('python')" id="msgTab_python" class="msg-code-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">Python</button>
            </div>

            <div class="p-6 bg-slate-950 text-slate-100 relative font-mono text-[12px]">
                <button type="button" onclick="copySnippet(this, 'msgCodeSnippet')" class="absolute top-4 right-4 px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[11px] font-sans font-semibold flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    <span>Copy Code</span>
                </button>
                <pre class="overflow-x-auto p-2 leading-relaxed text-[12px] font-mono text-emerald-400" id="msgCodeSnippet"></pre>
            </div>

            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Endpoint: <code class="font-mono text-slate-800"><?= e(base_url('api/send.php')) ?></code></span>
                <button type="button" onclick="closeMsgQuickCode()" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:text-slate-900">Close</button>
            </div>
        </div>
    </div>

    <script>
    let activeMsgGroupJid = '';
    let activeMsgGroupSubject = '';
    let activeMsgTab = 'curl';

    function selectGroupInComposer(jid) {
        const modeSelect = document.getElementById('recipientMode');
        if (modeSelect) {
            modeSelect.value = 'group';
            modeSelect.dispatchEvent(new Event('change'));
        }
        const groupSelect = document.getElementById('composerGroupSelect');
        if (groupSelect) {
            groupSelect.value = jid;
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function openMsgQuickCode(jid, subject) {
        activeMsgGroupJid = jid;
        activeMsgGroupSubject = subject;
        document.getElementById('msgModalSubject').textContent = subject;
        document.getElementById('msgModalJid').textContent = jid;
        switchMsgCodeTab(activeMsgTab);

        const modal = document.getElementById('msgQuickCodeModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
    }

    function closeMsgQuickCode() {
        const modal = document.getElementById('msgQuickCodeModal');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.querySelector('.transform').classList.remove('scale-100');
        modal.querySelector('.transform').classList.add('scale-95');
    }

    function switchMsgCodeTab(lang) {
        activeMsgTab = lang;
        document.querySelectorAll('.msg-code-tab').forEach(b => {
            b.classList.remove('border-emerald-600', 'text-emerald-700');
            b.classList.add('border-transparent', 'text-slate-500');
        });
        const activeBtn = document.getElementById('msgTab_' + lang);
        if (activeBtn) {
            activeBtn.classList.add('border-emerald-600', 'text-emerald-700');
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
        }

        const pre = document.getElementById('msgCodeSnippet');
        const url = '<?= e(base_url('api/send.php')) ?>';
        const apiKey = '<?= e($primaryApiKey) ?>';
        const jid = activeMsgGroupJid;

        if (lang === 'curl') {
            pre.textContent = `curl -X POST "${url}" \\
  -H "Authorization: Bearer ${apiKey}" \\
  -H "Content-Type: application/json" \\
  -d '{
    "to": "${jid}",
    "message": "Hello WhatsApp Group! 🚀"
  }'`;
        } else if (lang === 'php') {
            pre.textContent = `<?='<'.'?php'?>
$apiKey = '${apiKey}';
$payload = [
    'to' => '${jid}',
    'message' => 'Hello WhatsApp Group! 🚀'
];

$ch = curl_init('${url}');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
echo $response;`;
        } else if (lang === 'js') {
            pre.textContent = `const response = await fetch('${url}', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ${apiKey}',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    to: '${jid}',
    message: 'Hello WhatsApp Group! 🚀'
  })
});
const data = await response.json();
console.log(data);`;
        } else if (lang === 'python') {
            pre.textContent = `import requests

url = "${url}"
headers = {
    "Authorization": "Bearer ${apiKey}",
    "Content-Type": "application/json"
}
payload = {
    "to": "${jid}",
    "message": "Hello WhatsApp Group! 🚀"
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())`;
        }
    }

    function copySnippet(btn, elementId) {
        const code = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(code).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<span>Copied!</span>';
            btn.classList.add('bg-emerald-600', 'text-white');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.remove('bg-emerald-600', 'text-white');
            }, 1500);
        });
    }

    function filterMsgGroups(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.msg-group-card');
        let count = 0;
        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const jid = card.getAttribute('data-jid') || '';
            if (name.includes(q) || jid.includes(q)) {
                card.style.display = '';
                count++;
            } else {
                card.style.display = 'none';
            }
        });
        const badge = document.getElementById('msgGroupsCountBadge');
        if (badge) badge.textContent = count + ' Groups';
    }

    function escapeMsgHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function copyRawText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<span class="text-emerald-600 font-bold text-[9px]">✓</span>';
            setTimeout(() => { btn.innerHTML = orig; }, 1200);
        });
    }

    function renderMsgGroupsDOM(groups) {
        const grid = document.getElementById('msgGroupsGrid');
        const badge = document.getElementById('msgGroupsCountBadge');
        if (badge) badge.textContent = groups.length + ' Groups';

        const composerSelect = document.getElementById('composerGroupSelect');
        if (composerSelect) {
            const currentVal = composerSelect.value;
            let optionsHtml = '<option value="">-- Choose WhatsApp Group --</option>';
            groups.forEach(g => {
                const jid = g.id || g.group_jid || '';
                const subject = g.subject || 'Unnamed Group';
                const selected = (jid === currentVal) ? 'selected' : '';
                optionsHtml += `<option value="${escapeMsgHtml(jid)}" ${selected}>${escapeMsgHtml(subject)} &middot; ${escapeMsgHtml(jid)}</option>`;
            });
            composerSelect.innerHTML = optionsHtml;
        }

        if (!grid) return;

        if (!groups || groups.length === 0) {
            grid.innerHTML = '<div class="col-span-full py-8 text-center text-slate-400 text-[12px]">No WhatsApp groups found in cache. Ensure device is connected and click <strong>Sync</strong> above.</div>';
            return;
        }

        grid.innerHTML = groups.map(grp => {
            const jid = grp.id || grp.group_jid || '';
            const subject = grp.subject || 'Unnamed Group';
            const members = Number(grp.participants_count) || 0;
            const isAdmin = Boolean(grp.is_admin);
            const initial = subject.charAt(0).toUpperCase() || 'G';

            const safeJid = escapeMsgHtml(jid);
            const safeSubject = escapeMsgHtml(subject);

            return `
                <div class="msg-group-card bg-slate-50/50 hover:bg-white border border-slate-200/80 hover:border-emerald-300 rounded-xl p-3.5 transition-all shadow-subtle flex flex-col justify-between" data-name="${escapeMsgHtml(subject.toLowerCase())}" data-jid="${escapeMsgHtml(jid.toLowerCase())}">
                    <div>
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold text-[12px] flex items-center justify-center shrink-0">
                                ${escapeMsgHtml(initial)}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[12px] font-bold text-slate-900 truncate" title="${safeSubject}">${safeSubject}</div>
                                <div class="flex items-center gap-1 text-[10px] text-slate-400">
                                    <span>${members > 0 ? members + ' members' : 'Group'}</span>
                                    ${isAdmin ? '&bull; <span class="text-amber-600 font-semibold">Admin</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="flex items-center justify-between gap-1 bg-white border border-slate-200 rounded-md px-2 py-1">
                                <span class="font-mono text-[10px] text-slate-700 truncate select-all" title="${safeJid}">${safeJid}</span>
                                <button type="button" onclick="copyRawText('${safeJid}', this)" class="shrink-0 text-slate-400 hover:text-slate-800 p-0.5 rounded" title="Copy ID">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                        <button type="button" onclick="selectGroupInComposer('${safeJid}')" class="flex-1 text-center py-1 text-[10px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 rounded-md transition-colors shadow-subtle btn-tactile">
                            Use in Composer
                        </button>
                        <button type="button" onclick="openMsgQuickCode('${safeJid}', '${safeSubject.replace(/'/g, "\\'")}')" class="flex-1 text-center py-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition-colors btn-tactile flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            <span>Quick Code</span>
                        </button>
                    </div>
                </div>`;
        }).join('');
    }

    async function refreshMsgGroupsList(force = false) {
        const spinner = document.getElementById('msgRefreshSpinner');
        const btn = document.getElementById('msgRefreshGroupsBtn');
        if (spinner) spinner.classList.add('animate-spin');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch('<?= e(base_url('api/groups.php')) ?>' + (force ? '?refresh=1' : ''));
            const data = await res.json();
            if (data.ok && Array.isArray(data.groups)) {
                renderMsgGroupsDOM(data.groups);
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="text-emerald-600 font-bold">✓ Synced</span>';
                setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
            } else {
                alert(data.error || 'Failed to sync groups.');
            }
        } catch (e) {
            alert('Sync error: ' + e.message);
        } finally {
            if (spinner) spinner.classList.remove('animate-spin');
            if (btn) btn.disabled = false;
        }
    }
    </script>

    <!-- Footer Spacer -->
    <div class="h-8"></div>
