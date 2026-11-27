<?php
declare(strict_types=1);

$installedCount = count($installedMap);
$totalPresets = count($appStorePresets);

// Curated SVG Icons and badge styling for each tool preset
$toolMeta = [
    'web_search' => [
        'icon_bg' => 'bg-sky-50 text-sky-600 border-sky-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/></svg>'
    ],
    'get_current_time' => [
        'icon_bg' => 'bg-slate-100 text-slate-700 border-slate-200/90',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    ],
    'get_weather' => [
        'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/></svg>'
    ],
    'draft_email' => [
        'icon_bg' => 'bg-violet-50 text-violet-600 border-violet-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>'
    ],
    'confirm_action' => [
        'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>'
    ],
    'update_memory' => [
        'icon_bg' => 'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>'
    ],
    'delete_memory' => [
        'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>'
    ],
    'escalate_to_human' => [
        'icon_bg' => 'bg-amber-50 text-amber-600 border-amber-200/80',
        'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M10.5 8.25h3m-3 3h3"/></svg>'
    ]
];

$defaultToolMeta = [
    'icon_bg' => 'bg-slate-100 text-slate-700 border-slate-200/90',
    'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>'
];

// Map categories for filter counts
$catCounts = [
    'all' => $totalPresets,
    'builtin' => 0,
    'productivity' => 0,
    'api' => 0,
    'webhook' => 0,
    'support' => 0
];

foreach ($appStorePresets as $p) {
    $grp = 'builtin';
    if ($p['category'] === 'rest_api' || $p['category'] === 'shared_env_api') {
        $grp = 'api';
    } elseif ($p['category'] === 'webhook') {
        $grp = 'webhook';
    } elseif (in_array($p['slug'], ['draft_email', 'confirm_action', 'update_memory', 'delete_memory'], true)) {
        $grp = 'productivity';
    } elseif ($p['slug'] === 'escalate_to_human') {
        $grp = 'support';
    }
    if (isset($catCounts[$grp])) {
        $catCounts[$grp]++;
    }
}

// Category tabs definitions (only display if count > 0)
$catDefinitions = [
    'all' => ['label' => 'All Presets', 'count' => $catCounts['all']],
    'builtin' => ['label' => 'Utilities', 'count' => $catCounts['builtin']],
    'productivity' => ['label' => 'Productivity & Safety', 'count' => $catCounts['productivity']],
    'api' => ['label' => 'REST APIs', 'count' => $catCounts['api']],
    'webhook' => ['label' => 'Webhooks', 'count' => $catCounts['webhook']],
    'support' => ['label' => 'Support Desk', 'count' => $catCounts['support']]
];

