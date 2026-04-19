<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CRM Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        :root {
            --bg:          #09090F;
            --surface:     #101018;
            --surface-2:   #161622;
            --border:      #1E1E2E;
            --border-h:    #2A2A3E;
            --accent:      #C8A951;
            --accent-dim:  rgba(200,169,81,.10);
            --accent-glow: rgba(200,169,81,.22);
            --accent-l:    #E4C57A;
            --t1:          #ECEDF5;
            --t2:          #7E8499;
            --t3:          #3D4058;
            --ok:          #34D399;
            --warn:        #FBBF24;
            --err:         #F87171;
            --info:        #60A5FA;
            --violet:      #A78BFA;
            --sidebar-w:   220px;
            --sidebar-c:   60px;
            --sans:        'Outfit', sans-serif;
            --mono:        'Space Mono', monospace;
            --r:           10px;
            --r-sm:        7px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--t1);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse 90% 50% at 50% -10%, rgba(200,169,81,.06) 0%, transparent 60%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='28'%3E%3Ccircle cx='1' cy='1' r='.7' fill='%231E1E2E'/%3E%3C/svg%3E");
        }
        [x-cloak] { display: none !important; }

        /* ── AUTH ─────────────────────────────── */
        .auth-wrap { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:1.5rem; }
        .auth-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 2.5rem 2.25rem;
            width: 100%; max-width: 400px; position: relative; overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,.5);
        }
        .auth-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, var(--accent), var(--accent-l), transparent);
        }
        .auth-brand { display:flex; align-items:center; gap:.625rem; margin-bottom:2rem; }
        .auth-brand-icon {
            width:34px; height:34px; background:var(--accent-dim);
            border:1px solid rgba(200,169,81,.28); border-radius:8px;
            display:flex; align-items:center; justify-content:center;
        }
        .auth-brand-name { font-size:1rem; font-weight:700; color:var(--t1); }
        .auth-brand-name em { font-style:normal; color:var(--accent); }
        .auth-card h1 { font-size:1.5rem; font-weight:700; letter-spacing:-.025em; margin-bottom:.3rem; }
        .auth-card p.subtitle { font-size:.875rem; color:var(--t2); margin-bottom:1.75rem; }
        .tabs {
            display:flex; gap:3px; margin-bottom:1.75rem;
            background:var(--bg); padding:3px; border-radius:var(--r-sm); border:1px solid var(--border);
        }
        .tabs button {
            flex:1; padding:.45rem; border-radius:5px; border:none; background:transparent;
            color:var(--t2); cursor:pointer; font-size:.8125rem; font-family:var(--sans); font-weight:500; transition:all .18s;
        }
        .tabs button.active { background:var(--surface-2); color:var(--t1); font-weight:600; box-shadow:0 1px 4px rgba(0,0,0,.35); }
        .form-group { margin-bottom:.9rem; }
        label { display:block; font-size:.6875rem; font-weight:600; color:var(--t2); margin-bottom:.35rem; text-transform:uppercase; letter-spacing:.07em; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, textarea {
            width:100%; padding:.65rem .875rem; background:var(--bg); border:1px solid var(--border);
            border-radius:var(--r-sm); color:var(--t1); font-size:.9375rem; font-family:var(--sans); outline:none;
            transition: border-color .18s, box-shadow .18s;
        }
        select { cursor:pointer; }
        textarea { resize:vertical; min-height:80px; }
        input:focus, select:focus, textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-glow); }
        input::placeholder, textarea::placeholder { color:var(--t3); }
        .btn-primary {
            width:100%; padding:.725rem; background:var(--accent); border:none; border-radius:var(--r-sm);
            color:#0C0A06; font-size:.9375rem; font-weight:700; font-family:var(--sans);
            cursor:pointer; transition:background .18s, transform .15s, box-shadow .18s; margin-top:.625rem;
        }
        .btn-primary:hover { background:var(--accent-l); transform:translateY(-1px); box-shadow:0 6px 20px var(--accent-glow); }
        .btn-primary:active { transform:translateY(0); box-shadow:none; }
        .btn-primary:disabled { background:#5A4A1E; color:#3A3010; cursor:not-allowed; transform:none; box-shadow:none; }
        .error-msg {
            background:rgba(248,113,113,.07); border:1px solid rgba(248,113,113,.25);
            color:#FCA5A5; padding:.65rem .9rem; border-radius:var(--r-sm); font-size:.8125rem; margin-bottom:1rem;
        }

        /* ── APP SHELL ────────────────────────── */
        .app-shell { display:flex; height:100vh; overflow:hidden; }

        /* ── SIDEBAR ──────────────────────────── */
        .sidebar {
            width: var(--sidebar-w); min-width: var(--sidebar-w);
            background: var(--surface); border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            transition: width .25s ease, min-width .25s ease;
            overflow: hidden; position: relative; z-index: 30;
        }
        .sidebar.collapsed { width: var(--sidebar-c); min-width: var(--sidebar-c); }

        .sidebar-top {
            display:flex; align-items:center; justify-content:space-between;
            padding: 0 1rem; height: 58px; border-bottom: 1px solid var(--border); flex-shrink:0;
        }
        .sidebar-logo {
            display:flex; align-items:center; gap:.5rem;
            font-size:.9375rem; font-weight:700; color:var(--t1); white-space:nowrap;
            opacity:1; transition:opacity .2s;
        }
        .sidebar-logo em { font-style:normal; color:var(--accent); }
        .sidebar.collapsed .sidebar-logo { opacity:0; pointer-events:none; max-width:0; overflow:hidden; gap:0; }
        .sidebar.collapsed .sidebar-top  { justify-content:center; padding:0; }
        .sidebar-logo-icon {
            width:26px; height:26px; background:var(--accent-dim);
            border:1px solid rgba(200,169,81,.25); border-radius:6px;
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .btn-toggle {
            background:transparent; border:1px solid var(--border); border-radius:var(--r-sm);
            color:var(--t2); cursor:pointer; padding:.3rem; display:flex; align-items:center; justify-content:center;
            transition:all .18s; flex-shrink:0;
        }
        .btn-toggle:hover { border-color:var(--border-h); color:var(--t1); }

        .sidebar-nav { flex:1; padding:.75rem .5rem; overflow-y:auto; }
        .nav-label-section {
            font-size:.55rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
            color:var(--t3); padding:.5rem .75rem .4rem; white-space:nowrap;
            overflow:hidden; transition:opacity .2s;
        }
        .sidebar.collapsed .nav-label-section { opacity:0; }
        .nav-item {
            display:flex; align-items:center; gap:.75rem;
            padding:.55rem .75rem; border-radius:var(--r-sm); border:none;
            background:transparent; color:var(--t2); cursor:pointer; font-family:var(--sans);
            font-size:.875rem; font-weight:500; width:100%; text-align:left;
            transition:all .18s; white-space:nowrap;
        }
        .nav-item:hover { background:var(--surface-2); color:var(--t1); }
        .nav-item.active { background:var(--accent-dim); color:var(--accent); border:1px solid rgba(200,169,81,.2); }
        .nav-item svg { flex-shrink:0; }
        .nav-item-label { transition:opacity .2s; }
        .sidebar.collapsed .nav-item-label { opacity:0; width:0; overflow:hidden; }

        .sidebar-footer {
            border-top:1px solid var(--border); padding:.75rem .5rem; flex-shrink:0;
        }
        .user-pill-sidebar {
            display:flex; align-items:center; gap:.625rem;
            padding:.5rem .75rem; border-radius:var(--r-sm); overflow:hidden;
        }
        .user-avatar {
            width:28px; height:28px; background:var(--accent-dim);
            border:1px solid rgba(200,169,81,.25); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:.65rem; font-weight:800; color:var(--accent); font-family:var(--mono); flex-shrink:0;
        }
        .user-info { overflow:hidden; transition:opacity .2s; }
        .sidebar.collapsed .user-info { opacity:0; }
        .user-name { font-size:.8125rem; font-weight:600; color:var(--t1); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .btn-logout-small {
            margin-top:.25rem; display:block; background:transparent; border:none;
            color:var(--t3); font-size:.6875rem; font-family:var(--sans); cursor:pointer;
            text-align:left; padding:0; transition:color .15s;
        }
        .btn-logout-small:hover { color:var(--err); }

        /* ── MAIN AREA ────────────────────────── */
        .main-area { flex:1; display:flex; flex-direction:column; overflow:hidden; }

        .dash-header {
            background:rgba(16,16,24,.9); border-bottom:1px solid var(--border);
            padding:0 1.5rem; height:58px; display:flex; align-items:center;
            justify-content:space-between; flex-shrink:0;
            backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
        }
        .page-title { font-size:.9375rem; font-weight:600; color:var(--t1); }
        .page-subtitle { font-size:.75rem; color:var(--t2); margin-top:.1rem; }

        .content-area { flex:1; overflow-y:auto; padding:1.5rem; }

        /* ── SECTION HEADER ───────────────────── */
        .section-header { display:flex; align-items:center; gap:.625rem; margin-bottom:1rem; }
        .section-title { font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:var(--t3); white-space:nowrap; }
        .section-line { flex:1; height:1px; background:var(--border); }

        /* ── STATS GRID ───────────────────────── */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem; }
        .stat-card {
            background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
            padding:1.25rem 1.375rem; position:relative; overflow:hidden;
            transition:border-color .2s, transform .2s, box-shadow .2s; cursor:default;
        }
        .stat-card:hover { border-color:var(--border-h); transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,0,0,.3); }
        .stat-card::after {
            content:''; position:absolute; bottom:0; left:20%; right:20%; height:1px;
            background:linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity:0; transition:opacity .3s, left .3s, right .3s;
        }
        .stat-card:hover::after { opacity:.7; left:0; right:0; }
        .stat-icon { width:32px; height:32px; border-radius:7px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; }
        .ic-clients    { background:rgba(200,169,81,.1); }
        .ic-contacts   { background:rgba(96,165,250,.1); }
        .ic-notes      { background:rgba(52,211,153,.1); }
        .ic-activities { background:rgba(167,139,250,.1); }
        .stat-card .label { font-size:.6rem; font-weight:600; text-transform:uppercase; letter-spacing:.09em; color:var(--t3); margin-bottom:.35rem; }
        .stat-card .value { font-family:var(--mono); font-size:2.25rem; font-weight:700; color:var(--t1); line-height:1; }
        .stat-card .sub { font-size:.75rem; color:var(--t3); margin-top:.45rem; display:flex; gap:.5rem; }
        .stat-card .sub .ok   { color:var(--ok); }
        .stat-card .sub .warn { color:var(--warn); }

        /* ── PANEL ────────────────────────────── */
        .panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); padding:1.25rem 1.375rem; margin-bottom:2rem; }
        .status-row { display:flex; align-items:center; gap:.875rem; padding:.55rem 0; }
        .status-row + .status-row { border-top:1px solid var(--border); }
        .s-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .sd-lead     { background:var(--info);   box-shadow:0 0 6px rgba(96,165,250,.5); }
        .sd-active   { background:var(--ok);     box-shadow:0 0 6px rgba(52,211,153,.5); }
        .sd-inactive { background:var(--warn);   box-shadow:0 0 6px rgba(251,191,36,.5); }
        .sd-churned  { background:var(--err);    box-shadow:0 0 6px rgba(248,113,113,.5); }
        .status-name { width:68px; font-size:.8125rem; color:var(--t2); text-transform:capitalize; font-weight:500; }
        .status-bar-wrap { flex:1; background:var(--bg); border-radius:99px; height:5px; overflow:hidden; }
        .status-bar { height:100%; border-radius:99px; transition:width .9s cubic-bezier(.22,1,.36,1); }
        .bar-lead     { background:linear-gradient(90deg,#2563EB,#60A5FA); }
        .bar-active   { background:linear-gradient(90deg,#059669,#34D399); }
        .bar-inactive { background:linear-gradient(90deg,#B45309,#FBBF24); }
        .bar-churned  { background:linear-gradient(90deg,#DC2626,#F87171); }
        .status-count { width:26px; text-align:right; font-family:var(--mono); font-size:.75rem; font-weight:700; color:var(--t2); }

        /* ── ACTIVITY LIST ────────────────────── */
        .activity-list { list-style:none; }
        .activity-item { display:flex; align-items:center; gap:.875rem; padding:.75rem 0; }
        .activity-item + .activity-item { border-top:1px solid var(--border); }
        .activity-badge { padding:.2rem .55rem; border-radius:5px; font-size:.5625rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; flex-shrink:0; border:1px solid transparent; }
        .badge-call    { background:rgba(96,165,250,.1);  color:#60A5FA; border-color:rgba(96,165,250,.2); }
        .badge-email   { background:rgba(52,211,153,.1);  color:#34D399; border-color:rgba(52,211,153,.2); }
        .badge-meeting { background:rgba(167,139,250,.1); color:#A78BFA; border-color:rgba(167,139,250,.2); }
        .badge-task    { background:rgba(200,169,81,.1);  color:#C8A951; border-color:rgba(200,169,81,.2); }
        .activity-body { flex:1; min-width:0; }
        .activity-title { font-size:.875rem; color:var(--t1); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .activity-meta { font-size:.75rem; color:var(--t3); margin-top:.15rem; }
        .act-status-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .dot-done    { background:var(--ok);  box-shadow:0 0 5px rgba(52,211,153,.5); }
        .dot-pending { background:var(--warn); box-shadow:0 0 5px rgba(251,191,36,.5); }
        .empty-state { text-align:center; padding:2.5rem 0; color:var(--t3); font-size:.875rem; }

        /* ── TOOLBAR ──────────────────────────── */
        .toolbar { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; flex-wrap:wrap; }
        .toolbar-left { display:flex; gap:.5rem; flex:1; flex-wrap:wrap; }
        .search-input { flex:1; min-width:160px; max-width:280px; }
        .filter-select { width:140px; }
        .btn-add {
            display:flex; align-items:center; gap:.4rem;
            padding:.55rem 1rem; background:var(--accent); border:none; border-radius:var(--r-sm);
            color:#0C0A06; font-size:.875rem; font-weight:700; font-family:var(--sans);
            cursor:pointer; white-space:nowrap; transition:background .18s, box-shadow .18s;
        }
        .btn-add:hover { background:var(--accent-l); box-shadow:0 4px 14px var(--accent-glow); }

        /* ── TABLE ────────────────────────────── */
        .table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table thead { background:var(--surface-2); }
        .data-table th {
            padding:.65rem 1rem; text-align:left; font-size:.6rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.09em; color:var(--t3); white-space:nowrap;
        }
        .data-table td { padding:.75rem 1rem; font-size:.875rem; border-top:1px solid var(--border); }
        .data-table tbody tr { transition:background .15s; cursor:pointer; }
        .data-table tbody tr:hover { background:var(--surface-2); }
        .row-actions { display:flex; gap:.375rem; }
        .btn-icon {
            background:transparent; border:1px solid var(--border); border-radius:var(--r-sm);
            color:var(--t2); cursor:pointer; padding:.3rem .45rem; display:flex;
            align-items:center; justify-content:center; transition:all .15s;
        }
        .btn-icon:hover { border-color:var(--border-h); color:var(--t1); }
        .btn-icon.del:hover { border-color:rgba(248,113,113,.4); color:var(--err); }
        .btn-icon.restore:hover { border-color:rgba(52,211,153,.4); color:var(--ok); }
        .cell-muted { color:var(--t2); font-size:.8125rem; }

        /* Status badges */
        .sbadge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .55rem; border-radius:5px; font-size:.6875rem; font-weight:600; }
        .sbadge-lead     { background:rgba(96,165,250,.1);  color:#60A5FA; }
        .sbadge-active   { background:rgba(52,211,153,.1);  color:#34D399; }
        .sbadge-inactive { background:rgba(251,191,36,.1);  color:#FBBF24; }
        .sbadge-churned  { background:rgba(248,113,113,.1); color:#F87171; }

        /* ── PAGINATION ───────────────────────── */
        .pagination { display:flex; align-items:center; justify-content:space-between; padding:.875rem 1rem; border-top:1px solid var(--border); }
        .pagination-info { font-size:.8125rem; color:var(--t2); }
        .pagination-pages { display:flex; gap:.25rem; }
        .page-btn {
            background:transparent; border:1px solid var(--border); border-radius:var(--r-sm);
            color:var(--t2); cursor:pointer; padding:.3rem .625rem; font-size:.8125rem;
            font-family:var(--sans); transition:all .15s;
        }
        .page-btn:hover:not(:disabled) { border-color:var(--border-h); color:var(--t1); }
        .page-btn.active { background:var(--accent-dim); border-color:rgba(200,169,81,.3); color:var(--accent); font-weight:600; }
        .page-btn:disabled { opacity:.35; cursor:not-allowed; }

        /* ── MODAL ────────────────────────────── */
        .modal-backdrop {
            position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:100;
            display:flex; align-items:center; justify-content:center; padding:1rem;
            backdrop-filter:blur(4px);
        }
        .modal {
            background:var(--surface); border:1px solid var(--border); border-radius:14px;
            width:100%; max-width:480px; max-height:90vh; overflow:hidden;
            display:flex; flex-direction:column; box-shadow:0 32px 80px rgba(0,0,0,.6);
            position:relative;
        }
        .modal::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg, transparent, var(--accent), transparent);
        }
        .modal-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-title { font-size:1rem; font-weight:700; color:var(--t1); }
        .modal-close { background:transparent; border:1px solid var(--border); border-radius:var(--r-sm); color:var(--t2); cursor:pointer; padding:.3rem; display:flex; align-items:center; transition:all .15s; }
        .modal-close:hover { border-color:var(--border-h); color:var(--t1); }
        .modal-body { padding:1.5rem; overflow-y:auto; }
        .modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; gap:.625rem; justify-content:flex-end; }
        .btn-cancel { padding:.6rem 1.25rem; background:transparent; border:1px solid var(--border); border-radius:var(--r-sm); color:var(--t2); font-size:.875rem; font-family:var(--sans); cursor:pointer; transition:all .15s; }
        .btn-cancel:hover { border-color:var(--border-h); color:var(--t1); }
        .btn-save { padding:.6rem 1.5rem; background:var(--accent); border:none; border-radius:var(--r-sm); color:#0C0A06; font-size:.875rem; font-weight:700; font-family:var(--sans); cursor:pointer; transition:all .15s; }
        .btn-save:hover { background:var(--accent-l); box-shadow:0 4px 14px var(--accent-glow); }
        .btn-save:disabled { background:#5A4A1E; color:#3A3010; cursor:not-allowed; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }

        /* ── DRAWER ───────────────────────────── */
        .drawer-overlay { position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:40; cursor:pointer; }
        .drawer {
            position:fixed; top:0; right:0; bottom:0; width:420px; max-width:100vw;
            background:var(--surface); border-left:1px solid var(--border);
            z-index:50; display:flex; flex-direction:column;
            transform:translateX(100%); transition:transform .28s cubic-bezier(.4,0,.2,1);
            box-shadow:-16px 0 48px rgba(0,0,0,.4);
        }
        .drawer.open { transform:translateX(0); }
        .drawer-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; }
        .drawer-name { font-size:1.125rem; font-weight:700; color:var(--t1); }
        .drawer-company { font-size:.8125rem; color:var(--t2); margin-top:.15rem; }
        .drawer-tabs { display:flex; gap:0; border-bottom:1px solid var(--border); flex-shrink:0; }
        .drawer-tab { flex:1; padding:.65rem; background:transparent; border:none; color:var(--t2); font-size:.8125rem; font-weight:500; font-family:var(--sans); cursor:pointer; border-bottom:2px solid transparent; transition:all .18s; margin-bottom:-1px; }
        .drawer-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
        .drawer-tab:hover:not(.active) { color:var(--t1); }
        .drawer-body { flex:1; overflow-y:auto; padding:1.25rem 1.5rem; }
        .drawer-add-btn { display:flex; align-items:center; gap:.4rem; padding:.45rem .875rem; background:var(--accent-dim); border:1px solid rgba(200,169,81,.2); border-radius:var(--r-sm); color:var(--accent); font-size:.8125rem; font-weight:600; font-family:var(--sans); cursor:pointer; margin-bottom:1rem; transition:all .15s; }
        .drawer-add-btn:hover { background:rgba(200,169,81,.18); }
        .drawer-item { padding:.75rem 0; border-bottom:1px solid var(--border); }
        .drawer-item:last-child { border-bottom:none; }
        .drawer-item-name { font-size:.875rem; font-weight:500; color:var(--t1); }
        .drawer-item-meta { font-size:.75rem; color:var(--t3); margin-top:.2rem; }
        .drawer-info-row { display:flex; padding:.5rem 0; border-bottom:1px solid var(--border); }
        .drawer-info-row:last-child { border-bottom:none; }
        .drawer-info-label { width:90px; font-size:.75rem; font-weight:600; color:var(--t3); text-transform:uppercase; letter-spacing:.06em; flex-shrink:0; padding-top:.05rem; }
        .drawer-info-value { font-size:.875rem; color:var(--t1); }

        /* ── TOAST ────────────────────────────── */
        .toast-stack { position:fixed; bottom:1.5rem; right:1.5rem; z-index:200; display:flex; flex-direction:column; gap:.5rem; pointer-events:none; }
        .toast {
            display:flex; align-items:center; gap:.625rem;
            padding:.75rem 1rem; border-radius:var(--r); border:1px solid var(--border);
            background:var(--surface-2); color:var(--t1); font-size:.875rem; font-weight:500;
            box-shadow:0 8px 24px rgba(0,0,0,.4); min-width:220px; max-width:340px;
            animation:toastIn .25s ease;
        }
        .toast-ok  { border-color:rgba(52,211,153,.3); }
        .toast-err { border-color:rgba(248,113,113,.3); }
        .toast-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .toast-ok  .toast-dot { background:var(--ok);  box-shadow:0 0 6px rgba(52,211,153,.6); }
        .toast-err .toast-dot { background:var(--err); box-shadow:0 0 6px rgba(248,113,113,.6); }
        @keyframes toastIn { from { opacity:0; transform:translateX(12px); } to { opacity:1; transform:none; } }

        /* ── LOADING ──────────────────────────── */
        .loading { display:flex; flex-direction:column; align-items:center; gap:.75rem; padding:3rem 0; color:var(--t3); font-size:.875rem; }
        .spinner { width:22px; height:22px; border:2px solid var(--border); border-top-color:var(--accent); border-radius:50%; animation:spin .65s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        .anim { animation:fadeUp .4s ease both; }
        .d1 { animation-delay:.04s; } .d2 { animation-delay:.09s; } .d3 { animation-delay:.14s; }
        .d4 { animation-delay:.19s; } .d5 { animation-delay:.24s; } .d6 { animation-delay:.29s; }

        /* ── RESPONSIVE ───────────────────────── */
        @media (max-width:768px) {
            .sidebar { position:fixed; top:0; left:0; bottom:0; z-index:30; transform:translateX(-100%); }
            .sidebar.mobile-open { transform:translateX(0); }
            .form-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body x-data="crm()" x-init="init()">

    {{-- ── AUTH ──────────────────────────────────── --}}
    <div class="auth-wrap" x-show="!token" x-cloak>
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-brand-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <span class="auth-brand-name">CRM <em>API</em></span>
            </div>
            <h1>Welcome back</h1>
            <p class="subtitle">Sign in to your workspace</p>
            <div class="tabs">
                <button :class="{ active: authTab === 'login' }"    @click="authTab = 'login'">Login</button>
                <button :class="{ active: authTab === 'register' }" @click="authTab = 'register'">Register</button>
            </div>
            <div x-show="errorMsg" class="error-msg" x-text="errorMsg"></div>
            <form x-show="authTab === 'login'" @submit.prevent="login()">
                <div class="form-group"><label>Email</label><input type="email" x-model="form.email" placeholder="demo@crm.test" required /></div>
                <div class="form-group"><label>Password</label><input type="password" x-model="form.password" placeholder="••••••••" required /></div>
                <button class="btn-primary" type="submit" :disabled="loading"><span x-show="!loading">Sign in</span><span x-show="loading">Signing in…</span></button>
            </form>
            <form x-show="authTab === 'register'" @submit.prevent="register()">
                <div class="form-group"><label>Name</label><input type="text" x-model="form.name" placeholder="Your name" required /></div>
                <div class="form-group"><label>Email</label><input type="email" x-model="form.email" placeholder="you@example.com" required /></div>
                <div class="form-group"><label>Password</label><input type="password" x-model="form.password" placeholder="Min 8 characters" required /></div>
                <div class="form-group"><label>Confirm Password</label><input type="password" x-model="form.password_confirmation" placeholder="Repeat password" required /></div>
                <button class="btn-primary" type="submit" :disabled="loading"><span x-show="!loading">Create account</span><span x-show="loading">Creating…</span></button>
            </form>
        </div>
    </div>

    {{-- ── APP SHELL ─────────────────────────────── --}}
    <div class="app-shell" x-show="token" x-cloak>

        {{-- SIDEBAR --}}
        <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    </div>
                    CRM <em style="margin-left:.25rem">API</em>
                </div>
                <button class="btn-toggle" @click="sidebarCollapsed = !sidebarCollapsed" title="Toggle sidebar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-label-section">Menu</div>
                <button class="nav-item" :class="{ active: section === 'dashboard' }" @click="navigate('dashboard')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span class="nav-item-label">Dashboard</span>
                </button>
                <button class="nav-item" :class="{ active: section === 'clients' }" @click="navigate('clients')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="nav-item-label">Clients</span>
                </button>
                <button class="nav-item" :class="{ active: section === 'contacts' }" @click="navigate('contacts')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="nav-item-label">Contacts</span>
                </button>
                <button class="nav-item" :class="{ active: section === 'notes' }" @click="navigate('notes')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span class="nav-item-label">Notes</span>
                </button>
                <button class="nav-item" :class="{ active: section === 'activities' }" @click="navigate('activities')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span class="nav-item-label">Activities</span>
                </button>
            </nav>

            <div class="sidebar-footer">
                <div class="user-pill-sidebar">
                    <div class="user-avatar" x-text="user?.name?.charAt(0)?.toUpperCase() || '?'"></div>
                    <div class="user-info">
                        <div class="user-name" x-text="user?.name"></div>
                        <button class="btn-logout-small" @click="logout()">Sign out</button>
                    </div>
                </div>
            </div>
        </aside>

        {{-- MAIN AREA --}}
        <div class="main-area">

            <header class="dash-header">
                <div>
                    <div class="page-title" x-text="pageTitles[section]?.title"></div>
                    <div class="page-subtitle" x-text="pageTitles[section]?.sub"></div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span style="font-size:.8125rem;color:var(--t3);"
                          x-text="section==='clients' && clientsMeta ? clientsMeta.total+' total'
                                 : section==='notes' && notesMeta ? notesMeta.total+' total'
                                 : section==='activities' && activitiesMeta ? activitiesMeta.total+' total'
                                 : ''"></span>
                </div>
            </header>

            <div class="content-area">

                {{-- ── DASHBOARD ────────────────────── --}}
                <section x-show="section === 'dashboard'">
                    <div x-show="dashLoading" class="loading"><div class="spinner"></div><span>Loading…</span></div>
                    <template x-if="!dashLoading && stats">
                        <div>
                            <div class="section-header anim"><span class="section-title">Overview</span><div class="section-line"></div></div>
                            <div class="stats-grid">
                                <div class="stat-card anim d1">
                                    <div class="stat-icon ic-clients"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                                    <div class="label">Clients</div><div class="value" x-text="stats.clients.total"></div>
                                </div>
                                <div class="stat-card anim d2">
                                    <div class="stat-icon ic-contacts"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#60A5FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                                    <div class="label">Contacts</div><div class="value" x-text="stats.contacts.total"></div>
                                </div>
                                <div class="stat-card anim d3">
                                    <div class="stat-icon ic-notes"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                                    <div class="label">Notes</div><div class="value" x-text="stats.notes.total"></div>
                                </div>
                                <div class="stat-card anim d4">
                                    <div class="stat-icon ic-activities"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#A78BFA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                                    <div class="label">Activities</div><div class="value" x-text="stats.activities.total"></div>
                                    <div class="sub"><span class="ok" x-text="stats.activities.completed + ' done'"></span><span style="color:var(--border)">·</span><span class="warn" x-text="stats.activities.pending + ' pending'"></span></div>
                                </div>
                            </div>
                            <div class="section-header anim d5"><span class="section-title">Clients by status</span><div class="section-line"></div></div>
                            <div class="panel anim d5">
                                <template x-for="[status, count] in Object.entries(stats.clients.by_status)" :key="status">
                                    <div class="status-row">
                                        <span class="s-dot" :class="'sd-' + status"></span>
                                        <span class="status-name" x-text="status"></span>
                                        <div class="status-bar-wrap"><div class="status-bar" :class="'bar-' + status" :style="'width:' + Math.round((count / stats.clients.total) * 100) + '%'"></div></div>
                                        <span class="status-count" x-text="count"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="section-header anim d6"><span class="section-title">Recent activities</span><div class="section-line"></div></div>
                            <div class="panel anim d6">
                                <ul class="activity-list">
                                    <template x-for="act in stats.recent_activities" :key="act.id">
                                        <li class="activity-item">
                                            <span class="activity-badge" :class="'badge-' + (act.type || 'task')" x-text="act.type || 'task'"></span>
                                            <div class="activity-body">
                                                <div class="activity-title" x-text="act.title"></div>
                                                <div class="activity-meta"><span x-show="act.due_at" x-text="'Due ' + new Date(act.due_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})"></span></div>
                                            </div>
                                            <span class="act-status-dot" :class="act.completed_at ? 'dot-done' : 'dot-pending'"></span>
                                        </li>
                                    </template>
                                    <li x-show="!stats.recent_activities?.length" class="empty-state">No activities yet.</li>
                                </ul>
                            </div>
                        </div>
                    </template>
                </section>

                {{-- ── CLIENTS ──────────────────────── --}}
                <section x-show="section === 'clients'">
                    <div class="toolbar">
                        <div class="toolbar-left">
                            <input class="search-input" type="text" placeholder="Search by name…" x-model="clientsFilter.search" @input.debounce.350ms="clientsPage = 1; loadClients()" />
                            <select class="filter-select" x-model="clientsFilter.status" @change="clientsPage = 1; loadClients()">
                                <option value="">All statuses</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="churned">Churned</option>
                            </select>
                        </div>
                        <button class="btn-add" @click="openModal('create','client',{})">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Client
                        </button>
                    </div>

                    <div x-show="clientsLoading" class="loading"><div class="spinner"></div><span>Loading clients…</span></div>

                    <template x-if="!clientsLoading">
                        <div>
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Company</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="c in clients" :key="c.id">
                                            <tr @click.stop="openDrawer(c)">
                                                <td>
                                                    <div style="font-weight:500;" x-text="c.name"></div>
                                                    <div class="cell-muted" x-show="c.phone" x-text="c.phone"></div>
                                                </td>
                                                <td class="cell-muted" x-text="c.company || '—'"></td>
                                                <td class="cell-muted" x-text="c.email || '—'"></td>
                                                <td>
                                                    <span class="sbadge" :class="'sbadge-' + (c.status || 'lead')" x-text="c.status || 'lead'"></span>
                                                </td>
                                                <td>
                                                    <div class="row-actions" @click.stop>
                                                        <button class="btn-icon" title="Edit" @click="openModal('edit','client', {...c})">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        </button>
                                                        <button class="btn-icon del" title="Delete" @click="deleteClient(c.id)">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="clients.length === 0">
                                            <td colspan="5" class="empty-state" style="text-align:center;padding:2rem;">No clients found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="pagination" x-show="clientsMeta && clientsMeta.last_page > 1">
                                    <span class="pagination-info" x-text="'Page ' + clientsMeta?.current_page + ' of ' + clientsMeta?.last_page"></span>
                                    <div class="pagination-pages">
                                        <button class="page-btn" :disabled="clientsPage <= 1" @click="clientsPage--; loadClients()">←</button>
                                        <template x-for="p in clientsMeta?.last_page" :key="p">
                                            <button class="page-btn" :class="{ active: p === clientsPage }" @click="clientsPage = p; loadClients()" x-text="p"></button>
                                        </template>
                                        <button class="page-btn" :disabled="clientsPage >= clientsMeta?.last_page" @click="clientsPage++; loadClients()">→</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>

                {{-- ── CONTACTS ────────────────────────── --}}
                <section x-show="section === 'contacts'">
                    <div class="toolbar">
                        <div class="toolbar-left">
                            <input class="search-input" type="text" placeholder="Search by name, email…" x-model="contactsFilter.search" @input.debounce.350ms="contactsPage = 1; loadContacts()" />
                        </div>
                        <button class="btn-add" @click="openModalGlobal('contact')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Contact
                        </button>
                    </div>
                    <div x-show="contactsLoading" class="loading"><div class="spinner"></div><span>Loading contacts…</span></div>
                    <template x-if="!contactsLoading">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Client</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="c in contacts" :key="c.id">
                                        <tr>
                                            <td style="font-weight:500;" x-text="c.name"></td>
                                            <td class="cell-muted" x-text="c.position || '—'"></td>
                                            <td class="cell-muted" x-text="c.email || '—'"></td>
                                            <td class="cell-muted" x-text="c.phone || '—'"></td>
                                            <td class="cell-muted" x-text="c.client?.name || '—'"></td>
                                            <td>
                                                <div class="row-actions">
                                                    <button class="btn-icon" title="Edit" @click="openModal('edit','contact',{...c})">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" title="Delete" @click="deleteContact(c.id)">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="contacts.length === 0">
                                        <td colspan="6" class="empty-state" style="text-align:center;padding:2rem;">No contacts yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="pagination" x-show="contactsMeta && contactsMeta.last_page > 1">
                                <span class="pagination-info" x-text="'Page ' + contactsMeta?.current_page + ' of ' + contactsMeta?.last_page"></span>
                                <div class="pagination-pages">
                                    <button class="page-btn" :disabled="contactsPage <= 1" @click="contactsPage--; loadContacts()">←</button>
                                    <template x-for="p in contactsMeta?.last_page" :key="p">
                                        <button class="page-btn" :class="{ active: p === contactsPage }" @click="contactsPage = p; loadContacts()" x-text="p"></button>
                                    </template>
                                    <button class="page-btn" :disabled="contactsPage >= contactsMeta?.last_page" @click="contactsPage++; loadContacts()">→</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>

                {{-- ── NOTES ───────────────────────────── --}}
                <section x-show="section === 'notes'">
                    <div class="toolbar">
                        <div class="toolbar-left">
                            <input class="search-input" type="text" placeholder="Search notes…" x-model="notesFilter.search" @input.debounce.350ms="notesPage = 1; loadNotes()" />
                        </div>
                        <button class="btn-add" @click="openModalGlobal('note')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Note
                        </button>
                    </div>
                    <div x-show="notesLoading" class="loading"><div class="spinner"></div><span>Loading notes…</span></div>
                    <template x-if="!notesLoading">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Note</th>
                                        <th>Client</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="n in notes" :key="n.id">
                                        <tr>
                                            <td style="max-width:380px;">
                                                <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="n.body"></div>
                                            </td>
                                            <td class="cell-muted" x-text="n.client?.name || '—'"></td>
                                            <td class="cell-muted" x-text="new Date(n.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})"></td>
                                            <td>
                                                <div class="row-actions">
                                                    <button class="btn-icon" title="Edit" @click="openModal('edit','note',{...n})">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" title="Delete" @click="deleteNoteGlobal(n.id)">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="notes.length === 0">
                                        <td colspan="4" class="empty-state" style="text-align:center;padding:2rem;">No notes yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="pagination" x-show="notesMeta && notesMeta.last_page > 1">
                                <span class="pagination-info" x-text="'Page ' + notesMeta?.current_page + ' of ' + notesMeta?.last_page"></span>
                                <div class="pagination-pages">
                                    <button class="page-btn" :disabled="notesPage <= 1" @click="notesPage--; loadNotes()">←</button>
                                    <template x-for="p in notesMeta?.last_page" :key="p">
                                        <button class="page-btn" :class="{ active: p === notesPage }" @click="notesPage = p; loadNotes()" x-text="p"></button>
                                    </template>
                                    <button class="page-btn" :disabled="notesPage >= notesMeta?.last_page" @click="notesPage++; loadNotes()">→</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>

                {{-- ── ACTIVITIES ───────────────────────── --}}
                <section x-show="section === 'activities'">
                    <div class="toolbar">
                        <div class="toolbar-left">
                            <input class="search-input" type="text" placeholder="Search activities…" x-model="activitiesFilter.search" @input.debounce.350ms="activitiesPage = 1; loadActivities()" />
                            <select class="filter-select" x-model="activitiesFilter.type" @change="activitiesPage = 1; loadActivities()">
                                <option value="">All types</option>
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="task">Task</option>
                            </select>
                            <select class="filter-select" x-model="activitiesFilter.status" @change="activitiesPage = 1; loadActivities()">
                                <option value="">All statuses</option>
                                <option value="pending">Pending</option>
                                <option value="done">Completed</option>
                            </select>
                        </div>
                        <button class="btn-add" @click="openModalGlobal('activity')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Activity
                        </button>
                    </div>
                    <div x-show="activitiesLoading" class="loading"><div class="spinner"></div><span>Loading activities…</span></div>
                    <template x-if="!activitiesLoading">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="a in activities" :key="a.id">
                                        <tr>
                                            <td>
                                                <div style="font-weight:500;" x-text="a.title"></div>
                                                <div class="cell-muted" x-show="a.body" x-text="a.body?.substring(0,60) + (a.body?.length > 60 ? '…' : '')"></div>
                                            </td>
                                            <td><span class="activity-badge" :class="'badge-' + (a.type || 'task')" x-text="a.type || 'task'"></span></td>
                                            <td class="cell-muted" x-text="a.client?.name || '—'"></td>
                                            <td class="cell-muted" x-text="a.due_at ? new Date(a.due_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'"></td>
                                            <td>
                                                <button @click.stop="toggleActivity(a)"
                                                        :title="a.completed_at ? 'Mark as pending' : 'Mark as done'"
                                                        style="display:inline-flex;align-items:center;gap:.4rem;background:transparent;border:none;cursor:pointer;padding:.2rem .1rem;">
                                                    <span x-show="a.completed_at" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;color:var(--ok);">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Done
                                                    </span>
                                                    <span x-show="!a.completed_at" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;color:var(--warn);">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/></svg> Pending
                                                    </span>
                                                </button>
                                            </td>
                                            <td>
                                                <div class="row-actions">
                                                    <button class="btn-icon" title="Edit" @click="openModal('edit','activity',{...a})">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" title="Delete" @click="deleteActivity(a.id)">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="activities.length === 0">
                                        <td colspan="6" class="empty-state" style="text-align:center;padding:2rem;">No activities found.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="pagination" x-show="activitiesMeta && activitiesMeta.last_page > 1">
                                <span class="pagination-info" x-text="'Page ' + activitiesMeta?.current_page + ' of ' + activitiesMeta?.last_page"></span>
                                <div class="pagination-pages">
                                    <button class="page-btn" :disabled="activitiesPage <= 1" @click="activitiesPage--; loadActivities()">←</button>
                                    <template x-for="p in activitiesMeta?.last_page" :key="p">
                                        <button class="page-btn" :class="{ active: p === activitiesPage }" @click="activitiesPage = p; loadActivities()" x-text="p"></button>
                                    </template>
                                    <button class="page-btn" :disabled="activitiesPage >= activitiesMeta?.last_page" @click="activitiesPage++; loadActivities()">→</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>

            </div>{{-- /content-area --}}
        </div>{{-- /main-area --}}

        {{-- ── DRAWER ──────────────────────────────── --}}
        <div class="drawer-overlay" x-show="drawerClient" @click="drawerClient = null" x-cloak></div>
        <div class="drawer" :class="{ open: drawerClient }" x-cloak>
            <template x-if="drawerClient">
                <div style="display:contents">
                    <div class="drawer-header">
                        <div>
                            <div class="drawer-name" x-text="drawerClient.name"></div>
                            <div class="drawer-company" x-text="drawerClient.company || drawerClient.email || ''"></div>
                        </div>
                        <button class="modal-close" @click="drawerClient = null">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div class="drawer-tabs">
                        <button class="drawer-tab" :class="{ active: drawerTab === 'info' }"       @click="drawerTab = 'info'">Info</button>
                        <button class="drawer-tab" :class="{ active: drawerTab === 'contacts' }"   @click="drawerTab = 'contacts'">Contacts</button>
                        <button class="drawer-tab" :class="{ active: drawerTab === 'notes' }"      @click="drawerTab = 'notes'">Notes</button>
                        <button class="drawer-tab" :class="{ active: drawerTab === 'activities' }" @click="drawerTab = 'activities'">Activities</button>
                    </div>
                    <div class="drawer-body">

                        {{-- Info tab --}}
                        <div x-show="drawerTab === 'info'">
                            <div class="drawer-info-row"><span class="drawer-info-label">Status</span><span><span class="sbadge" :class="'sbadge-' + (drawerClient.status || 'lead')" x-text="drawerClient.status || 'lead'"></span></span></div>
                            <div class="drawer-info-row"><span class="drawer-info-label">Email</span><span class="drawer-info-value" x-text="drawerClient.email || '—'"></span></div>
                            <div class="drawer-info-row"><span class="drawer-info-label">Phone</span><span class="drawer-info-value" x-text="drawerClient.phone || '—'"></span></div>
                            <div class="drawer-info-row"><span class="drawer-info-label">Company</span><span class="drawer-info-value" x-text="drawerClient.company || '—'"></span></div>
                            <div class="drawer-info-row"><span class="drawer-info-label">Created</span><span class="drawer-info-value" x-text="new Date(drawerClient.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})"></span></div>
                        </div>

                        {{-- Contacts tab --}}
                        <div x-show="drawerTab === 'contacts'">
                            <button class="drawer-add-btn" @click="openModal('create','contact',{ client_id: drawerClient.id })">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Contact
                            </button>
                            <div x-show="drawerLoading" class="loading" style="padding:1rem 0"><div class="spinner"></div></div>
                            <template x-if="!drawerLoading">
                                <div>
                                    <template x-for="c in drawerContacts" :key="c.id">
                                        <div class="drawer-item">
                                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                                <div class="drawer-item-name" x-text="c.name"></div>
                                                <div style="display:flex;gap:.3rem;">
                                                    <button class="btn-icon" @click="openModal('edit','contact',{...c, client_id: drawerClient.id})">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" @click="deleteContact(c.id)">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="drawer-item-meta" x-text="[c.position, c.email, c.phone].filter(Boolean).join(' · ')"></div>
                                        </div>
                                    </template>
                                    <div x-show="drawerContacts.length === 0" class="empty-state">No contacts yet.</div>
                                </div>
                            </template>
                        </div>

                        {{-- Notes tab --}}
                        <div x-show="drawerTab === 'notes'">
                            <button class="drawer-add-btn" @click="openModal('create','note',{ client_id: drawerClient.id })">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Note
                            </button>
                            <div x-show="drawerLoading" class="loading" style="padding:1rem 0"><div class="spinner"></div></div>
                            <template x-if="!drawerLoading">
                                <div>
                                    <template x-for="n in drawerNotes" :key="n.id">
                                        <div class="drawer-item">
                                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                                                <div class="drawer-item-name" style="font-weight:400;line-height:1.5;" x-text="n.body"></div>
                                                <div style="display:flex;gap:.3rem;flex-shrink:0;">
                                                    <button class="btn-icon" @click="openModal('edit','note',{...n})">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" @click="deleteNote(n.id)">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="drawer-item-meta" x-text="new Date(n.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})"></div>
                                        </div>
                                    </template>
                                    <div x-show="drawerNotes.length === 0" class="empty-state">No notes yet.</div>
                                </div>
                            </template>
                        </div>

                        {{-- Activities tab --}}
                        <div x-show="drawerTab === 'activities'">
                            <button class="drawer-add-btn" @click="openModal('create','activity',{ client_id: drawerClient.id })">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Activity
                            </button>
                            <div x-show="drawerLoading" class="loading" style="padding:1rem 0"><div class="spinner"></div></div>
                            <template x-if="!drawerLoading">
                                <div>
                                    <template x-for="a in drawerActivities" :key="a.id">
                                        <div class="drawer-item">
                                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                                <div style="display:flex;align-items:center;gap:.5rem;min-width:0;">
                                                    <span class="activity-badge" :class="'badge-'+(a.type||'task')" x-text="a.type||'task'"></span>
                                                    <div class="drawer-item-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="a.title"></div>
                                                </div>
                                                <div style="display:flex;gap:.3rem;flex-shrink:0;">
                                                    <button class="btn-icon" @click="openModal('edit','activity',{...a, client_id: drawerClient.id})">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                    <button class="btn-icon del" @click="deleteActivity(a.id)">
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="drawer-item-meta" style="display:flex;align-items:center;gap:.625rem;margin-top:.3rem;">
                                                <span x-show="a.due_at" x-text="'Due ' + new Date(a.due_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})"></span>
                                                <button @click="toggleActivity(a)"
                                                        :title="a.completed_at ? 'Mark as pending' : 'Mark as done'"
                                                        style="display:inline-flex;align-items:center;gap:.3rem;background:transparent;border:1px solid;border-radius:5px;cursor:pointer;padding:.15rem .45rem;font-size:.7rem;font-family:var(--sans);"
                                                        :style="a.completed_at ? 'color:var(--ok);border-color:rgba(52,211,153,.3);' : 'color:var(--warn);border-color:rgba(251,191,36,.3);'">
                                                    <svg x-show="a.completed_at" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <svg x-show="!a.completed_at" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/></svg>
                                                    <span x-text="a.completed_at ? 'Done' : 'Pending'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="drawerActivities.length === 0" class="empty-state">No activities yet.</div>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </template>
        </div>

        {{-- ── MODAL ───────────────────────────────── --}}
        <div class="modal-backdrop" x-show="modal.open" @click.self="closeModal()" x-cloak>
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title" x-text="modal.title"></div>
                    <button class="modal-close" @click="closeModal()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- Client form --}}
                    <template x-if="modal.entity === 'client'">
                        <div>
                            <div class="form-group"><label>Name *</label><input type="text" x-model="modal.data.name" placeholder="Acme Corp" required /></div>
                            <div class="form-row">
                                <div class="form-group"><label>Email</label><input type="email" x-model="modal.data.email" placeholder="info@acme.com" /></div>
                                <div class="form-group"><label>Phone</label><input type="tel" x-model="modal.data.phone" placeholder="+1 555 0100" /></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Company</label><input type="text" x-model="modal.data.company" placeholder="Acme Corp" /></div>
                                <div class="form-group"><label>Status</label>
                                    <select x-model="modal.data.status">
                                        <option value="lead">Lead</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="churned">Churned</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Contact form --}}
                    <template x-if="modal.entity === 'contact'">
                        <div>
                            <template x-if="modal.data._needClient">
                                <div class="form-group">
                                    <label>Client *</label>
                                    <select x-model="modal.data.client_id">
                                        <option value="">— select a client —</option>
                                        <template x-for="c in clientsAll" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <div class="form-group"><label>Name *</label><input type="text" x-model="modal.data.name" placeholder="Jane Doe" required /></div>
                            <div class="form-row">
                                <div class="form-group"><label>Email</label><input type="email" x-model="modal.data.email" placeholder="jane@acme.com" /></div>
                                <div class="form-group"><label>Phone</label><input type="tel" x-model="modal.data.phone" placeholder="+1 555 0101" /></div>
                            </div>
                            <div class="form-group"><label>Position</label><input type="text" x-model="modal.data.position" placeholder="CTO" /></div>
                        </div>
                    </template>

                    {{-- Note form --}}
                    <template x-if="modal.entity === 'note'">
                        <div>
                            <template x-if="modal.data._needClient">
                                <div class="form-group">
                                    <label>Client *</label>
                                    <select x-model="modal.data.client_id">
                                        <option value="">— select a client —</option>
                                        <template x-for="c in clientsAll" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <div class="form-group"><label>Note *</label><textarea x-model="modal.data.body" placeholder="Write your note here…" rows="4"></textarea></div>
                        </div>
                    </template>

                    {{-- Activity form --}}
                    <template x-if="modal.entity === 'activity'">
                        <div>
                            <template x-if="modal.data._needClient">
                                <div class="form-group">
                                    <label>Client *</label>
                                    <select x-model="modal.data.client_id">
                                        <option value="">— select a client —</option>
                                        <template x-for="c in clientsAll" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <div class="form-group"><label>Title *</label><input type="text" x-model="modal.data.title" placeholder="Follow-up call" /></div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select x-model="modal.data.type">
                                        <option value="task">Task</option>
                                        <option value="call">Call</option>
                                        <option value="email">Email</option>
                                        <option value="meeting">Meeting</option>
                                    </select>
                                </div>
                                <div class="form-group"><label>Due date</label><input type="datetime-local" x-model="modal.data.due_at" /></div>
                            </div>
                            <div class="form-group"><label>Notes</label><textarea x-model="modal.data.body" placeholder="Optional details…" rows="3"></textarea></div>
                            <div class="form-group" x-show="modal.mode === 'edit'">
                                <label>Completed at</label>
                                <input type="datetime-local" x-model="modal.data.completed_at" />
                            </div>
                        </div>
                    </template>

                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal()">Cancel</button>
                    <button class="btn-save" @click="saveEntity()" :disabled="modal.saving">
                        <span x-show="!modal.saving" x-text="modal.mode === 'create' ? 'Create' : 'Save changes'"></span>
                        <span x-show="modal.saving">Saving…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── TOASTS ──────────────────────────────── --}}
        <div class="toast-stack">
            <template x-for="t in toasts" :key="t.id">
                <div class="toast" :class="t.type === 'ok' ? 'toast-ok' : 'toast-err'">
                    <span class="toast-dot"></span>
                    <span x-text="t.msg"></span>
                </div>
            </template>
        </div>

    </div>{{-- /app-shell --}}

    <script>
    function crm() {
        return {
            /* ── Auth ── */
            token:    localStorage.getItem('crm_token') || '',
            user:     JSON.parse(localStorage.getItem('crm_user') || 'null'),
            authTab:  'login',
            form:     { name: '', email: '', password: '', password_confirmation: '' },
            errorMsg: '',
            loading:  false,

            /* ── Navigation ── */
            section:          'dashboard',
            sidebarCollapsed: false,
            pageTitles: {
                dashboard:  { title: 'Dashboard',   sub: 'Overview of your CRM' },
                clients:    { title: 'Clients',     sub: 'Manage your client list' },
                contacts:   { title: 'Contacts',    sub: 'All contacts across clients' },
                notes:      { title: 'Notes',       sub: 'All notes across clients' },
                activities: { title: 'Activities',  sub: 'All activities across clients' },
            },

            /* ── Dashboard ── */
            stats:       null,
            dashLoading: false,

            /* ── Clients ── */
            clients:       [],
            clientsMeta:   null,
            clientsPage:   1,
            clientsFilter: { status: '', search: '' },
            clientsLoading: false,

            /* ── Contacts ── */
            contacts:        [],
            contactsMeta:    null,
            contactsPage:    1,
            contactsFilter:  { search: '' },
            contactsLoading: false,

            /* ── Notes ── */
            notes:        [],
            notesMeta:    null,
            notesPage:    1,
            notesFilter:  { search: '' },
            notesLoading: false,

            /* ── Activities ── */
            activities:        [],
            activitiesMeta:    null,
            activitiesPage:    1,
            activitiesFilter:  { type: '', status: '', search: '' },
            activitiesLoading: false,

            /* ── Drawer ── */
            drawerClient:     null,
            drawerTab:        'info',
            drawerContacts:   [],
            drawerNotes:      [],
            drawerActivities: [],
            drawerLoading:    false,

            /* ── Modal ── */
            modal:      { open: false, mode: 'create', entity: 'client', title: '', data: {}, saving: false },
            clientsAll: [],

            /* ── Toasts ── */
            toasts: [],

            /* ───────────────────────────────── */
            init() {
                axios.interceptors.response.use(null, (e) => {
                    if (e.response?.status === 401) this.logout();
                    return Promise.reject(e);
                });
                if (this.token) {
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.token;
                    this.loadDashboard();
                }
            },

            /* ── Auth methods ── */
            async login() {
                this.errorMsg = ''; this.loading = true;
                try {
                    const { data } = await axios.post('/api/auth/login', { email: this.form.email, password: this.form.password });
                    this.setSession(data.token, data.user); this.loadDashboard();
                } catch (e) { this.errorMsg = e.response?.data?.message || 'Login failed.'; }
                finally { this.loading = false; }
            },

            async register() {
                this.errorMsg = ''; this.loading = true;
                try {
                    const { data } = await axios.post('/api/auth/register', { name: this.form.name, email: this.form.email, password: this.form.password, password_confirmation: this.form.password_confirmation });
                    this.setSession(data.token, data.user); this.loadDashboard();
                } catch (e) {
                    const errors = e.response?.data?.errors;
                    this.errorMsg = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Registration failed.');
                } finally { this.loading = false; }
            },

            setSession(token, user) {
                this.token = token; this.user = user;
                localStorage.setItem('crm_token', token);
                localStorage.setItem('crm_user', JSON.stringify(user));
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
            },

            async logout() {
                try { await axios.post('/api/auth/logout'); } catch (_) {}
                this.token = ''; this.user = null; this.stats = null;
                localStorage.removeItem('crm_token'); localStorage.removeItem('crm_user');
                delete axios.defaults.headers.common['Authorization'];
            },

            /* ── Navigation ── */
            navigate(sec) {
                const prev = this.section;
                this.section = sec;
                if (prev !== sec) {
                    if (prev === 'clients')    { this.clientsFilter = { status: '', search: '' }; this.clientsPage = 1; }
                    if (prev === 'contacts')   { this.contactsFilter = { search: '' }; this.contactsPage = 1; }
                    if (prev === 'notes')      { this.notesFilter = { search: '' }; this.notesPage = 1; }
                    if (prev === 'activities') { this.activitiesFilter = { type: '', status: '', search: '' }; this.activitiesPage = 1; }
                }
                if (sec === 'dashboard')  this.loadDashboard();
                if (sec === 'clients')    this.loadClients();
                if (sec === 'contacts')   this.loadContacts();
                if (sec === 'notes')      this.loadNotes();
                if (sec === 'activities') this.loadActivities();
            },

            /* ── Dashboard ── */
            async loadDashboard() {
                this.dashLoading = true;
                try { const { data } = await axios.get('/api/dashboard'); this.stats = data; }
                catch (e) { if (e.response?.status === 401) this.logout(); }
                finally { this.dashLoading = false; }
            },

            /* ── Clients ── */
            async loadClients() {
                this.clientsLoading = true;
                try {
                    const params = { page: this.clientsPage };
                    if (this.clientsFilter.status) params.status = this.clientsFilter.status;
                    if (this.clientsFilter.search) params.search = this.clientsFilter.search;
                    const { data } = await axios.get('/api/clients', { params });
                    this.clients     = data.data;
                    this.clientsMeta = data.meta;
                } catch (e) { this.toast('Failed to load clients', 'err'); }
                finally { this.clientsLoading = false; }
            },

            async saveClient() {
                const d    = this.modal.data;
                const body = { name: d.name, email: d.email || null, phone: d.phone || null, company: d.company || null, status: d.status || 'lead' };
                if (this.modal.mode === 'create') {
                    const { data } = await axios.post('/api/clients', body);
                    this.toast('Client created', 'ok');
                } else {
                    await axios.put(`/api/clients/${d.id}`, body);
                    this.toast('Client updated', 'ok');
                }
                this.clientsAll = [];
                this.closeModal(); this.loadClients(); this.loadDashboard();
            },

            async deleteClient(id) {
                if (!confirm('Delete this client?')) return;
                try {
                    await axios.delete(`/api/clients/${id}`);
                    this.clientsAll = [];
                    this.toast('Client deleted', 'ok'); this.loadClients(); this.loadDashboard();
                } catch (e) { this.toast('Failed to delete client', 'err'); }
            },

            /* ── Contacts ── */
            async loadContacts() {
                this.contactsLoading = true;
                try {
                    const params = { page: this.contactsPage };
                    if (this.contactsFilter.search) params.search = this.contactsFilter.search;
                    const { data } = await axios.get('/api/contacts', { params });
                    this.contacts     = data.data;
                    this.contactsMeta = data.meta;
                } catch (e) { this.toast('Failed to load contacts', 'err'); }
                finally { this.contactsLoading = false; }
            },

            async saveContact() {
                const d    = this.modal.data;
                const body = { name: d.name, email: d.email || null, phone: d.phone || null, position: d.position || null };
                if (this.modal.mode === 'create') {
                    await axios.post(`/api/clients/${d.client_id}/contacts`, body);
                    this.toast('Contact created', 'ok');
                } else {
                    await axios.put(`/api/contacts/${d.id}`, body);
                    this.toast('Contact updated', 'ok');
                }
                this.closeModal();
                if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                if (this.section === 'contacts') this.loadContacts();
            },

            async deleteContact(id) {
                if (!confirm('Delete this contact?')) return;
                try {
                    await axios.delete(`/api/contacts/${id}`);
                    this.toast('Contact deleted', 'ok');
                    if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                    if (this.section === 'contacts') this.loadContacts();
                } catch (e) { this.toast('Failed to delete contact', 'err'); }
            },

            /* ── Notes ── */
            async loadNotes() {
                this.notesLoading = true;
                try {
                    const params = { page: this.notesPage };
                    if (this.notesFilter.search) params.search = this.notesFilter.search;
                    const { data } = await axios.get('/api/notes', { params });
                    this.notes     = data.data;
                    this.notesMeta = data.meta;
                } catch (e) { this.toast('Failed to load notes', 'err'); }
                finally { this.notesLoading = false; }
            },

            async saveNote() {
                const d = this.modal.data;
                if (this.modal.mode === 'create') {
                    await axios.post(`/api/clients/${d.client_id}/notes`, { body: d.body });
                    this.toast('Note created', 'ok');
                } else {
                    await axios.put(`/api/notes/${d.id}`, { body: d.body });
                    this.toast('Note updated', 'ok');
                }
                this.closeModal();
                if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                if (this.section === 'notes') this.loadNotes();
            },

            async deleteNote(id) {
                if (!confirm('Delete this note?')) return;
                try {
                    await axios.delete(`/api/notes/${id}`);
                    this.toast('Note deleted', 'ok');
                    if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                    if (this.section === 'notes') this.loadNotes();
                } catch (e) { this.toast('Failed to delete note', 'err'); }
            },

            async deleteNoteGlobal(id) { await this.deleteNote(id); },

            /* ── Activities ── */
            async loadActivities() {
                this.activitiesLoading = true;
                try {
                    const params = { page: this.activitiesPage };
                    if (this.activitiesFilter.type)   params.type   = this.activitiesFilter.type;
                    if (this.activitiesFilter.status) params.status = this.activitiesFilter.status;
                    if (this.activitiesFilter.search) params.search = this.activitiesFilter.search;
                    const { data } = await axios.get('/api/activities', { params });
                    this.activities     = data.data;
                    this.activitiesMeta = data.meta;
                } catch (e) { this.toast('Failed to load activities', 'err'); }
                finally { this.activitiesLoading = false; }
            },

            async toggleActivity(activity) {
                const completed_at = activity.completed_at ? null : new Date().toISOString();
                try {
                    await axios.put(`/api/activities/${activity.id}`, { completed_at });
                    activity.completed_at = completed_at;
                    this.toast(completed_at ? 'Marked as done' : 'Marked as pending', 'ok');
                    this.loadDashboard();
                    if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                } catch (e) { this.toast('Failed to update activity', 'err'); }
            },

            async saveActivity() {
                const d = this.modal.data;
                const body = {
                    title:        d.title,
                    type:         d.type || 'task',
                    body:         d.body || null,
                    due_at:       d.due_at || null,
                    completed_at: d.completed_at || null,
                };
                if (this.modal.mode === 'create') {
                    await axios.post(`/api/clients/${d.client_id}/activities`, body);
                    this.toast('Activity created', 'ok');
                } else {
                    await axios.put(`/api/activities/${d.id}`, body);
                    this.toast('Activity updated', 'ok');
                }
                this.closeModal();
                if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                if (this.section === 'activities') this.loadActivities();
                this.loadDashboard();
            },

            async deleteActivity(id) {
                if (!confirm('Delete this activity?')) return;
                try {
                    await axios.delete(`/api/activities/${id}`);
                    this.toast('Activity deleted', 'ok');
                    if (this.drawerClient) this.loadDrawerData(this.drawerClient.id);
                    if (this.section === 'activities') this.loadActivities();
                    this.loadDashboard();
                } catch (e) { this.toast('Failed to delete activity', 'err'); }
            },

            /* ── Drawer ── */
            openDrawer(client) {
                this.drawerClient     = client;
                this.drawerTab        = 'info';
                this.drawerContacts   = [];
                this.drawerNotes      = [];
                this.drawerActivities = [];
                this.loadDrawerData(client.id);
            },

            async loadDrawerData(clientId) {
                this.drawerLoading = true;
                try {
                    const [contacts, notes, activities] = await Promise.all([
                        axios.get(`/api/clients/${clientId}/contacts`),
                        axios.get(`/api/clients/${clientId}/notes`),
                        axios.get(`/api/clients/${clientId}/activities`),
                    ]);
                    this.drawerContacts   = contacts.data.data;
                    this.drawerNotes      = notes.data.data;
                    this.drawerActivities = activities.data.data;
                } catch (e) { this.toast('Failed to load client data', 'err'); }
                finally { this.drawerLoading = false; }
            },

            /* ── Modal ── */
            openModal(mode, entity, data) {
                const titles = {
                    create: { client: 'New Client', contact: 'New Contact', note: 'New Note', activity: 'New Activity' },
                    edit:   { client: 'Edit Client', contact: 'Edit Contact', note: 'Edit Note', activity: 'Edit Activity' },
                };
                this.modal = { open: true, mode, entity, title: titles[mode][entity], data: { ...data }, saving: false };
            },

            async openModalGlobal(entity) {
                await this.loadClientsAll();
                const data = { _needClient: true, client_id: '' };
                if (entity === 'activity') data.type = 'task';
                this.openModal('create', entity, data);
            },

            async loadClientsAll() {
                if (this.clientsAll.length) return;
                try {
                    const { data } = await axios.get('/api/clients', { params: { page: 1, per_page: 100 } });
                    this.clientsAll = data.data;
                } catch (_) {}
            },

            closeModal() { this.modal.open = false; },

            async saveEntity() {
                this.modal.saving = true;
                try {
                    if (this.modal.entity === 'client')   await this.saveClient();
                    if (this.modal.entity === 'contact')  await this.saveContact();
                    if (this.modal.entity === 'note')     await this.saveNote();
                    if (this.modal.entity === 'activity') await this.saveActivity();
                } catch (e) {
                    const errors = e.response?.data?.errors;
                    const msg    = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Something went wrong.');
                    this.toast(msg, 'err');
                } finally { this.modal.saving = false; }
            },

            /* ── Toasts ── */
            toast(msg, type = 'ok') {
                const id = Date.now();
                this.toasts.push({ id, msg, type });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3500);
            },
        };
    }
    </script>
</body>
</html>
