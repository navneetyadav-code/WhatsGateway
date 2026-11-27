<?php
declare(strict_types=1);

/**
 * Agent Memory & Response Guidelines Dashboard
 * Plain English, human-friendly interface for managing:
 * 1. Saved Notes & Facts (what the agent remembers across conversations)
 * 2. Example Questions & Answers (how the agent should respond to specific inquiries)
 */
?>

<div class="space-y-5 mb-8 max-w-7xl mx-auto">

    <!-- ====================================================================
         1. TOP BAR: TITLE, STATUS & ACTIONS
    ===================================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/90">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Agent Memory &amp; Knowledge</h1>
                <?php if ($deviceConnected): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Connected</span>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span>Offline</span>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Manage business facts, customer notes, and approved example answers for your WhatsApp assistant.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openMemoryModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Note</span>
            </button>

            <button type="button" onclick="openTrainingModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span>Add Example Q&amp;A</span>
            </button>
        </div>
    </div>

    <!-- ====================================================================
         2. OVERVIEW METRICS: CLEAR & DIRECT
    ===================================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        
        <!-- Metric 1: Saved Notes -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Saved Notes</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span id="kpiTotalMemories" class="text-2xl font-black text-slate-900 tracking-tight tabular-nums"><?= $totalMemories ?></span>
                <span class="text-xs text-slate-400 font-medium">active notes</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span><?= $bossMemoriesCount ?> Business/General</span>
                <span>•</span>
                <span><?= $customerMemoriesCount ?> Customer-specific</span>
            </div>
        </div>

        <!-- Metric 2: Example Answers -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Example Q&amp;As</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span id="kpiTotalTraining" class="text-2xl font-black text-slate-900 tracking-tight tabular-nums"><?= $totalTrainingCount ?></span>
                <span class="text-xs text-slate-400 font-medium">response rules</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500 truncate">
                <span>Approved replies for common questions</span>
            </div>
        </div>

        <!-- Metric 3: In-Chat Commands -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Chat Commands</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-lg font-bold text-slate-900">Auto-Remember</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Say:</span>
                <code class="px-1 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[10px]">"Remember that..."</code>
            </div>
        </div>

        <!-- Metric 4: Assistant Mode -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Assistant Scope</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 text-sky-700 border border-sky-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-lg font-bold text-slate-900"><?= $agentMode === 'personal' ? 'Personal Assistant' : 'Customer Support' ?></span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500 truncate">
                <span>Active for WhatsApp</span>
            </div>
        </div>

    </div>

    <!-- ====================================================================
         3. DUAL-TAB NAVIGATION & CONTENT
    ===================================================================== -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-4">
        
        <!-- Tab Buttons & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/90 rounded-lg border border-slate-200/80">
                <button type="button" id="tabBtnMemory" onclick="switchPageTab('memory')" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Saved Notes &amp; Facts</span>
                    <span id="badgeMemTab" class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-slate-200 text-slate-700"><?= $totalMemories ?></span>
                </button>

                <button type="button" id="tabBtnTraining" onclick="switchPageTab('training')" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Example Q&amp;As</span>
                    <span id="badgeTrainTab" class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-slate-200 text-slate-700"><?= $totalTrainingCount ?></span>
                </button>
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
                <span class="text-[11px] text-slate-400">Forget command:</span>
                <code class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">"Forget my..."</code>
            </div>
        </div>

        <!-- ====================================================================
             TAB 1: SAVED NOTES & FACTS
        ===================================================================== -->
        <div id="panelMemory" class="space-y-4">
            <!-- Filter Bar & Search Input -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <button type="button" onclick="filterMemories('all')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white cursor-pointer shadow-2xs" data-filter="all">All Notes (<?= $totalMemories ?>)</button>
                    <button type="button" onclick="filterMemories('owner')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="owner">General (<?= $bossMemoriesCount ?>)</button>
                    <button type="button" onclick="filterMemories('customers')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="customers">Customer-Specific (<?= $customerMemoriesCount ?>)</button>
                    <button type="button" onclick="filterMemories('expiring')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="expiring">Temporary (<?= $temporaryMemoriesCount ?>)</button>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" id="memorySearchInput" oninput="searchMemories(this.value)" placeholder="Search notes..." class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Notes Grid -->
            <div id="memoryListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <?php if (empty($userMemories)): ?>
                    <div id="memoryEmptyState" class="col-span-full py-12 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="font-bold text-slate-700 text-sm">No saved notes yet</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Add business information or let the assistant learn automatically during WhatsApp conversations.</p>
                        <button type="button" onclick="openMemoryModal()" class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                            + Add Your First Note
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($userMemories as $m): ?>
                        <?php
                            $isCustomer = !empty($m['contact_phone']);
                            $daysLeft = !empty($m['expires_at']) ? max(0, (int) ceil((strtotime($m['expires_at']) - time()) / 86400)) : null;
                        ?>
                        <div class="memory-card bg-white rounded-xl border border-slate-200 hover:border-slate-300 p-3.5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between gap-3 group relative"
                             data-id="<?= (int) $m['id'] ?>"
                             data-key="<?= e($m['memory_key']) ?>"
                             data-val="<?= e($m['memory_value']) ?>"
                             data-phone="<?= e($m['contact_phone'] ?? '') ?>"
                             data-type="<?= e($m['memory_type'] ?? 'fact') ?>"
                             data-durable="<?= !empty($m['is_durable']) ? '1' : '0' ?>"
                             data-scope="<?= $isCustomer ? 'customers' : 'owner' ?>"
                             data-expiring="<?= !empty($m['expires_at']) ? '1' : '0' ?>">
                            
                            <div class="space-y-2">
                                <!-- Top Row: Topic Name & Scope Badges -->
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200/80 truncate">
                                        <?= e($m['memory_key']) ?>
                                    </span>
                                    
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <?php if ($isCustomer): ?>
                                            <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200" title="Customer Phone Number">
                                                <?= e($m['contact_phone']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                General
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($m['is_durable'])): ?>
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Permanent
                                            </span>
                                        <?php elseif ($daysLeft !== null): ?>
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
                                                <?= $daysLeft ?>d left
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Note Content -->
                                <div class="text-xs text-slate-700 font-normal leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100 break-words line-clamp-4">
                                    <?= e($m['memory_value']) ?>
                                </div>
                            </div>

                            <!-- Card Bottom: Usage Count & Action Buttons -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-[11px] text-slate-400">
                                <span>
                                    Used <strong class="text-slate-700 font-semibold"><?= (int) ($m['access_count'] ?? 0) ?></strong> times
                                </span>

                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="editMemoryRow(this)" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Edit
                                    </button>
                                    <button type="button" onclick="deleteMemoryRow(<?= (int) $m['id'] ?>, '<?= e($m['memory_key']) ?>')" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Delete
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====================================================================
             TAB 2: EXAMPLE Q&As (RESPONSE GUIDANCE)
        ===================================================================== -->
        <div id="panelTraining" class="hidden space-y-4">
            <!-- Plain English Explanation Banner -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3 text-xs">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs">Teach your assistant approved responses</h4>
                    <p class="text-slate-500 mt-0.5">Provide sample questions and ideal answers so your assistant knows your tone and business rules (e.g. discount policy, return instructions, booking procedures).</p>
                </div>
            </div>

            <div id="trainingListContainer" class="space-y-3">
                <?php if (empty($trainingExamples)): ?>
                    <div id="trainingEmptyState" class="py-12 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="font-bold text-slate-700 text-sm">No example Q&amp;As added yet</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Add your first example question and answer to guide how the assistant handles common requests.</p>
                        <button type="button" onclick="openTrainingModal()" class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                            + Add First Example Q&amp;A
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($trainingExamples as $t): ?>
                        <div class="training-card bg-white rounded-xl border border-slate-200 hover:border-slate-300 p-4 shadow-2xs hover:shadow-xs transition-all space-y-3 group"
                             data-id="<?= (int) $t['id'] ?>"
                             data-query="<?= e($t['user_query']) ?>"
                             data-resp="<?= e($t['ideal_response']) ?>"
                             data-const="<?= e($t['negative_constraint'] ?? '') ?>"
                             data-cat="<?= e($t['category'] ?? 'general') ?>">
                            
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                    <?= e($t['category'] ?? 'General') ?>
                                </span>
                                
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="editTrainingRow(this)" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Edit
                                    </button>
                                    <button type="button" onclick="deleteTrainingRow(<?= (int) $t['id'] ?>)" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/80">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">When Customer Asks:</span>
                                    <p class="font-medium text-slate-900 leading-relaxed"><?= e($t['user_query']) ?></p>
                                </div>

                                <div class="bg-emerald-50/70 p-3 rounded-lg border border-emerald-200/80">
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block mb-1">Approved Response:</span>
                                    <p class="text-emerald-950 font-normal leading-relaxed"><?= e($t['ideal_response']) ?></p>
                                </div>
                            </div>

                            <?php if (!empty($t['negative_constraint'])): ?>
                                <div class="bg-amber-50/80 p-2.5 rounded-lg border border-amber-200/80 flex items-center gap-2 text-xs text-amber-900">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.2 rounded bg-amber-600 text-white shrink-0">Rule / Avoid</span>
                                    <span><?= e($t['negative_constraint']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- ====================================================================
     MODAL 1: ADD / EDIT NOTE
===================================================================== -->
<div id="memoryModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 id="memoryModalTitle" class="text-sm font-bold text-slate-900">Add Note</h3>
            <button type="button" onclick="closeMemoryModal()" class="text-slate-400 hover:text-slate-800 text-sm font-bold cursor-pointer">✕</button>
        </div>

        <form id="memoryForm" onsubmit="submitMemoryForm(event)" class="space-y-3">
            <input type="hidden" id="memInputId" name="id" value="">

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Note Topic / Name</label>
                <input type="text" id="memInputKey" name="key" required placeholder="e.g. office_hours, refund_policy, wifi_password" class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Short name to identify this note (letters, numbers, underscore).</span>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Note Details</label>
                <textarea id="memInputVal" name="value" required rows="3" placeholder="e.g. Our business hours are Monday to Saturday, 9:30 AM to 6:30 PM IST. Closed on Sundays." class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Applies To</label>
                    <select id="memInputScope" onchange="toggleCustomerPhoneField(this.value)" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 cursor-pointer">
                        <option value="owner">General (All Chats)</option>
                        <option value="customer">Specific Customer</option>
                    </select>
                </div>

                <div id="memPhoneWrap" class="hidden">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Phone</label>
                    <input type="text" id="memInputPhone" name="contact_phone" placeholder="919876543210" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-xs font-semibold text-slate-800">Keep permanently</span>
                    <input type="checkbox" id="memInputDurable" name="is_durable" value="1" checked onchange="document.getElementById('memTtlWrap').classList.toggle('hidden', this.checked)" class="w-4 h-4 rounded text-slate-900 focus:ring-slate-800 cursor-pointer">
                </label>
                <div id="memTtlWrap" class="hidden pt-1 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Auto-expire in days:</span>
                    <input type="number" id="memInputTtl" name="ttl_days" value="14" min="1" max="90" class="w-16 px-2 py-1 text-xs border border-slate-200 rounded bg-white text-center font-bold">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeMemoryModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg cursor-pointer">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs cursor-pointer">Save Note</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL 2: ADD / EDIT EXAMPLE Q&A
===================================================================== -->
<div id="trainingModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 id="trainingModalTitle" class="text-sm font-bold text-slate-900">Add Example Q&amp;A</h3>
            <button type="button" onclick="closeTrainingModal()" class="text-slate-400 hover:text-slate-800 text-sm font-bold cursor-pointer">✕</button>
        </div>

        <form id="trainingForm" onsubmit="submitTrainingForm(event)" class="space-y-3">
            <input type="hidden" id="trainInputId" name="id" value="">

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">When Customer Asks:</label>
                <input type="text" id="trainInputQuery" name="user_query" required placeholder="e.g. Can you give me a discount on bulk orders?" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Approved Response:</label>
                <textarea id="trainInputResp" name="ideal_response" required rows="3" placeholder="e.g. We offer a 10% discount on orders of 5 units or more. Contact sales@company.com for larger orders." class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Rule or Things to Avoid (Optional):</label>
                <input type="text" id="trainInputConst" name="negative_constraint" placeholder="e.g. Never offer discounts above 10% without manager approval" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeTrainingModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg cursor-pointer">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs cursor-pointer">Save Q&amp;A</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchPageTab(tab, updateHistory = true) {
        if (tab !== 'memory' && tab !== 'training') {
            tab = 'memory';
        }

        document.getElementById('panelMemory').classList.toggle('hidden', tab !== 'memory');
        document.getElementById('panelTraining').classList.toggle('hidden', tab !== 'training');

        const btnMem = document.getElementById('tabBtnMemory');
        const btnTrain = document.getElementById('tabBtnTraining');

        btnMem.className = (tab === 'memory')
            ? 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer'
            : 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer';

        btnTrain.className = (tab === 'training')
            ? 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer'
            : 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer';

        try {
            localStorage.setItem('memory_active_tab', tab);
            if (updateHistory) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState(null, '', url);
            }
        } catch (_) {}
    }

    // Auto-restore tab on load
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const hashTab = window.location.hash ? window.location.hash.replace('#', '').replace('tab=', '') : null;
            const tabParam = urlParams.get('tab') || hashTab || localStorage.getItem('memory_active_tab');
            if (tabParam && ['memory', 'training'].includes(tabParam)) {
                switchPageTab(tabParam, false);
            }
        } catch (_) {}
    });

    function filterMemories(filterType) {
        document.querySelectorAll('.mem-filter-btn').forEach(btn => {
            if (btn.dataset.filter === filterType) {
                btn.className = 'mem-filter-btn px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white cursor-pointer shadow-2xs';
            } else {
                btn.className = 'mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer';
            }
        });

        document.querySelectorAll('.memory-card').forEach(card => {
            if (filterType === 'all') {
                card.classList.remove('hidden');
            } else if (filterType === 'owner') {
                card.classList.toggle('hidden', card.dataset.scope !== 'owner');
            } else if (filterType === 'customers') {
                card.classList.toggle('hidden', card.dataset.scope !== 'customers');
            } else if (filterType === 'expiring') {
                card.classList.toggle('hidden', card.dataset.expiring !== '1');
            }
        });
    }

    function searchMemories(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.memory-card').forEach(card => {
            const key = (card.dataset.key || '').toLowerCase();
            const val = (card.dataset.val || '').toLowerCase();
            const phone = (card.dataset.phone || '').toLowerCase();
            const match = (!q || key.includes(q) || val.includes(q) || phone.includes(q));
            card.classList.toggle('hidden', !match);
        });
    }

    function openMemoryModal() {
        document.getElementById('memoryModalTitle').textContent = 'Add Note';
        document.getElementById('memInputId').value = '';
        document.getElementById('memInputKey').value = '';
        document.getElementById('memInputVal').value = '';
        document.getElementById('memInputPhone').value = '';
        document.getElementById('memInputScope').value = 'owner';
        document.getElementById('memPhoneWrap').classList.add('hidden');
        document.getElementById('memInputDurable').checked = true;
        document.getElementById('memTtlWrap').classList.add('hidden');
        document.getElementById('memoryModal').classList.remove('hidden');
    }

    function closeMemoryModal() {
        document.getElementById('memoryModal').classList.add('hidden');
    }

    function toggleCustomerPhoneField(scope) {
        document.getElementById('memPhoneWrap').classList.toggle('hidden', scope !== 'customer');
    }

    function editMemoryRow(btn) {
        const card = btn.closest('.memory-card');
        if (!card) return;
        document.getElementById('memoryModalTitle').textContent = 'Edit Note';
        document.getElementById('memInputId').value = card.dataset.id;
        document.getElementById('memInputKey').value = card.dataset.key;
        document.getElementById('memInputVal').value = card.dataset.val;
        const phone = card.dataset.phone || '';
        document.getElementById('memInputPhone').value = phone;
        document.getElementById('memInputScope').value = phone ? 'customer' : 'owner';
        document.getElementById('memPhoneWrap').classList.toggle('hidden', !phone);
        const isDurable = (card.dataset.durable === '1');
        document.getElementById('memInputDurable').checked = isDurable;
        document.getElementById('memTtlWrap').classList.toggle('hidden', isDurable);
        document.getElementById('memoryModal').classList.remove('hidden');
    }

    async function submitMemoryForm(e) {
        e.preventDefault();
        const id = document.getElementById('memInputId').value;
        const key = document.getElementById('memInputKey').value;
        const value = document.getElementById('memInputVal').value;
        const scope = document.getElementById('memInputScope').value;
        const phone = (scope === 'customer') ? document.getElementById('memInputPhone').value : '';
        const isDurable = document.getElementById('memInputDurable').checked ? 1 : 0;
        const ttlDays = document.getElementById('memInputTtl').value;

        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=save_memory")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, key, value, contact_phone: phone, is_durable: isDurable, ttl_days: ttlDays })
            });
            const data = await res.json();
            if (data.success) {
                closeMemoryModal();
                window.location.reload();
            } else {
                alert(data.error || 'Failed to save note.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    async function deleteMemoryRow(id, key) {
        if (!confirm(`Are you sure you want to delete "${key}"?`)) return;
        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=delete_memory")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, key })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.querySelector(`.memory-card[data-id="${id}"]`);
                if (card) card.remove();
                const badge = document.getElementById('badgeMemTab');
                if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || '1') - 1);
                const kpi = document.getElementById('kpiTotalMemories');
                if (kpi) kpi.textContent = Math.max(0, parseInt(kpi.textContent || '1') - 1);
            } else {
                alert(data.error || 'Failed to delete note.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    function openTrainingModal() {
        document.getElementById('trainingModalTitle').textContent = 'Add Example Q&A';
        document.getElementById('trainInputId').value = '';
        document.getElementById('trainInputQuery').value = '';
        document.getElementById('trainInputResp').value = '';
        document.getElementById('trainInputConst').value = '';
        document.getElementById('trainingModal').classList.remove('hidden');
    }

    function closeTrainingModal() {
        document.getElementById('trainingModal').classList.add('hidden');
    }

    function editTrainingRow(btn) {
        const card = btn.closest('.training-card');
        if (!card) return;
        document.getElementById('trainingModalTitle').textContent = 'Edit Example Q&A';
        document.getElementById('trainInputId').value = card.dataset.id;
        document.getElementById('trainInputQuery').value = card.dataset.query;
        document.getElementById('trainInputResp').value = card.dataset.resp;
        document.getElementById('trainInputConst').value = card.dataset.const || '';
        document.getElementById('trainingModal').classList.remove('hidden');
    }

    async function submitTrainingForm(e) {
        e.preventDefault();
        const id = document.getElementById('trainInputId').value;
        const query = document.getElementById('trainInputQuery').value;
        const resp = document.getElementById('trainInputResp').value;
        const constraint = document.getElementById('trainInputConst').value;

        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=save_training")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, user_query: query, ideal_response: resp, negative_constraint: constraint })
            });
            const data = await res.json();
            if (data.success) {
                closeTrainingModal();
                window.location.reload();
            } else {
                alert(data.error || 'Failed to save example Q&A.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    async function deleteTrainingRow(id) {
        if (!confirm('Are you sure you want to delete this example Q&A?')) return;
        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=delete_training")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.querySelector(`.training-card[data-id="${id}"]`);
                if (card) card.remove();
                const badge = document.getElementById('badgeTrainTab');
                if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || '1') - 1);
                const kpi = document.getElementById('kpiTotalTraining');
                if (kpi) kpi.textContent = Math.max(0, parseInt(kpi.textContent || '1') - 1);
            } else {
                alert(data.error || 'Failed to delete example Q&A.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    // Auto switch tab if requested in query param or URL hash
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || (window.location.search.includes('page=training') ? 'training' : '') || window.location.hash.replace('#', '');
        if (tab === 'training' || tab === 'memory') {
            switchPageTab(tab);
        }
    });

    // Keyboard shortcut ESC to dismiss modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMemoryModal();
            closeTrainingModal();
        }
    });
</script>
