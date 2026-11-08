<?php

declare(strict_types=1);

/**
 * WhatsApp Groups Manager Partial
 *
 * Allows non-technical users to discover their WhatsApp Group IDs (@g.us),
 * copy them with one click, send messages to groups, and generate ready-to-use
 * Quick Code snippets in cURL, PHP, JavaScript, and Python.
 */
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">WhatsApp Groups</h1>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" id="groupsCountBadge"><?= count($groups) ?> Groups</span>
        </div>
        <p class="text-[13px] text-slate-500 mt-1 font-medium">Discover your WhatsApp Group IDs (<code class="text-emerald-700 bg-emerald-50/70 px-1 py-0.5 rounded text-[11px] font-mono">@g.us</code>), send dispatches, or generate dynamic API integration code.</p>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="<?= e(base_url('index.php?page=message')) ?>" class="px-3.5 py-2 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center gap-1.5 btn-tactile">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            <span>Compose Message</span>
        </a>
        <button type="button" id="refreshGroupsBtn" onclick="refreshGroupsList(true)" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
            <svg id="refreshSpinner" class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span>Sync from WhatsApp</span>
        </button>
    </div>
</div>

<!-- Non-Technical User Explainer Card -->
<div class="mb-6 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent border border-emerald-500/20 rounded-xl p-4 sm:p-5">
    <div class="flex items-start gap-3.5">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-[14px] font-bold text-slate-900">How WhatsApp Group Messaging Works</h3>
            <p class="text-[12px] text-slate-600 mt-1 leading-relaxed">
                In WhatsApp, groups do not have phone numbers. Instead, each group has a unique <strong>Group ID</strong> ending with <code class="text-emerald-800 font-mono font-semibold">@g.us</code> (for example: <code class="text-slate-800 font-mono font-medium">120363028372648291@g.us</code>).
                To send a message or media to any group, simply pass that Group ID into the <code class="text-slate-800 font-mono">"to"</code> parameter of the API or select it in the composer below.
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] font-medium text-slate-600">
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 1. Connected number must be a member of the group</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 2. Click "Copy ID" on any group below</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 3. Click "Quick Code" to get copy-paste code</span>
            </div>
        </div>
    </div>
</div>

<!-- Search & Controls Bar -->
<div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-3.5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="relative w-full sm:w-80">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" id="groupSearchInput" oninput="filterGroups(this.value)" placeholder="Search by group name or ID..." class="block w-full pl-9 pr-3 py-1.5 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50 focus:bg-white transition-all shadow-subtle text-slate-800">
    </div>

    <div class="flex items-center gap-2 self-end sm:self-auto text-[12px] text-slate-500">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-50 border border-slate-200/80 text-slate-700">
            <span class="w-1.5 h-1.5 rounded-full <?= $deviceConnected ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
            <span><?= $deviceConnected ? ($deviceSleeping ? 'Standby (Auto-Wake)' : 'WhatsApp Online') : 'WhatsApp Offline' ?></span>
        </span>
    </div>
</div>