// Prepare JSON payload for the client-side detail modal
$presetsJson = json_encode(array_map(function($preset) use ($installedMap, $toolMeta, $defaultToolMeta) {
    $isInst = isset($installedMap[$preset['slug']]);
    $meta = $toolMeta[$preset['slug']] ?? $defaultToolMeta;
    return [
        'slug' => $preset['slug'],
        'title' => $preset['title'],
        'svg_icon' => $meta['svg'],
        'icon_bg' => $meta['icon_bg'],
        'category' => $preset['category'],
        'category_label' => $preset['category_label'],
        'group' => $preset['group'] ?? '',
        'short_description' => $preset['short_description'] ?? $preset['description'],
        'description' => $preset['description'],
        'recommended_mode' => $preset['recommended_mode'] ?? 'All Modes',
        'parameters' => $preset['parameters'] ?? [],
        'execution_config' => $preset['execution_config'] ?? [],
        'rbac_roles' => $preset['rbac_roles'] ?? ['admin'],
        'is_installed' => $isInst
    ];
}, $appStorePresets), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
?>

<div class="space-y-4 mb-8">
    <!-- Header: Minimal & Crisp -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tools Store</h1>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                    Function Calling Catalog
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Autonomous presets and capability modules for your WhatsApp AI agent.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span><?= $installedCount ?> of <?= $totalPresets ?> Installed</span>
            </span>
            <a href="<?= e(base_url('index.php?page=tools')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                <span>Installed Tools</span>
            </a>
        </div>
    </div>

    <!-- Toolbar: Search Input + Category Filter Pills -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white rounded-xl border border-slate-200/80 p-2.5 shadow-2xs">
        <!-- Search Input -->
        <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" id="appSearchInput" placeholder="Search by name, slug, or keywords..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200/90 rounded-lg text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 transition-colors">
        </div>

        <!-- Category Tabs (Dynamically only rendered if count > 0) -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0 scrollbar-none" id="categoryTabContainer">
            <?php foreach ($catDefinitions as $catKey => $catInfo): ?>
                <?php if ($catInfo['count'] > 0): ?>
                    <button type="button" onclick="setCategoryFilter('<?= e($catKey) ?>', this)" class="cat-pill <?= $catKey === 'all' ? 'active bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?> px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer shrink-0">
                        <?= e($catInfo['label']) ?> <span class="opacity-75 text-[11px] font-mono ml-0.5">(<?= $catInfo['count'] ?>)</span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Cards Grid (6 per page) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5" id="presetsCardsGrid">
        <?php foreach ($appStorePresets as $preset): 
            $isInstalled = isset($installedMap[$preset['slug']]);
            $paramsCount = count($preset['parameters'] ?? []);
            $meta = $toolMeta[$preset['slug']] ?? $defaultToolMeta;
            
            // Map category for filter
            $filterGroup = 'builtin';
            if ($preset['category'] === 'rest_api' || $preset['category'] === 'shared_env_api') {
                $filterGroup = 'api';
            } elseif ($preset['category'] === 'webhook') {
                $filterGroup = 'webhook';
            } elseif (in_array($preset['slug'], ['draft_email', 'confirm_action', 'update_memory', 'delete_memory'], true)) {
                $filterGroup = 'productivity';
            } elseif ($preset['slug'] === 'escalate_to_human') {
                $filterGroup = 'support';
            }
            $shortDesc = $preset['short_description'] ?? $preset['description'];
        ?>
            <div class="app-card group bg-white rounded-xl border <?= $isInstalled ? 'border-emerald-200/90' : 'border-slate-200/80' ?> p-4 shadow-2xs hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                 tabindex="0"
                 data-slug="<?= e($preset['slug']) ?>"
                 data-category="<?= e($filterGroup) ?>"
                 data-title="<?= e(strtolower($preset['title'])) ?>"
                 data-desc="<?= e(strtolower($preset['description'])) ?>"
                 data-installed="<?= $isInstalled ? '1' : '0' ?>"
                 onclick="openAppModal('<?= e($preset['slug']) ?>')">
                <div>
                    <!-- Top Row: Vector SVG Icon + Title + Status -->
                    <div class="flex items-start justify-between gap-2.5 mb-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-9 h-9 rounded-lg <?= e($meta['icon_bg']) ?> border flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform select-none">
                                <?= $meta['svg'] ?>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-xs sm:text-[13px] font-bold text-slate-900 group-hover:text-slate-800 truncate leading-tight">
                                    <?= e($preset['title']) ?>
                                </h2>
                                <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block mt-0.5 truncate">
                                    <?= e($preset['category_label']) ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($isInstalled): ?>
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Installed
                            </span>
                        <?php else: ?>
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-50 text-slate-500 border border-slate-200/70">
                                Available
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Summary (Crisp & readable) -->
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-3">
                        <?= e($shortDesc) ?>
                    </p>
                </div>

                <!-- Bottom Row: Spec badge + Direct Action -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <span class="text-[11px] font-mono text-slate-500 flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        <?= $paramsCount > 0 ? $paramsCount . ' param' . ($paramsCount > 1 ? 's' : '') : 'Direct call' ?>
                    </span>

                    <div class="flex items-center gap-1.5">
                        <?php if ($isInstalled): ?>
                            <span class="text-[11px] font-semibold text-slate-500 group-hover:text-slate-900 transition-colors flex items-center gap-1">
                                View Specs
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        <?php else: ?>
                            <form method="post" action="<?= e(base_url('index.php?page=app-store-install')) ?>" onclick="event.stopPropagation();" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="preset_slug" value="<?= e($preset['slug']) ?>">
                                <button type="submit" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                                    + Install
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div id="noAppsState" class="hidden bg-white rounded-xl border border-slate-200/80 p-8 text-center">
        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <p class="text-xs font-bold text-slate-800">No matching tools found</p>
        <p class="text-[11px] text-slate-500 mt-0.5">Try searching with a different term or clear the category filter.</p>
        <button type="button" onclick="resetFilters()" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
            Reset Filters
        </button>
    </div>

    <!-- Pagination Controls (Compact & Direct) -->
    <div id="paginationBar" class="flex items-center justify-between gap-3 bg-white rounded-xl border border-slate-200/80 px-3.5 py-2.5 shadow-2xs">
        <div class="text-xs text-slate-500">
            <span id="pageRangeText">Showing 1–6 of <?= $totalPresets ?></span>
        </div>
        <div class="flex items-center gap-1.5" id="paginationButtons">
            <button type="button" id="prevPageBtn" onclick="changePage(-1)" class="px-2.5 py-1 text-xs font-semibold rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer">
                Prev
            </button>
            <div id="pageNumberPills" class="flex items-center gap-1"></div>
            <button type="button" id="nextPageBtn" onclick="changePage(1)" class="px-2.5 py-1 text-xs font-semibold rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer">
                Next
            </button>
        </div>
    </div>
</div>

<!-- Modal Dialog -->
<div id="appModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="closeAppModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200/90 text-left transition-all" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span id="modalIconContainer" class="w-11 h-11 rounded-xl border flex items-center justify-center shrink-0 select-none"></span>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 id="modalTitle" class="text-sm sm:text-base font-bold text-slate-900"></h2>
                            <span id="modalCategoryBadge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200/80"></span>
                        </div>
                        <p class="text-xs font-mono text-slate-500 mt-0.5">slug: <strong id="modalSlug" class="text-slate-800"></strong></p>
                    </div>
                </div>
                <button type="button" onclick="closeAppModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close dialog">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="py-4 space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                <!-- Status & Target Mode -->
                <div class="flex items-center justify-between gap-2 p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-500 font-medium">Status:</span>
                        <span id="modalStatusBadge"></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-500 font-medium">Mode:</span>
                        <span id="modalMode" class="font-bold text-slate-800"></span>
                    </div>
                </div>

                <!-- Full Description -->
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-1">Description</h3>
                    <p id="modalDescription" class="text-xs text-slate-600 leading-relaxed"></p>
                </div>

                <!-- Parameters Section -->
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-1.5">Function Parameters</h3>
                    <div id="modalParametersContainer" class="space-y-1.5"></div>
                </div>

                <!-- Execution Specs -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Access Roles: <strong id="modalRoles" class="text-slate-700 font-mono"></strong></span>
                        <span id="modalHandler" class="text-slate-500 font-mono"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2" id="modalActions">
                <button type="button" onclick="closeAppModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    Close
                </button>
                <div id="modalActionButtons" class="flex items-center gap-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Presets Metadata for Client-Side Modal & Pagination -->
<script type="application/json" id="presetsJsonData">
<?= $presetsJson ?>
</script>

<script>
    const presetsData = JSON.parse(document.getElementById('presetsJsonData').textContent || '[]');
    const presetsMap = {};
    presetsData.forEach(p => { presetsMap[p.slug] = p; });

    let currentCategory = 'all';
    let searchQuery = '';
    let currentPage = 1;
    const pageSize = 6;

    const cards = Array.from(document.querySelectorAll('.app-card'));

    function applyFilters() {
        const query = searchQuery.trim().toLowerCase();
        let matched = cards.filter(card => {
            const cat = card.getAttribute('data-category');
            const title = card.getAttribute('data-title') || '';
            const slug = card.getAttribute('data-slug') || '';
            const desc = card.getAttribute('data-desc') || '';
            const matchCat = (currentCategory === 'all' || cat === currentCategory);
            const matchQuery = !query || title.includes(query) || slug.includes(query) || desc.includes(query);
            return matchCat && matchQuery;
        });

        const totalMatched = matched.length;
        const totalPages = Math.ceil(totalMatched / pageSize) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;

        // Hide all cards first
        cards.forEach(c => c.classList.add('hidden'));

        // Show slice for current page
        matched.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        // Update empty state
        const emptyState = document.getElementById('noAppsState');
        const grid = document.getElementById('presetsCardsGrid');
        const paginationBar = document.getElementById('paginationBar');

        if (totalMatched === 0) {
            emptyState.classList.remove('hidden');
            grid.classList.add('hidden');
            paginationBar.classList.add('hidden');
        } else {
            emptyState.classList.add('hidden');
            grid.classList.remove('hidden');
            paginationBar.classList.remove('hidden');
        }

        // Update pagination numbers & buttons
        const rangeText = document.getElementById('pageRangeText');
        const displayStart = totalMatched === 0 ? 0 : startIndex + 1;
        const displayEnd = Math.min(endIndex, totalMatched);
        rangeText.textContent = `Showing ${displayStart}–${displayEnd} of ${totalMatched}`;

        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        prevBtn.disabled = (currentPage <= 1);
        nextBtn.disabled = (currentPage >= totalPages);

        const pagePills = document.getElementById('pageNumberPills');
        pagePills.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = i;
            btn.className = `w-7 h-7 rounded-md text-xs font-semibold transition-colors cursor-pointer ${
                i === currentPage 
                    ? 'bg-slate-900 text-white' 
                    : 'text-slate-600 hover:bg-slate-100 border border-slate-200'
            }`;
            btn.onclick = () => {
                currentPage = i;
                applyFilters();
            };
            pagePills.appendChild(btn);
        }
    }

    function setCategoryFilter(category, btn) {
        currentCategory = category;
        currentPage = 1;

        document.querySelectorAll('.cat-pill').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'shadow-2xs');
            b.classList.add('text-slate-600', 'hover:bg-slate-100');
        });
        btn.classList.add('bg-slate-900', 'text-white', 'shadow-2xs');
        btn.classList.remove('text-slate-600', 'hover:bg-slate-100');

        applyFilters();
    }

    function resetFilters() {
        currentCategory = 'all';
        searchQuery = '';
        currentPage = 1;

        const searchInput = document.getElementById('appSearchInput');
        if (searchInput) searchInput.value = '';

        const allPill = document.querySelector('.cat-pill');
        if (allPill) setCategoryFilter('all', allPill);
    }

    function changePage(delta) {
        currentPage += delta;
        applyFilters();
    }

    document.getElementById('appSearchInput').addEventListener('input', function(e) {
        searchQuery = e.target.value;
        currentPage = 1;
        applyFilters();
    });

    // Modal Handling
    const modal = document.getElementById('appModal');

    function openAppModal(slug) {
        const preset = presetsMap[slug];
        if (!preset) return;

        // Populate SVG icon
        const iconContainer = document.getElementById('modalIconContainer');
        iconContainer.className = 'w-11 h-11 rounded-xl border flex items-center justify-center shrink-0 select-none ' + (preset.icon_bg || 'bg-slate-100 text-slate-700 border-slate-200/90');
        iconContainer.innerHTML = preset.svg_icon || '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>';

        document.getElementById('modalTitle').textContent = preset.title;
        document.getElementById('modalCategoryBadge').textContent = preset.category_label || preset.category;
        document.getElementById('modalSlug').textContent = preset.slug;
        document.getElementById('modalMode').textContent = preset.recommended_mode || 'All Modes';
        document.getElementById('modalDescription').textContent = preset.description;
        document.getElementById('modalRoles').textContent = (preset.rbac_roles || []).join(', ') || 'admin';

        // Execution details
        let execDesc = '';
        if (preset.execution_config && preset.execution_config.handler) {
            execDesc = 'Handler: ' + preset.execution_config.handler;
        } else if (preset.execution_config && preset.execution_config.url) {
            execDesc = 'HTTP ' + (preset.execution_config.method || 'GET');
        } else if (preset.execution_config && preset.execution_config.target_url) {
            execDesc = 'Webhook POST';
        }
        document.getElementById('modalHandler').textContent = execDesc;

        // Status badge
        const statusBadge = document.getElementById('modalStatusBadge');
        if (preset.is_installed) {
            statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 inline-flex items-center gap-1';
            statusBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Installed';
        } else {
            statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200/60';
            statusBadge.textContent = 'Available to Install';
        }

        // Parameters container
        const paramsContainer = document.getElementById('modalParametersContainer');
        paramsContainer.innerHTML = '';
        const params = preset.parameters || [];
        if (params.length === 0) {
            paramsContainer.innerHTML = '<p class="text-xs text-slate-400 italic">No parameters required — executes directly.</p>';
        } else {
            params.forEach(p => {
                const item = document.createElement('div');
                item.className = 'p-2 rounded-lg bg-slate-50 border border-slate-100 text-xs flex flex-col gap-1';
                item.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-slate-800">${escapeHtml(p.name)}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-200/80 text-slate-600">${escapeHtml(p.type)}</span>
                        </div>
                        <span class="text-[10px] font-semibold ${p.required ? 'text-amber-700' : 'text-slate-400'}">${p.required ? 'Required' : 'Optional'}</span>
                    </div>
                    ${p.description ? `<p class="text-[11px] text-slate-500">${escapeHtml(p.description)}</p>` : ''}
                `;
                paramsContainer.appendChild(item);
            });
        }

        // Footer Action buttons
        const actionButtons = document.getElementById('modalActionButtons');
        actionButtons.innerHTML = '';

        if (preset.is_installed) {
            actionButtons.innerHTML = `
                <a href="<?= e(base_url('index.php?page=tools')) ?>" class="px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-colors">
                    Manage in Tools
                </a>
                <form method="post" action="<?= e(base_url('index.php?page=app-store-uninstall')) ?>" onsubmit="return confirm('Uninstall ${escapeHtml(preset.title)} from your dashboard?');" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="tool_slug" value="${escapeHtml(preset.slug)}">
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                        Uninstall
                    </button>
                </form>
            `;
        } else {
            actionButtons.innerHTML = `
                <form method="post" action="<?= e(base_url('index.php?page=app-store-install')) ?>" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="preset_slug" value="${escapeHtml(preset.slug)}">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                        + Install Tool
                    </button>
                </form>
            `;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAppModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Keyboard support: Escape closes modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeAppModal();
        }
    });

    // Keyboard accessibility for cards
    cards.forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target === card) {
                    e.preventDefault();
                    openAppModal(card.getAttribute('data-slug'));
                }
            }
        });
    });

    // Initialize pagination on load
    applyFilters();
</script>
