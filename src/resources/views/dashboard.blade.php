<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SecureScan — Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Sora:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Sora', sans-serif; background: #f1f5f9; color: #1e293b; }

        /* ── Sidebar ── */
        .ss-sidebar {
            width: 220px; height: 100vh; position: fixed; top: 0; left: 0;
            background: #0f1623; display: flex; flex-direction: column; z-index: 100;
        }
        .ss-logo {
            display: flex; align-items: center; gap: 10px;
            padding: 22px 20px; border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .ss-logo-icon {
            width: 30px; height: 30px; border-radius: 8px; background: #22c55e;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ss-logo-icon svg { width: 16px; height: 16px; }
        .ss-logo-text { font-size: 15px; font-weight: 600; color: #f1f5f9; }
        .ss-nav { flex: 1; padding: 16px 0; }
        .ss-nav-label {
            font-size: 10px; font-weight: 500; color: #475569;
            text-transform: uppercase; letter-spacing: 0.08em;
            padding: 0 20px 8px; margin-top: 12px;
        }
        .ss-nav a {
            display: flex; align-items: center; gap: 10px; padding: 10px 20px;
            font-size: 13px; color: #94a3b8; text-decoration: none;
            border-left: 2px solid transparent;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .ss-nav a:hover { background: rgba(255,255,255,0.04); color: #f1f5f9; }
        .ss-nav a.active { background: rgba(34,197,94,0.08); color: #22c55e; border-left-color: #22c55e; }
        .ss-nav a svg { width: 15px; height: 15px; flex-shrink: 0; }
        .ss-pro-badge {
            margin: 12px 16px 20px; padding: 12px 14px;
            background: rgba(34,197,94,0.08); border-radius: 10px;
            border: 1px solid rgba(34,197,94,0.18);
        }
        .ss-pro-badge strong { display: block; font-size: 12px; color: #22c55e; font-weight: 500; }
        .ss-pro-badge span { font-size: 11px; color: #64748b; }

        /* ── Main ── */
        .ss-main { margin-left: 220px; min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Topbar ── */
        .ss-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 28px; background: #fff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky; top: 0; z-index: 50;
        }
        .ss-topbar-title { font-size: 16px; font-weight: 600; color: #0f172a; }
        .ss-topbar-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        .ss-scan-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #22c55e; color: #fff; border: none;
            border-radius: 8px; padding: 9px 18px;
            font-size: 13px; font-weight: 600; font-family: 'Sora', sans-serif;
            cursor: pointer; transition: background 0.15s, transform 0.1s;
        }
        .ss-scan-btn:hover { background: #16a34a; }
        .ss-scan-btn:active { transform: scale(0.97); }
        .ss-scan-btn:disabled { background: #86efac; cursor: not-allowed; }
        .ss-scan-btn svg { width: 14px; height: 14px; }

        /* ── Content ── */
        .ss-content { padding: 24px 28px; flex: 1; display: flex; flex-direction: column; gap: 20px; }

        /* ── Progress Card ── */
        .ss-progress-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px;
        }
        .ss-progress-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;
        }
        .ss-status-row { display: flex; align-items: center; gap: 7px; }
        .ss-dot { width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; transition: background 0.3s; }
        .ss-dot.scanning { background: #22c55e; animation: pulse 1.2s infinite; }
        .ss-dot.done     { background: #22c55e; animation: none; }
        .ss-dot.error    { background: #ef4444; animation: none; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.25; } }
        .ss-status-label { font-size: 13px; color: #64748b; }
        .ss-step-label   { font-size: 11px; color: #94a3b8; margin-top: 4px; font-family: 'JetBrains Mono', monospace; }
        .ss-pct          { font-size: 14px; font-weight: 500; color: #22c55e; font-family: 'JetBrains Mono', monospace; }
        .ss-progress-track { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
        .ss-progress-fill  { height: 100%; background: #22c55e; border-radius: 999px; width: 0%; transition: width 0.3s ease; }

        /* ── Metrics ── */
        .ss-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .ss-metric {
            background: #fff; border: 1px solid #e2e8f0;
            border-top: 3px solid transparent; border-radius: 12px; padding: 18px 20px;
        }
        .ss-metric.bump { animation: bump 0.25s ease; }
        @keyframes bump { 0%,100% { transform: scale(1); } 50% { transform: scale(1.04); } }
        .ss-metric.high   { border-top-color: #ef4444; }
        .ss-metric.medium { border-top-color: #f59e0b; }
        .ss-metric.low    { border-top-color: #22c55e; }
        .ss-metric-val   { font-size: 32px; font-weight: 600; line-height: 1; font-family: 'JetBrains Mono', monospace; color: #0f172a; }
        .ss-metric-label { font-size: 11px; font-weight: 500; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.07em; margin-top: 6px; }
        .ss-metric-badge { display: inline-block; font-size: 10px; font-weight: 500; padding: 3px 8px; border-radius: 999px; margin-top: 8px; }
        .ss-metric.high   .ss-metric-badge { background: #fef2f2; color: #ef4444; }
        .ss-metric.medium .ss-metric-badge { background: #fffbeb; color: #d97706; }
        .ss-metric.low    .ss-metric-badge { background: #f0fdf4; color: #16a34a; }

        /* ── Bottom Grid ── */
        .ss-bottom-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: start; }

        .ss-panel {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 18px 20px; display: flex; flex-direction: column; gap: 10px;
        }

        .ss-panel-header {
            display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        }

        .ss-panel-title {
            font-size: 11px; font-weight: 500; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.07em;
        }

        .ss-finding-count {
            font-size: 11px; font-weight: 600; color: #0f172a;
            background: #f1f5f9; padding: 2px 8px; border-radius: 999px;
        }

        /* ── Findings scrollable list ── */
        .ss-findings-scroll {
            overflow-y: auto;
            max-height: 320px;       /* fixed height → scrolls internally */
            padding-right: 4px;      /* space for scrollbar */
        }

        /* thin custom scrollbar */
        .ss-findings-scroll::-webkit-scrollbar,
        .ss-log-box::-webkit-scrollbar { width: 4px; }
        .ss-findings-scroll::-webkit-scrollbar-track,
        .ss-log-box::-webkit-scrollbar-track { background: transparent; }
        .ss-findings-scroll::-webkit-scrollbar-thumb,
        .ss-log-box::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
        .ss-findings-scroll::-webkit-scrollbar-thumb:hover,
        .ss-log-box::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .ss-finding {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid #f1f5f9;
            font-size: 12px; animation: fadeIn 0.25s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(3px); } to { opacity: 1; transform: none; } }
        .ss-finding:last-child { border-bottom: none; }

        .ss-sev {
            flex-shrink: 0; padding: 2px 7px; border-radius: 999px;
            font-size: 10px; font-weight: 500; font-family: 'JetBrains Mono', monospace;
            margin-top: 1px;
        }
        .ss-sev.H { background: #fef2f2; color: #ef4444; }
        .ss-sev.M { background: #fffbeb; color: #d97706; }
        .ss-sev.L { background: #f0fdf4; color: #16a34a; }

        .ss-finding-type { font-size: 12px; font-weight: 500; color: #0f172a; }
        .ss-finding-file { font-size: 11px; color: #94a3b8; font-family: 'JetBrains Mono', monospace; margin-top: 2px; word-break: break-all; }
        .ss-finding-line { font-size: 10px; color: #cbd5e1; font-family: 'JetBrains Mono', monospace; margin-top: 1px; }

        .ss-empty { font-size: 12px; color: #cbd5e1; padding: 28px 0; text-align: center; }

        /* ── Severity filter tabs ── */
        .ss-filter-tabs { display: flex; gap: 6px; flex-shrink: 0; }
        .ss-filter-tab {
            font-size: 10px; font-weight: 500; padding: 3px 9px;
            border-radius: 999px; border: 1px solid #e2e8f0;
            background: transparent; color: #94a3b8; cursor: pointer;
            font-family: 'Sora', sans-serif; transition: all 0.15s;
        }
        .ss-filter-tab:hover { border-color: #cbd5e1; color: #64748b; }
        .ss-filter-tab.active-all    { background: #0f172a; color: #fff; border-color: #0f172a; }
        .ss-filter-tab.active-high   { background: #fef2f2; color: #ef4444; border-color: #fecaca; }
        .ss-filter-tab.active-medium { background: #fffbeb; color: #d97706; border-color: #fde68a; }
        .ss-filter-tab.active-low    { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }

        /* ── Log box ── */
        .ss-log-box {
            background: #0f1623; border-radius: 8px; padding: 12px 14px;
            overflow-y: auto; max-height: 320px;
            font-family: 'JetBrains Mono', monospace; font-size: 11px;
        }
        .ss-log-line { display: flex; gap: 10px; padding: 2px 0; line-height: 1.6; animation: fadeIn 0.2s ease; }
        .ss-log-time { color: #475569; flex-shrink: 0; }
        .ss-log-msg      { color: #94a3b8; word-break: break-all; }
        .ss-log-msg.ok   { color: #22c55e; }
        .ss-log-msg.warn { color: #f59e0b; }
        .ss-log-msg.err  { color: #ef4444; }

        /* ── Chart ── */
        .ss-chart-wrap { position: relative; height: 200px; }
    </style>
</head>
<body>

{{-- ══ Sidebar ══ --}}
<div class="ss-sidebar">
    <div class="ss-logo">
        <div class="ss-logo-icon">
            <svg viewBox="0 0 16 16" fill="none"><path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/><circle cx="8" cy="8" r="2.5" fill="white"/></svg>
        </div>
        <span class="ss-logo-text">SecureScan</span>
    </div>
    <nav class="ss-nav">
        <div class="ss-nav-label">Main</div>
        <a href="{{route('securescan.index')}}" class="{{ request()->routeIs('securescan.index') ? 'active' : '' }}">
            <svg viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".4"/><rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".4"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".4"/></svg>
            Dashboard
        </a>
    </nav>
</div>

{{-- ══ Main ══ --}}
<div class="ss-main">
    <div class="ss-topbar">
        <div>
            <div class="ss-topbar-title">Dashboard</div>
            <div class="ss-topbar-sub" id="lastScanLabel">
                @if($lastScan ?? null)
                    Last scan: {{ $lastScan->created_at->diffForHumans() }}
                @else
                    No scans run yet
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            @auth
                <span style="font-size:13px;color:#64748b;">{{ Auth::user()->name }}</span>
            @endauth
            <button class="ss-scan-btn" id="scanBtn" onclick="startScan()">
                <svg viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 7l1.5 1.5L9 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Start Scan
            </button>
        </div>
    </div>

    <div class="ss-content">

        @if(session('success'))
            <div style="padding:12px 16px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:10px;font-size:13px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Progress --}}
        <div class="ss-progress-card">
            <div class="ss-progress-header">
                <div>
                    <div class="ss-status-row">
                        <span class="ss-dot" id="statusDot"></span>
                        <span class="ss-status-label" id="statusText">Idle — ready to scan</span>
                    </div>
                    <div class="ss-step-label" id="stepLabel"></div>
                </div>
                <span class="ss-pct" id="pctLabel">0%</span>
            </div>
            <div class="ss-progress-track">
                <div class="ss-progress-fill" id="progressFill"></div>
            </div>
        </div>

        {{-- Metrics --}}
        <div class="ss-metrics">
            <div class="ss-metric high" id="metricHigh">
                <div class="ss-metric-val" id="highCount">0</div>
                <div class="ss-metric-label">High Severity</div>
                <span class="ss-metric-badge">Critical</span>
            </div>
            <div class="ss-metric medium" id="metricMedium">
                <div class="ss-metric-val" id="mediumCount">0</div>
                <div class="ss-metric-label">Medium Severity</div>
                <span class="ss-metric-badge">Warning</span>
            </div>
            <div class="ss-metric low" id="metricLow">
                <div class="ss-metric-val" id="lowCount">0</div>
                <div class="ss-metric-label">Low Severity</div>
                <span class="ss-metric-badge">Info</span>
            </div>
        </div>

        {{-- Bottom grid --}}
        <div class="ss-bottom-grid">

            {{-- Findings panel --}}
            <div class="ss-panel">
                <div class="ss-panel-header">
                    <div class="ss-panel-title">Findings</div>
                    <span class="ss-finding-count" id="findingCount" style="display:none;"></span>
                </div>

                {{-- Severity filter tabs --}}
                <div class="ss-filter-tabs" id="filterTabs" style="display:none;">
                    <button class="ss-filter-tab active-all" onclick="filterFindings('all')">All</button>
                    <button class="ss-filter-tab" onclick="filterFindings('HIGH')">High</button>
                    <button class="ss-filter-tab" onclick="filterFindings('MEDIUM')">Med</button>
                    <button class="ss-filter-tab" onclick="filterFindings('LOW')">Low</button>
                </div>

                <div class="ss-findings-scroll" id="findingsScroll">
                    <div class="ss-empty" id="findingsEmpty">No findings yet — run a scan</div>
                </div>
            </div>

            {{-- Logs panel --}}
            <div class="ss-panel">
                <div class="ss-panel-header">
                    <div class="ss-panel-title">Live Logs</div>
                </div>
                <div class="ss-log-box" id="logBox">
                    <div class="ss-log-line">
                        <span class="ss-log-time">--:--</span>
                        <span class="ss-log-msg">Awaiting scan start...</span>
                    </div>
                </div>
            </div>

            {{-- Chart panel --}}
            <div class="ss-panel">
                <div class="ss-panel-header">
                    <div class="ss-panel-title">Severity Breakdown</div>
                </div>
                <div class="ss-chart-wrap">
                    <canvas id="severityChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// ── Chart ─────────────────────────────────────────────────────────────────
const chart = new Chart(document.getElementById('severityChart'), {
    type: 'doughnut',
    data: {
        labels: ['High', 'Medium', 'Low'],
        datasets: [{
            data: [0, 0, 0],
            backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { family: 'Sora', size: 11 }, color: '#94a3b8', boxWidth: 10, padding: 14 }
            }
        }
    }
});

// ── State ─────────────────────────────────────────────────────────────────
let evtSource        = null;
let counts           = { high: 0, medium: 0, low: 0 };
let allFindings      = [];      // full list for filter
let activeFilter     = 'all';

// ── Start scan ────────────────────────────────────────────────────────────
function startScan() {
    if (evtSource) { evtSource.close(); evtSource = null; }

    counts       = { high: 0, medium: 0, low: 0 };
    allFindings  = [];
    activeFilter = 'all';

    setProgress(0);
    setStatus('scanning', 'Initialising scanner...');
    document.getElementById('findingsScroll').innerHTML = '<div class="ss-empty">Scanning for issues...</div>';
    document.getElementById('findingCount').style.display = 'none';
    document.getElementById('filterTabs').style.display   = 'none';
    document.getElementById('logBox').innerHTML = '';
    document.getElementById('highCount').textContent   = '0';
    document.getElementById('mediumCount').textContent = '0';
    document.getElementById('lowCount').textContent    = '0';
    chart.data.datasets[0].data = [0, 0, 0];
    chart.update();
    setBtnState('scanning');

    evtSource = new EventSource('{{ route("securescan.stream") }}');

    evtSource.addEventListener('start', (e) => {
        const d = JSON.parse(e.data);
        setStatus('scanning', `Scanning — ${d.total} steps total`);
    });

    evtSource.addEventListener('progress', (e) => {
        const d = JSON.parse(e.data);
        setProgress(d.progress);
        document.getElementById('stepLabel').textContent = `Step ${d.step} / ${d.total}`;
        appendLog(d.log, classForLog(d.log));
    });

    evtSource.addEventListener('finding', (e) => {
        const f = JSON.parse(e.data);
        allFindings.push(f);

        const sev = (f.severity || '').toUpperCase();
        if (sev === 'HIGH')   { counts.high++;   bumpMetric('metricHigh',   'highCount',   counts.high);   }
        if (sev === 'MEDIUM') { counts.medium++; bumpMetric('metricMedium', 'mediumCount', counts.medium); }
        if (sev === 'LOW')    { counts.low++;    bumpMetric('metricLow',    'lowCount',    counts.low);    }

        chart.data.datasets[0].data = [counts.high, counts.medium, counts.low];
        chart.update();

        // Only render if passes active filter
        if (activeFilter === 'all' || sev === activeFilter) {
            renderFindingRow(f);
        }

        updateFindingCount();
    });

    evtSource.addEventListener('done', (e) => {
        const d = JSON.parse(e.data);
        setProgress(100);
        setStatus('done', `Scan complete — ${d.total} finding${d.total !== 1 ? 's' : ''} found`);
        document.getElementById('lastScanLabel').textContent = 'Last scan: just now';
        document.getElementById('stepLabel').textContent = '';
        appendLog('Scan finished.', 'ok');
        setBtnState('idle');
        if (allFindings.length > 0) {
            document.getElementById('filterTabs').style.display = 'flex';
        }
        evtSource.close();
        evtSource = null;
    });

    evtSource.onerror = () => {
        setStatus('error', 'Connection lost — check server logs');
        appendLog('SSE connection error.', 'err');
        setBtnState('idle');
        evtSource.close();
        evtSource = null;
    };
}

// ── Filter findings ───────────────────────────────────────────────────────
function filterFindings(sev) {
    activeFilter = sev;

    // Update tab active states
    document.querySelectorAll('.ss-filter-tab').forEach((btn, i) => {
        btn.className = 'ss-filter-tab';
        if (i === 0 && sev === 'all')    btn.classList.add('active-all');
        if (i === 1 && sev === 'HIGH')   btn.classList.add('active-high');
        if (i === 2 && sev === 'MEDIUM') btn.classList.add('active-medium');
        if (i === 3 && sev === 'LOW')    btn.classList.add('active-low');
    });

    // Re-render filtered list
    const scroll = document.getElementById('findingsScroll');
    const filtered = sev === 'all'
        ? allFindings
        : allFindings.filter(f => (f.severity || '').toUpperCase() === sev);

    if (filtered.length === 0) {
        scroll.innerHTML = `<div class="ss-empty">No ${sev === 'all' ? '' : sev.toLowerCase() + ' '}findings</div>`;
        return;
    }

    scroll.innerHTML = '';
    filtered.forEach(f => renderFindingRow(f, false));  // false = no auto-scroll
}

// ── Render one finding row ────────────────────────────────────────────────
function renderFindingRow(f, autoScroll = true) {
    const scroll = document.getElementById('findingsScroll');

    // Remove empty placeholder if present
    const empty = scroll.querySelector('.ss-empty');
    if (empty) empty.remove();

    const sev = (f.severity || '').toUpperCase();
    const cls = sev === 'HIGH' ? 'H' : sev === 'MEDIUM' ? 'M' : 'L';

    const row = document.createElement('div');
    row.className   = 'ss-finding';
    row.dataset.sev = sev;
    row.innerHTML   = `
        <span class="ss-sev ${cls}">${cls}</span>
        <div>
            <div class="ss-finding-type">${esc(f.type)}</div>
            <div class="ss-finding-file">${esc(f.file)}</div>
            ${f.line ? `<div class="ss-finding-line">Line ${esc(String(f.line))}</div>` : ''}
        </div>`;
    scroll.appendChild(row);

    if (autoScroll) scroll.scrollTop = scroll.scrollHeight;
}

function updateFindingCount() {
    const el = document.getElementById('findingCount');
    el.textContent     = allFindings.length;
    el.style.display   = 'inline-block';
}

// ── Helpers ───────────────────────────────────────────────────────────────
function setProgress(pct) {
    const p = Math.min(100, Math.round(pct * 10) / 10);
    document.getElementById('progressFill').style.width = p + '%';
    document.getElementById('pctLabel').textContent     = p + '%';
}

function setStatus(state, label) {
    document.getElementById('statusDot').className   = 'ss-dot' + (state !== 'idle' ? ' ' + state : '');
    document.getElementById('statusText').textContent = label;
}

function setBtnState(state) {
    const btn = document.getElementById('scanBtn');
    btn.disabled = state === 'scanning';
    btn.innerHTML = state === 'scanning'
        ? `<svg viewBox="0 0 14 14" fill="none" width="14" height="14"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 2"/></svg> Scanning...`
        : `<svg viewBox="0 0 14 14" fill="none" width="14" height="14"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 7l1.5 1.5L9 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Start Scan`;
}

function bumpMetric(cardId, countId, val) {
    document.getElementById(countId).textContent = val;
    const card = document.getElementById(cardId);
    card.classList.remove('bump');
    void card.offsetWidth;
    card.classList.add('bump');
}

function appendLog(msg, cls = '') {
    const box = document.getElementById('logBox');
    const now  = new Date();
    const ts   = pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    const line = document.createElement('div');
    line.className = 'ss-log-line';
    line.innerHTML = `<span class="ss-log-time">${ts}</span><span class="ss-log-msg ${cls}">${esc(msg)}</span>`;
    box.appendChild(line);
    const lines = box.querySelectorAll('.ss-log-line');
    if (lines.length > 100) lines[0].remove();
    box.scrollTop = box.scrollHeight;
}

function classForLog(line) {
    const l = (line || '').toUpperCase();
    if (l.includes('ERROR') || l.includes('CRITICAL')) return 'err';
    if (l.includes('WARN'))                            return 'warn';
    if (l.includes('SUCCESS') || l.includes('COMPLETE') || l.includes('DONE')) return 'ok';
    return '';
}

function esc(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function pad(n) { return String(n).padStart(2, '0'); }
</script>

</body>
</html>