<!-- Groups Container -->
<div id="groupsListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <?php if (empty($groups)): ?>
        <div class="col-span-full bg-white rounded-xl border border-dashed border-slate-300 p-8 text-center" id="emptyGroupsPlaceholder">
            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800">No WhatsApp Groups Synced Yet</h3>
            <p class="text-[12px] text-slate-500 mt-1 max-w-md mx-auto">
                Make sure your WhatsApp device is connected and belongs to at least one group, then click <strong>Sync from WhatsApp</strong> to fetch your groups.
            </p>
            <div class="mt-4">
                <button type="button" onclick="refreshGroupsList(true)" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition-colors">Sync Groups Now</button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $grp): 
            $jid = (string) ($grp['group_jid'] ?? $grp['id']);
            $subject = (string) ($grp['subject'] ?? 'Unnamed Group');
            $members = (int) ($grp['participants_count'] ?? 0);
            $isAdmin = !empty($grp['is_admin']);
            $isAnnounce = !empty($grp['announce']);
            $initial = mb_strtoupper(mb_substr($subject, 0, 1, 'UTF-8'), 'UTF-8');
            $desc = trim((string) ($grp['description'] ?? ''));
        ?>
            <div class="group-card bg-white rounded-xl border border-slate-200/80 shadow-subtle hover:shadow-card hover:border-emerald-300/80 transition-all p-4 flex flex-col justify-between" data-name="<?= e(strtolower($subject)) ?>" data-jid="<?= e(strtolower($jid)) ?>">
                <div>
                    <!-- Header with Avatar & Title -->
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-[14px] flex items-center justify-center shrink-0 shadow-sm">
                            <?= e($initial ?: 'G') ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[13px] font-bold text-slate-900 truncate" title="<?= e($subject) ?>"><?= e($subject) ?></h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    <?= $members > 0 ? e((string)$members) . ' members' : 'Active group' ?>
                                </span>
                                <?php if ($isAdmin): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Admin</span>
                                <?php endif; ?>
                                <?php if ($isAnnounce): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700">Announce-only</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($desc !== ''): ?>
                        <p class="text-[11px] text-slate-500 mb-3 line-clamp-2 leading-relaxed" title="<?= e($desc) ?>"><?= e($desc) ?></p>
                    <?php endif; ?>

                    <!-- Group ID with 1-Click Copy -->
                    <div class="mb-3">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">WhatsApp Group ID</div>
                        <div class="flex items-center justify-between gap-1.5 bg-slate-50 border border-slate-200/90 rounded-lg px-2.5 py-1.5">
                            <span class="font-mono text-[11px] text-slate-800 truncate select-all" title="<?= e($jid) ?>"><?= e($jid) ?></span>
                            <button type="button" onclick="copyText('<?= e($jid) ?>', this)" class="shrink-0 text-slate-500 hover:text-slate-900 p-1 rounded hover:bg-white transition-colors" title="Copy Group ID">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= e(base_url('index.php?page=message&group=' . urlencode($jid))) ?>" class="flex-1 text-center px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors shadow-subtle btn-tactile">
                        Send Message
                    </a>
                    <button type="button" onclick="openQuickCodeModal('<?= e(addslashes($jid)) ?>', '<?= e(addslashes($subject)) ?>')" class="flex-1 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 rounded-lg transition-colors flex items-center justify-center gap-1 btn-tactile">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span>Quick Code</span>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================================== -->
<!-- DYNAMIC QUICK CODE MODAL / POPUP                               -->
<!-- ============================================================== -->
<div id="quickCodeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" onclick="if(event.target===this) closeQuickCodeModal()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <div>
                    <h2 class="text-[14px] font-bold text-slate-900">API Quick Code</h2>
                    <p class="text-[11px] text-slate-500">Target Group: <span id="modalGroupSubject" class="font-semibold text-slate-800"></span> (<span id="modalGroupJid" class="font-mono text-emerald-700"></span>)</p>
                </div>
            </div>
            <button type="button" onclick="closeQuickCodeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Language Tabs -->
        <div class="px-6 pt-3 border-b border-slate-100 flex items-center gap-2 bg-white">
            <button type="button" onclick="switchQuickCodeTab('curl')" id="tabBtn_curl" class="quickcode-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-emerald-600 text-emerald-700">cURL (Bash)</button>
            <button type="button" onclick="switchQuickCodeTab('php')" id="tabBtn_php" class="quickcode-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">PHP</button>
            <button type="button" onclick="switchQuickCodeTab('js')" id="tabBtn_js" class="quickcode-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">JavaScript (Node)</button>
            <button type="button" onclick="switchQuickCodeTab('python')" id="tabBtn_python" class="quickcode-tab px-3 py-1.5 text-[12px] font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-900">Python</button>
        </div>

        <!-- Code Content Area -->
        <div class="p-6 bg-slate-950 text-slate-100 relative font-mono text-[12px]">
            <button type="button" id="copyCodeSnippetBtn" onclick="copyCurrentCodeSnippet(this)" class="absolute top-4 right-4 px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[11px] font-sans font-semibold flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                <span>Copy Code</span>
            </button>
            <pre class="overflow-x-auto p-2 leading-relaxed text-[12px] font-mono text-emerald-400" id="quickCodeSnippet"></pre>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <div>
                API Endpoint: <code class="font-mono text-slate-700 font-semibold"><?= e(base_url('api/send.php')) ?></code>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeQuickCodeModal()" class="px-3.5 py-1.5 text-[12px] font-semibold text-slate-600 hover:text-slate-900 rounded-lg transition-colors">Close</button>
                <a id="modalComposeLink" href="#" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">Open in Composer</a>
            </div>
        </div>
    </div>
