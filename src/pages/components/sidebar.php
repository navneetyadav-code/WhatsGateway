<?php

declare(strict_types=1);

$currentPage = $page ?? ($_GET['page'] ?? 'dashboard');
$userForSidebar = $user ?? current_user();
$userId = (int) ($userForSidebar['id'] ?? 0);

$agentMode = 'support';
$workspaceMode = (string) ($userForSidebar['workspace_mode'] ?? '');
if ($userId > 0) {
    try {
        $pdo = Database::pdo();
        if ($workspaceMode === '') {
            $stmt = $pdo->prepare('SELECT workspace_mode FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $workspaceMode = (string) ($stmt->fetchColumn() ?: '');
        }
        $stmt = $pdo->prepare('SELECT agent_mode FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $agentMode = (string) ($stmt->fetchColumn() ?: 'support');
    } catch (\Throwable $e) {}
}

if (!in_array($workspaceMode, ['personal', 'support', 'api_only'], true)) {
    $workspaceMode = ($agentMode === 'personal') ? 'personal' : 'support';
}

$isPersonalMode = ($workspaceMode === 'personal');
$isApiOnly = ($workspaceMode === 'api_only');
$isSupportMode = ($workspaceMode === 'support');

if (!isset($initials)) {
    $nameParts = preg_split('/\s+/', trim((string) ($userForSidebar['name'] ?? 'Admin'))) ?: [];
    $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . substr($nameParts[1] ?? 'D', 0, 1));
}

$isActive = static function (array $pages) use ($currentPage): bool {
    return in_array($currentPage, $pages, true);
};

/*
|--------------------------------------------------------------------------
| Navigation structure: Workspace direct, Categories grouped, ZERO count cards
|--------------------------------------------------------------------------
*/

$workspaceDirectItems = [
    [
        'label' => 'Overview',
        'page' => 'dashboard',
        'pages' => ['dashboard'],
        'icon' => 'dashboard',
    ],
    [
        'label' => 'Connect WhatsApp',
        'page' => 'device',
        'pages' => ['device'],
        'icon' => 'phone',
    ],
    [
        'label' => 'New Message',
        'page' => 'message',
        'pages' => ['message'],
        'icon' => 'edit',
    ],
    [
        'label' => 'API Keys',
        'page' => 'api-keys',
        'pages' => ['api-keys'],
        'icon' => 'key',
    ],
];

$whatsappCategory = [
    'title' => 'WhatsApp',
    'type' => 'group',
    'icon' => 'whatsapp',
    'items' => [
        [
            'label' => 'WhatsApp Groups',
            'page' => 'groups',
            'pages' => ['groups'],
            'icon' => 'user-group',
        ],
        [
            'label' => 'Contacts',
            'page' => 'contacts',
            'pages' => ['contacts'],
            'icon' => 'users',
        ],
        [
            'label' => 'Message Queue',
            'page' => 'queue',
            'pages' => ['queue'],
            'icon' => 'queue',
        ],
        [
            'label' => 'Webhooks',
            'page' => 'webhooks',
            'pages' => ['webhooks'],
            'icon' => 'webhook',
        ],
    ],
];

$adminCategory = [
    'title' => 'Administration',
    'type' => 'direct',
    'items' => [
        [
            'label' => 'Settings',
            'page' => 'settings',
            'pages' => ['settings', 'security'],
            'icon' => 'settings',
        ],
        [
            'label' => 'Audit Logs',
            'page' => 'audit',
            'pages' => ['audit'],
            'icon' => 'audit',
        ],
    ],
];

if ($isPersonalMode) {
    $navigation = [
        'workspace' => [
            'title' => 'Workspace',
            'type' => 'direct',
            'items' => $workspaceDirectItems,
        ],
        'whatsapp' => $whatsappCategory,
        'personal_assistant' => [
            'title' => 'Personal Assistant',
            'type' => 'group',
            'icon' => 'bot',
            'items' => [
                [
                    'label' => 'Personal Assistant',
                    'page' => 'personal-assistant',
                    'pages' => ['chatbot', 'ai-agent', 'support-bot', 'personal-assistant'],
                    'icon' => 'bot',
                ],
                [
                    'label' => 'AI Providers & SMTP',
                    'page' => 'ai-credentials',
                    'pages' => ['ai-credentials', 'ai-keys'],
                    'icon' => 'plug',
                ],
                [
                    'label' => 'Custom Tools',
                    'page' => 'tools',
                    'pages' => ['tools'],
                    'icon' => 'tools',
                ],
                [
                    'label' => 'Tools Store',
                    'page' => 'app-store',
                    'pages' => ['app-store', 'presets'],
                    'icon' => 'store',
                ],
                [
                    'label' => 'Memory & Training',
                    'page' => 'memory',
                    'pages' => ['memory', 'training'],
                    'icon' => 'memory',
                ],
            ],
        ],
        'administration' => $adminCategory,
    ];
} elseif ($isSupportMode) {
    $navigation = [
        'workspace' => [
            'title' => 'Workspace',
            'type' => 'direct',
            'items' => $workspaceDirectItems,
        ],
        'whatsapp' => $whatsappCategory,
        'customer_support' => [
            'title' => 'Customer Support',
            'type' => 'group',
            'icon' => 'headset',
            'items' => [
                [
                    'label' => 'Live Chat',
                    'page' => 'live-chat',
                    'pages' => ['live-chat'],
                    'icon' => 'chat',
                ],
                [
                    'label' => 'Customer Support Bot',
                    'page' => 'support-bot',
                    'pages' => ['chatbot', 'ai-agent', 'support-bot'],
                    'icon' => 'bot',
                ],
                [
                    'label' => 'Company Knowledge Base',
                    'page' => 'knowledge',
                    'pages' => ['knowledge'],
                    'icon' => 'book',
                ],
                [
                    'label' => 'Escalations & Alarms',
                    'page' => 'escalations',
                    'pages' => ['escalations'],
                    'icon' => 'alert',
                ],
                [
                    'label' => 'Customer Feedback',
                    'page' => 'feedback',
                    'pages' => ['feedback'],
                    'icon' => 'star',
                ],
                [
                    'label' => 'AI Providers & SMTP',
                    'page' => 'ai-credentials',
                    'pages' => ['ai-credentials', 'ai-keys'],
                    'icon' => 'plug',
                ],
                [
                    'label' => 'Custom Tools',
                    'page' => 'tools',
                    'pages' => ['tools'],
                    'icon' => 'tools',
                ],
                [
                    'label' => 'Tools Store',
                    'page' => 'app-store',
                    'pages' => ['app-store', 'presets'],
                    'icon' => 'store',
                ],
                [
                    'label' => 'Memory & Training',
                    'page' => 'memory',
                    'pages' => ['memory', 'training'],
                    'icon' => 'memory',
                ],
                [
                    'label' => 'Campaigns',
                    'page' => 'campaigns',
                    'pages' => ['campaigns'],
                    'icon' => 'megaphone',
                ],
            ],
        ],
        'administration' => $adminCategory,
    ];
} else {
    // API & Webhooks Only
    $navigation = [
        'workspace' => [
            'title' => 'Workspace',
            'type' => 'direct',
            'items' => $workspaceDirectItems,
        ],
        'whatsapp' => $whatsappCategory,
        'administration' => $adminCategory,
    ];
}

foreach ($navigation as $key => &$category) {
    $category['items'] = array_values(
        array_filter(
            $category['items'],
            static function (array $item) use ($isPersonalMode): bool {
                if (($item['mode'] ?? null) === 'support' && $isPersonalMode) {
                    return false;
                }
                return true;
            }
        )
    );

    $category['active'] = false;
    foreach ($category['items'] as $item) {
        if ($isActive($item['pages'])) {
            $category['active'] = true;
            break;
        }
    }
}
unset($category);

/*
|--------------------------------------------------------------------------
| SVG Icon Paths
|--------------------------------------------------------------------------
*/

$iconPaths = [
    'dashboard' =>
        '<rect x="3" y="3" width="7" height="9" rx="1"/>
         <rect x="14" y="3" width="7" height="5" rx="1"/>
         <rect x="14" y="12" width="7" height="9" rx="1"/>
         <rect x="3" y="16" width="7" height="5" rx="1"/>',

    'edit' =>
        '<path d="M12 20h9"/>
         <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',

    'user-group' =>
        '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
         <circle cx="9" cy="7" r="4"/>
         <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
         <path d="M16 3.13a4 4 0 0 1 0 7.75"/>',

    'phone' =>
        '<rect x="6" y="2" width="12" height="20" rx="2"/>
         <path d="M11 18h2"/>',

    'whatsapp' =>
        '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
         <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2z"/>',

    'key' =>
        '<circle cx="8" cy="15" r="5"/>
         <path d="m21 2-9.6 9.6M15.5 7.5l3 3L21 8l-3-3"/>',

    'users' =>
        '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
         <circle cx="9" cy="7" r="4"/>
         <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
         <path d="M16 3.13a4 4 0 0 1 0 7.75"/>',

    'chat' =>
        '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',

    'headset' =>
        '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
         <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',

    'megaphone' =>
        '<path d="m3 11 18-5v12L3 14v-3z"/>
         <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',

    'queue' =>
        '<path d="M4 6h16M4 12h16M4 18h16"/>',

    'star' =>
        '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',

    'alert' =>
        '<circle cx="12" cy="12" r="10"/>
         <line x1="12" y1="8" x2="12" y2="12"/>
         <line x1="12" y1="16" x2="12.01" y2="16"/>',

    'ai' =>
        '<rect x="3" y="7" width="18" height="13" rx="2"/>
         <path d="M8 7V4h8v3M12 11v5m-2.5-2.5h5"/>
         <path d="M7 20v2m10-2v2"/>',

    'bot' =>
        '<rect x="4" y="7" width="16" height="13" rx="3"/>
         <path d="M12 3v4m-4 6h.01M16 13h.01M9 17h6"/>
         <path d="M2 12h2m16 0h2"/>',

    'plug' =>
        '<path d="M12 22v-5"/>
         <path d="M9 8V2m6 6V2"/>
         <path d="M5 8h14v4a7 7 0 0 1-14 0z"/>',

    'memory' =>
        '<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',

    'book' =>
        '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
         <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',

    'workflow' =>
        '<rect x="3" y="3" width="7" height="7" rx="1"/>
         <rect x="14" y="14" width="7" height="7" rx="1"/>
         <path d="M6.5 10v4a2 2 0 0 0 2 2H14m0-3 3.5 3-3.5 3"/>',

    'tools' =>
        '<path d="M14.7 6.3a4 4 0 0 0-5.6 5.6l-6.5 6.5a2 2 0 0 0 2.8 2.8l6.5-6.5a4 4 0 0 0 5.6-5.6l-3 3-3-3z"/>',

    'store' =>
        '<path d="M3 9h18l-1.5-6h-15z"/>
         <path d="M5 9v12h14V9M9 21v-7h6v7"/>
         <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>',

    'integrations' =>
        '<path d="M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/>
         <path d="M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/>',

    'webhook' =>
        '<path d="M18 8a3 3 0 1 0-3-3"/>
         <path d="M6 16a3 3 0 1 0 0 6"/>
         <path d="M18 16a3 3 0 1 1 0 6"/>
         <path d="m8.5 14 4-7m3 7-4-7M9 19h6"/>',

    'settings' =>
        '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
         <circle cx="12" cy="12" r="3"/>',

    'shield' =>
        '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>
         <path d="m9 12 2 2 4-4"/>',

    'audit' =>
        '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
         <path d="M14 2v6h6M8 13h8m-8 4h8"/>',

    'chevron' =>
        '<path d="m9 18 6-6-6-6"/>',

    'logout' =>
        '<path d="M10 17l5-5-5-5"/>
         <path d="M15 12H3"/>
         <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
];

/*
|--------------------------------------------------------------------------
| Icon & URL renderers
|--------------------------------------------------------------------------
*/

$renderIcon = static function (string $name, string $class = 'sidebar-icon') use ($iconPaths): string {
    $path = $iconPaths[$name] ?? $iconPaths['dashboard'];
    return '<svg class="' . e($class) . '" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">'
        . $path .
        '</svg>';
};

$pageUrl = static function (string $pageName): string {
    return e(base_url('index.php?page=' . urlencode($pageName)));
};

$renderItem = static function (array $item, string $type = 'normal') use ($isActive, $renderIcon, $pageUrl): string {
    $active = $isActive($item['pages']);
    $class = $type === 'sub'
        ? 'sidebar-sublink'
        : ($type === 'flyout'
            ? 'sidebar-flyout-link'
            : 'sidebar-link');

    if ($active) {
        $class .= ' is-active';
    }

    $iconClass = $type === 'sub'
        ? 'sidebar-subicon'
        : 'sidebar-icon';

    $tooltipAttr = ($type === 'normal') ? ' data-tooltip="' . e($item['label']) . '"' : '';

    $html = '<a href="' . $pageUrl($item['page']) . '" class="' . $class . '" ' . ($active ? 'aria-current="page"' : '') . $tooltipAttr . ' title="' . e($item['label']) . '">';
    if ($type === 'flyout') {
        $html .= '<span class="truncate">' . e($item['label']) . '</span>';
    } else {
        $html .= '<span class="sidebar-item-content">';
        $html .= '<span class="sidebar-icon-wrap relative flex items-center justify-center shrink-0">';
        $html .= $renderIcon($item['icon'], $iconClass);
        $html .= '</span>';
        $html .= '<span class="sidebar-label truncate">' . e($item['label']) . '</span>';
        $html .= '</span>';
    }
    $html .= '</a>';

    return $html;
};

?>

<aside
    id="sidebar"
    class="bg-[#F8FAFC] border-r border-slate-200/80 text-slate-600
    w-[280px] lg:w-[288px] flex-shrink-0 flex flex-col z-30
    h-screen max-h-screen min-h-0 overflow-hidden
    fixed inset-y-0 left-0 -translate-x-full
    md:relative md:translate-x-0 select-none transition-transform duration-200"
    aria-label="Application sidebar"
>
    <!-- Header -->
    <div class="sidebar-header h-[68px] flex items-center justify-between px-4 sm:px-5 shrink-0 border-b border-slate-200/70 relative">
        <a href="<?= $pageUrl('dashboard') ?>" class="sidebar-brand flex items-center gap-3 text-slate-900 group min-w-0" title="WhatsGateway Dashboard">
            <span class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-slate-200 shadow-sm shrink-0">
                <img src="<?= e(base_url('assets/logo_white.png')) ?>" alt="WhatsGateway" class="h-7 w-7 rounded-lg object-contain">
            </span>
            <span class="sidebar-brand-text font-extrabold tracking-[-0.03em] text-[16px] truncate">
                WhatsGateway
            </span>
        </a>

        <div class="flex items-center gap-1 shrink-0">
            <button id="collapseSidebar" type="button" class="hidden md:flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-200/60 transition-colors cursor-pointer" aria-label="Toggle sidebar collapse" title="Collapse sidebar (Ctrl+B)">
                <svg class="sidebar-chevron w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <button id="closeSidebar" type="button" class="md:hidden text-slate-400 hover:text-slate-900 p-2 -mr-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close navigation">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation List (Smoothly scrollable, categorized, accordion groups, zero count cards) -->
    <nav id="sidebarNav" class="sidebar-scroll flex-1 min-h-0 overflow-y-auto py-5 px-3 font-medium text-[13px] space-y-5" aria-label="Primary navigation">
        <?php foreach ($navigation as $categoryKey => $category): ?>
            <?php
                $items = $category['items'];
                $categoryActive = $category['active'];
                if (empty($items)) continue;
            ?>
            <section class="sidebar-category" data-category="<?= e($categoryKey) ?>">
                <div class="sidebar-category-title">
                    <?= e($category['title']) ?>
                </div>

                <?php if ($category['type'] === 'direct'): ?>
                    <!-- Direct Navigation Items -->
                    <div class="space-y-1">
                        <?php foreach ($items as $item): ?>
                            <?= $renderItem($item, 'normal') ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Collapsible Dropdown Group -->
                    <?php
                        $isGroupOpen = $categoryActive;
                    ?>
                    <div class="sidebar-group <?= $isGroupOpen ? 'is-open' : '' ?>" data-group="<?= e($categoryKey) ?>">
                        <button
                            type="button"
                            class="sidebar-group-btn sidebar-link justify-between w-full cursor-pointer select-none <?= $categoryActive ? 'group-active' : '' ?>"
                            aria-expanded="<?= $isGroupOpen ? 'true' : 'false' ?>"
                            title="<?= e($category['title']) ?>"
                        >
                            <span class="sidebar-item-content">
                                <span class="sidebar-icon-wrap relative flex items-center justify-center shrink-0">
                                    <?= $renderIcon($category['icon']) ?>
                                </span>
                                <span class="sidebar-label truncate">
                                    <?= e($category['title']) ?>
                                </span>
                            </span>

                            <span class="sidebar-group-meta flex items-center shrink-0">
                                <svg class="sidebar-chevron-down w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>

                        <!-- Child Sublinks -->
                        <div class="sidebar-group-content pl-4 pr-1 space-y-0.5 mt-1 border-l-2 border-slate-200/80 ml-5 transition-all">
                            <?php foreach ($items as $item): ?>
                                <?= $renderItem($item, 'sub') ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Collapsed Rail Flyout Menu -->
                        <div class="sidebar-flyout-menu hidden absolute left-[74px] top-0 w-56 bg-white rounded-xl border border-slate-200/90 shadow-xl py-2 px-1 z-50">
                            <div class="px-3 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                                <?= e($category['title']) ?>
                            </div>
                            <?php foreach ($items as $item): ?>
                                <?= $renderItem($item, 'flyout') ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </nav>

    <!-- User Profile Footer -->
    <div class="sidebar-footer p-3 border-t border-slate-200/70 bg-[#F8FAFC] shrink-0">
        <div class="sidebar-user-card flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white border border-slate-200/80 shadow-subtle transition-all" data-tooltip="<?= e($userForSidebar['name'] ?? 'Admin') ?>">
            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold font-mono shrink-0" title="<?= e($userForSidebar['name'] ?? 'Admin') ?>">
                <?= e($initials) ?>
            </div>
            <div class="sidebar-user-meta flex-1 truncate min-w-0">
                <p class="text-[12px] font-semibold text-slate-900 truncate leading-tight">
                    <?= e($userForSidebar['name'] ?? 'Admin') ?>
                </p>
                <p class="text-[10px] text-slate-400 truncate font-mono mt-0.5">
                    <?= $isApiOnly ? 'REST API & Webhooks' : ($isPersonalMode ? 'Personal Assistant' : 'Customer Support') ?>
                </p>
            </div>
            <form method="post" action="<?= e(base_url('index.php?page=logout')) ?>" title="Sign out" class="sidebar-user-logout shrink-0">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Sign out">
                    <?= $renderIcon('logout', 'w-4 h-4') ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            try {
                var nav = document.getElementById('sidebarNav') || document.querySelector('.sidebar-scroll');

                // 1. Accordion group open/close logic
                document.querySelectorAll('#sidebar .sidebar-group').forEach(function(group) {
                    var groupKey = group.getAttribute('data-group');
                    if (!groupKey) return;
                    // If the active page is inside this group, ensure it is open
                    if (group.querySelector('.is-active')) {
                        group.classList.add('is-open');
                        var btn = group.querySelector('.sidebar-group-btn');
                        if (btn) btn.setAttribute('aria-expanded', 'true');
                        return;
                    }
                    var stored = localStorage.getItem('sidebar_group_' + groupKey);
                    var btn = group.querySelector('.sidebar-group-btn');
                    if (stored === 'open') {
                        group.classList.add('is-open');
                        if (btn) btn.setAttribute('aria-expanded', 'true');
                    } else if (stored === 'closed') {
                        group.classList.remove('is-open');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    }
                });

                // 2. Exact scroll position preservation across full-page reloads and navigations
                if (nav) {
                    var savedScroll = sessionStorage.getItem('sidebar_scroll_top');
                    if (savedScroll !== null) {
                        var parsed = parseInt(savedScroll, 10);
                        nav.scrollTop = parsed;
                        requestAnimationFrame(function() {
                            nav.scrollTop = parsed;
                        });
                        window.addEventListener('load', function() {
                            nav.scrollTop = parsed;
                        });
                    } else {
                        var activeItem = nav.querySelector('.is-active');
                        if (activeItem) {
                            var nRect = nav.getBoundingClientRect();
                            var aRect = activeItem.getBoundingClientRect();
                            if (aRect.top < nRect.top || aRect.bottom > nRect.bottom) {
                                activeItem.scrollIntoView({ block: 'nearest' });
                            }
                        }
                    }

                    // Continuously save scroll position
                    nav.addEventListener('scroll', function() {
                        sessionStorage.setItem('sidebar_scroll_top', nav.scrollTop);
                    }, { passive: true });

                    // Save position immediately when clicking any navigation link
                    nav.addEventListener('click', function(e) {
                        var link = e.target.closest('a');
                        if (link) {
                            sessionStorage.setItem('sidebar_scroll_top', nav.scrollTop);
                        }
                    });

                    // Save position before page unload / navigation
                    window.addEventListener('beforeunload', function() {
                        sessionStorage.setItem('sidebar_scroll_top', nav.scrollTop);
                    });
                }
            } catch (e) {}
        })();
    </script>
</aside>