</div>

<script>
window.__WA_GROUPS_CFG = {
    apiUrl: '<?= e(base_url('api/send.php')) ?>',
    groupsApiUrl: '<?= e(base_url('api/groups.php')) ?>',
    apiKey: '<?= e($primaryApiKey) ?>',
    composeUrl: '<?= e(base_url('index.php?page=message&group=')) ?>'
};

let currentModalJid = '';
let currentModalSubject = '';
let currentActiveTab = 'curl';

function openQuickCodeModal(jid, subject) {
    currentModalJid = jid;
    currentModalSubject = subject;
    document.getElementById('modalGroupSubject').textContent = subject;
    document.getElementById('modalGroupJid').textContent = jid;
    document.getElementById('modalComposeLink').href = window.__WA_GROUPS_CFG.composeUrl + encodeURIComponent(jid);
    
    switchQuickCodeTab(currentActiveTab);
    
    const modal = document.getElementById('quickCodeModal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    modal.querySelector('.transform').classList.remove('scale-95');
    modal.querySelector('.transform').classList.add('scale-100');
}

function closeQuickCodeModal() {
    const modal = document.getElementById('quickCodeModal');
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.querySelector('.transform').classList.remove('scale-100');
    modal.querySelector('.transform').classList.add('scale-95');
}

function switchQuickCodeTab(lang) {
    currentActiveTab = lang;
    document.querySelectorAll('.quickcode-tab').forEach(b => {
        b.classList.remove('border-emerald-600', 'text-emerald-700');
        b.classList.add('border-transparent', 'text-slate-500');
    });
    const activeBtn = document.getElementById('tabBtn_' + lang);
    if (activeBtn) {
        activeBtn.classList.add('border-emerald-600', 'text-emerald-700');
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
    }

    const pre = document.getElementById('quickCodeSnippet');
    pre.textContent = generateSnippet(lang, currentModalJid, window.__WA_GROUPS_CFG.apiUrl, window.__WA_GROUPS_CFG.apiKey);
}

function generateSnippet(lang, jid, url, apiKey) {
    if (lang === 'curl') {
        return `curl -X POST "${url}" \\
  -H "Authorization: Bearer ${apiKey}" \\
  -H "Content-Type: application/json" \\
  -d '{
    "to": "${jid}",
    "message": "Hello WhatsApp Group! 🚀"
  }'`;
    }

    if (lang === 'php') {
        return `<?='<'.'?php'?>
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
    }

    if (lang === 'js') {
        return `const response = await fetch('${url}', {
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
    }

    if (lang === 'python') {
        return `import requests

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

    return '';
}

function copyCurrentCodeSnippet(btn) {
    const code = document.getElementById('quickCodeSnippet').textContent;
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

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="text-emerald-600 text-[10px] font-bold">Copied!</span>';
        setTimeout(() => {
            btn.innerHTML = orig;
        }, 1400);
    });
}

function filterGroups(query) {
    const q = (query || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.group-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const jid = card.getAttribute('data-jid') || '';
        if (name.includes(q) || jid.includes(q)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const badge = document.getElementById('groupsCountBadge');
    if (badge) {
        badge.textContent = visibleCount + ' Groups';
    }
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

function renderGroupsDOM(groups) {
    const container = document.getElementById('groupsListContainer');
    if (!container) return;

    const countBadge = document.getElementById('groupsCountBadge');
    if (countBadge) {
        countBadge.textContent = groups.length + ' Groups';
    }

    if (!groups || groups.length === 0) {
        container.innerHTML = `
            <div class="col-span-full bg-white rounded-xl border border-dashed border-slate-300 p-8 text-center" id="emptyGroupsPlaceholder">
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-[14px] font-bold text-slate-800">No WhatsApp Groups Synced Yet</h3>
                <p class="text-[12px] text-slate-500 mt-1 max-w-md mx-auto">
                    Make sure your WhatsApp device is connected and belongs to at least one group, then click <strong>Sync from WhatsApp</strong> to fetch your groups.
                </p>
                <div class="mt-4">
                    <button type="button" onclick="refreshGroupsList(true)" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition-colors">Sync Groups Now</button>
                </div>
            </div>`;
        return;
    }

    container.innerHTML = groups.map(grp => {
        const jid = grp.id || grp.group_jid || '';
        const subject = grp.subject || 'Unnamed Group';
        const members = Number(grp.participants_count) || 0;
        const isAdmin = Boolean(grp.is_admin);
        const isAnnounce = Boolean(grp.announce);
        const initial = subject.charAt(0).toUpperCase() || 'G';
        const desc = (grp.description || grp.desc || '').trim();

        const safeJid = escapeHtml(jid);
        const safeSubject = escapeHtml(subject);
        const safeDesc = escapeHtml(desc);

        return `
            <div class="group-card bg-white rounded-xl border border-slate-200/80 shadow-subtle hover:shadow-card hover:border-emerald-300/80 transition-all p-4 flex flex-col justify-between" data-name="${escapeHtml(subject.toLowerCase())}" data-jid="${escapeHtml(jid.toLowerCase())}">
                <div>
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-[14px] flex items-center justify-center shrink-0 shadow-sm">
                            ${escapeHtml(initial)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[13px] font-bold text-slate-900 truncate" title="${safeSubject}">${safeSubject}</h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    ${members > 0 ? members + ' members' : 'Active group'}
                                </span>
                                ${isAdmin ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Admin</span>' : ''}
                                ${isAnnounce ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700">Announce-only</span>' : ''}
                            </div>
                        </div>
                    </div>

                    ${desc ? `<p class="text-[11px] text-slate-500 mb-3 line-clamp-2 leading-relaxed" title="${safeDesc}">${safeDesc}</p>` : ''}

                    <div class="mb-3">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">WhatsApp Group ID</div>
                        <div class="flex items-center justify-between gap-1.5 bg-slate-50 border border-slate-200/90 rounded-lg px-2.5 py-1.5">
                            <span class="font-mono text-[11px] text-slate-800 truncate select-all" title="${safeJid}">${safeJid}</span>
                            <button type="button" onclick="copyText('${safeJid}', this)" class="shrink-0 text-slate-500 hover:text-slate-900 p-1 rounded hover:bg-white transition-colors" title="Copy Group ID">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="${window.__WA_GROUPS_CFG.composeUrl + encodeURIComponent(jid)}" class="flex-1 text-center px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors shadow-subtle btn-tactile">
                        Send Message
                    </a>
                    <button type="button" onclick="openQuickCodeModal('${safeJid}', '${safeSubject.replace(/'/g, "\\'")}')" class="flex-1 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 rounded-lg transition-colors flex items-center justify-center gap-1 btn-tactile">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span>Quick Code</span>
                    </button>
                </div>
            </div>`;
    }).join('');
}

async function refreshGroupsList(force = false) {
    const btn = document.getElementById('refreshGroupsBtn');
    const spinner = document.getElementById('refreshSpinner');
    if (spinner) spinner.classList.add('animate-spin');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch(window.__WA_GROUPS_CFG.groupsApiUrl + (force ? '?refresh=1' : ''));
        const data = await res.json();
        if (data.ok && Array.isArray(data.groups)) {
            renderGroupsDOM(data.groups);
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="text-emerald-400 font-bold">✓ Synced</span>';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        } else {
            alert(data.error || 'Failed to sync groups. Please ensure WhatsApp device is connected.');
        }
    } catch (e) {
        alert('Network error while syncing groups: ' + e.message);
    } finally {
        if (spinner) spinner.classList.remove('animate-spin');
        if (btn) btn.disabled = false;
    }
}
</script>
