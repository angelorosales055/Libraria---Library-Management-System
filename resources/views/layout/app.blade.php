<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Libraria - @yield('title', 'Library Management System')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --teal-dark: #1a3a3a;
            --teal-mid: #2d5a5a;
            --teal-accent: #3d7a6e;
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --cream: #f5f0e8;
            --cream-dark: #ede8de;
            --white: #ffffff;
            --text-dark: #1a2520;
            --text-mid: #4a5550;
            --text-light: #8a9590;
            --success: #2e7d52;
            --success-light: #d4edda;
            --danger: #c0392b;
            --danger-light: #fde8e6;
            --warning: #d4860a;
            --warning-light: #fef3cd;
            --info: #1a6fa8;
            --info-light: #dbeeff;
            --sidebar-w: 180px;
            --nav-h: 56px;
            --radius: 8px;
            --shadow: 0 2px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ── TOP NAV ── */
        .topnav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--nav-h);
            background: var(--teal-dark);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            gap: 12px;
        }
        .topnav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .brand-icon {
            width: 32px; height: 32px;
            background: var(--gold);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: var(--teal-dark); font-weight: 700;
        }
        .brand-name {
            font-family: 'DM Serif Display', serif;
            color: var(--white); font-size: 18px; letter-spacing: 0.5px;
        }
        .topnav-center {
            flex: 1; max-width: 380px; margin: 0 24px;
            min-width: 0;
        }
        .search-bar {
            width: 100%; position: relative;
        }
        .search-bar input {
            width: 100%; padding: 7px 14px 7px 36px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px; color: var(--white);
            font-family: inherit; font-size: 13px;
            outline: none; transition: all .2s;
        }
        .search-bar input::placeholder { color: rgba(255,255,255,0.5); }
        .search-bar input:focus { background: rgba(255,255,255,0.18); border-color: var(--gold); }
        .search-bar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.5); font-size: 12px; }
        .topnav-right { display: flex; align-items: center; gap: 10px; }
        .theme-toggle { margin-right: 4px; }
        .role-badge {
            padding: 4px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .role-admin { background: var(--gold); color: var(--teal-dark); }
        .role-librarian { background: var(--teal-accent); color: var(--white); }
        .role-user { background: rgba(255,255,255,0.2); color: var(--white); }
        .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--gold-light);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: var(--teal-dark);
            cursor: pointer;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: var(--nav-h); left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--cream);
            border-right: 1px solid rgba(0,0,0,0.08);
            display: flex; flex-direction: column;
            padding: 16px 12px;
            overflow-y: auto; z-index: 90;
        }
        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: var(--radius);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            color: var(--text-mid);
            transition: all .15s; cursor: pointer;
        }
        .sidebar-link:hover { background: var(--cream-dark); color: var(--text-dark); }
        .sidebar-link.active {
            background: var(--teal-dark); color: var(--white);
            font-weight: 600; box-shadow: 0 2px 8px rgba(26,58,58,0.3);
        }
        .sidebar-link i { width: 16px; text-align: center; font-size: 13px; }
        .sidebar-bottom {
            padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.08);
        }
        .sidebar-user-info { padding: 8px 14px; margin-bottom: 6px; }
        .sidebar-user-name { font-size: 13px; font-weight: 600; color: var(--text-dark); }
        .sidebar-user-role { font-size: 11px; color: var(--text-light); }
        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: var(--radius);
            background: none; border: none; cursor: pointer;
            font-family: inherit; font-size: 13px; color: var(--text-light);
            width: 100%; text-align: left; transition: all .15s;
        }
        .logout-btn:hover { background: var(--danger-light); color: var(--danger); }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-top: var(--nav-h);
            margin-left: var(--sidebar-w);
            padding: 28px;
            min-height: calc(100vh - var(--nav-h));
        }

        /* ── CARDS ── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.04);
        }
        .card-body { padding: 20px; }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 15px; font-weight: 600; color: var(--text-dark); }

        /* ── STAT CARDS ── */
        .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--white); border-radius: var(--radius);
            padding: 18px 20px; box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.04);
            border-top: 3px solid transparent;
        }
        .stat-card.blue { border-top-color: var(--info); }
        .stat-card.green { border-top-color: var(--success); }
        .stat-card.red { border-top-color: var(--danger); }
        .stat-card.gold { border-top-color: var(--gold); }
        .stat-card-link { display: block; text-decoration: none; color: inherit; }
        .stat-card-link:hover { transform: translateY(-1px); }
        .stat-label { font-size: 11px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--text-dark); line-height: 1; }
        .stat-sub { font-size: 11px; color: var(--text-light); margin-top: 4px; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: var(--radius);
            font-family: inherit; font-size: 13px; font-weight: 600;
            cursor: pointer; border: none; transition: all .15s;
            text-decoration: none; line-height: 1;
        }
        .btn-primary { background: var(--teal-dark); color: var(--white); }
        .btn-primary:hover { background: var(--teal-mid); color: var(--white); }
        .btn-success { background: var(--success); color: var(--white); }
        .btn-success:hover { background: #235f3e; color: var(--white); }
        .btn-danger { background: var(--danger); color: var(--white); }
        .btn-danger:hover { background: #a93226; color: var(--white); }
        .btn-warning { background: var(--warning); color: var(--white); }
        .btn-gold { background: var(--gold); color: var(--teal-dark); }
        .btn-gold:hover { background: var(--gold-light); }
        .btn-outline { background: transparent; color: var(--text-dark); border: 1.5px solid rgba(0,0,0,0.15); }
        .btn-outline:hover { border-color: var(--teal-dark); color: var(--teal-dark); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-xs { padding: 3px 8px; font-size: 11px; }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 8px; border-radius: 20px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.3px;
        }
        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-danger { background: var(--danger-light); color: var(--danger); }
        .badge-warning { background: var(--warning-light); color: var(--warning); }
        .badge-info { background: var(--info-light); color: var(--info); }
        .badge-gray { background: #e8eae9; color: var(--text-mid); }

        /* ── TABLES ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 10px 14px; text-align: left;
            font-size: 11px; font-weight: 600; color: var(--text-light);
            text-transform: uppercase; letter-spacing: 0.5px;
            background: var(--cream); border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        tbody td {
            padding: 12px 14px; font-size: 13px; color: var(--text-dark);
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        tbody tr:hover { background: rgba(0,0,0,0.015); }
        tbody tr:last-child td { border-bottom: none; }

        /* ── FORMS ── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-mid); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid rgba(0,0,0,0.12);
            border-radius: var(--radius); font-family: inherit; font-size: 13px;
            color: var(--text-dark); background: var(--white);
            outline: none; transition: all .15s;
        }
        .form-control:focus { border-color: var(--teal-accent); box-shadow: 0 0 0 3px rgba(61,122,110,0.1); }
        .form-control::placeholder { color: var(--text-light); }
        select.form-control { appearance: none; cursor: pointer; }

        .theme-toggle {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.92);
            color: var(--teal-dark); cursor: pointer; transition: all .15s;
        }
        .theme-toggle:hover { background: rgba(255,255,255,1); }

        body[data-theme='dark'] {
            background: #07121f;
            color: #f7f9ff;
            --cream: #0f1b2d;
            --cream-dark: #15253d;
            --white: #14243a;
            --text-dark: #f7f9ff;
            --text-mid: #9bb1d4;
            --text-light: #a6b8d5;
            --sidebar-w: 180px;
            --shadow: 0 12px 30px rgba(0,0,0,0.45);
            --danger-light: rgba(192,57,43,0.18);
            --warning-light: rgba(212,134,10,0.18);
            --info-light: rgba(26,111,168,0.18);
            --success-light: rgba(46,125,82,0.18);
        }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px; border-radius: var(--radius);
            font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-success { background: var(--success-light); color: var(--success); border: 1px solid rgba(46,125,82,0.2); }
        .alert-danger { background: var(--danger-light); color: var(--danger); border: 1px solid rgba(192,57,43,0.2); }
        .alert-warning { background: var(--warning-light); color: var(--warning); border: 1px solid rgba(212,134,10,0.2); }

        /* ── MODAL ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--white); border-radius: 12px;
            box-shadow: var(--shadow-lg); width: 90%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
            animation: modalIn .2s ease;
        }
        @keyframes modalIn { from { opacity:0; transform:translateY(-16px) scale(.97); } to { opacity:1; transform:none; } }
        .modal-header {
            padding: 18px 20px; border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-size: 16px; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 18px; color: var(--text-light); cursor: pointer; padding: 2px; }
        .modal-close:hover { color: var(--danger); }
        .modal-body { padding: 20px; }
        .modal-footer { padding: 16px 20px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: flex-end; gap: 10px; }

        /* ── PAGE HEADER ── */
        .page-header { margin-bottom: 24px; }
        .page-title { font-size: 22px; font-weight: 700; color: var(--text-dark); }
        .page-subtitle { font-size: 13px; color: var(--text-light); margin-top: 2px; }
        .page-header-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }

        /* ── WORKFLOW STEPS ── */
        .workflow-steps {
            display: flex; gap: 0; margin-bottom: 24px;
            background: var(--cream-dark); border-radius: var(--radius); padding: 14px 20px;
            overflow-x: auto;
        }
        .workflow-step {
            display: flex; align-items: center; gap: 8px; flex-shrink: 0;
        }
        .workflow-step:not(:last-child)::after {
            content: '›'; color: var(--text-light); margin: 0 12px; font-size: 18px;
        }
        .step-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--teal-dark); color: var(--white);
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .step-text { font-size: 12px; color: var(--text-mid); }
        .step-sub { font-size: 10px; color: var(--text-light); }

        /* ── SIMPLE PAGINATION ── */
        .pagination-simple {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }
        .pagination-simple .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid rgba(0,0,0,0.12);
            background: var(--white);
            color: var(--text-dark);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: var(--shadow);
        }
        .pagination-simple .pagination-btn:hover:not(.pagination-disabled) {
            background: var(--cream-dark);
            border-color: var(--teal-accent);
            color: var(--teal-dark);
            transform: translateY(-1px);
        }
        .pagination-simple .pagination-disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: var(--cream);
        }
        .pagination-simple .pagination-info {
            font-size: 13px;
            color: var(--text-mid);
            font-weight: 500;
            padding: 0 8px;
            user-select: none;
        }
        body[data-theme='dark'] .pagination-simple .pagination-btn {
            border-color: rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--text-dark);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        body[data-theme='dark'] .pagination-simple .pagination-btn:hover:not(.pagination-disabled) {
            background: rgba(77,212,182,0.14);
            border-color: rgba(77,212,182,0.4);
            color: var(--accent);
        }
        body[data-theme='dark'] .pagination-simple .pagination-disabled {
            background: rgba(255,255,255,0.03);
        }
        body[data-theme='dark'] .pagination-simple .pagination-info {
            color: var(--text-mid);
        }

        /* ── HAMBURGER MENU ── */
        .hamburger-btn {
            display: none; flex-direction: column; justify-content: center;
            align-items: center; gap: 4px;
            width: 36px; height: 36px;
            border: none; background: none;
            cursor: pointer; padding: 6px;
        }
        .hamburger-btn span {
            display: block; width: 20px; height: 2px;
            background: var(--white); border-radius: 1px;
            transition: all .3s;
        }
        .hamburger-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }
        .hamburger-btn.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        /* ── SIDEBAR OVERLAY ── */
        .sidebar-overlay {
            display: none;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
            .hamburger-btn { display: flex; }
            .topnav { padding: 0 8px; }
            .topnav-center { 
                flex: 1; max-width: none; margin: 0 8px;
                display: none;
            }
            .topnav-right { gap: 6px; }
            .brand-name { display: none; }
            .sidebar { 
                position: fixed; top: var(--nav-h); left: 0; bottom: 0;
                width: var(--sidebar-w);
                transform: translateX(-100%); 
                transition: transform .25s ease;
                z-index: 95;
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            .sidebar.open ~ .sidebar-overlay { 
                display: block;
                position: fixed; top: var(--nav-h); left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.3);
                z-index: 91;
            }
            .main-content { margin-left: 0; }
        }
        @media (max-width: 600px) {
            .stat-cards { grid-template-columns: 1fr; }
            .main-content { padding: 16px; }
            .sidebar { width: 70vw; max-width: 250px; }
            .topnav-right { flex-direction: column; gap: 0; }
            .role-badge { display: none; }
        }

        /* ── MISC ── */
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .text-warning { color: var(--warning); }
        .text-muted { color: var(--text-light); }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mt-4 { margin-top: 16px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .w-full { width: 100%; }
        .font-bold { font-weight: 700; }

        /* Toast */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast {
            background: var(--teal-dark); color: var(--white);
            padding: 12px 16px; border-radius: var(--radius);
            font-size: 13px; min-width: 260px;
            display: flex; align-items: center; gap: 10px;
            box-shadow: var(--shadow-lg);
            animation: toastIn .3s ease;
        }
        .toast.success { border-left: 4px solid var(--success); }
        .toast.error { border-left: 4px solid var(--danger); }
        @keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }

        /* Book card */
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 14px; }
        .book-card {
            background: var(--white); border-radius: var(--radius);
            overflow: hidden; box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.04);
            transition: transform .15s, box-shadow .15s;
        }
        .book-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .book-cover {
            height: 110px; display: flex; align-items: center; justify-content: center;
            font-size: 32px; position: relative;
        }
        .book-info { padding: 10px; }
        .book-title { font-size: 12px; font-weight: 600; color: var(--text-dark); line-height: 1.3; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .book-author { font-size: 11px; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .book-status { margin-top: 6px; }

        /* Member avatar */
        .member-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; flex-shrink: 0;
        }
        .avatar-teal { background: var(--teal-dark); color: var(--white); }
        .avatar-gold { background: var(--gold); color: var(--teal-dark); }
        .avatar-sage { background: #5a8a6a; color: var(--white); }
        .avatar-rust { background: #8a4a2a; color: var(--white); }
        .avatar-navy { background: #2a4a7a; color: var(--white); }

        /* ── PAGINATION ── */
        nav[aria-label='Pagination Navigation'] a[rel='prev'],
        nav[aria-label='Pagination Navigation'] a[rel='next'],
        nav[aria-label='Pagination Navigation'] span[aria-disabled='true'] {
            min-width: 1.25rem !important;
            min-height: 1.25rem !important;
            width: 1.25rem !important;
            height: 1.25rem !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.75rem !important;
            line-height: 1 !important;
        }

        nav[aria-label='Pagination Navigation'] svg,
        nav[aria-label='Pagination Navigation'] a[rel='prev'] svg,
        nav[aria-label='Pagination Navigation'] a[rel='next'] svg {
            width: 0.75rem !important;
            height: 0.75rem !important;
            flex-shrink: 0 !important;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- TOP NAV --}}
<nav class="topnav">
    <button id="hamburgerBtn" class="hamburger-btn" aria-label="Toggle navigation menu" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <a href="{{ route('dashboard') }}" class="topnav-brand">
        <div class="brand-icon">L</div>
        <span class="brand-name">Libraria</span>
    </a>
    <div class="topnav-center">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search books, members..." id="globalSearch">
        </div>
    </div>
    <div class="topnav-right">
        <button id="themeToggleButton" class="theme-toggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme">
            <i id="themeToggleIcon" class="fas fa-moon"></i>
        </button>
        @auth
            <span class="role-badge role-{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>
            <div class="user-avatar" title="{{ auth()->user()->name }}">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
        @endauth
    </div>
</nav>

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="{{ route('books.index') }}" class="sidebar-link {{ request()->routeIs('books.*') ? 'active' : '' }}">
            <i class="fas fa-book"></i> Book Catalog
        </a>
        <a href="{{ route('circulation.index') }}" class="sidebar-link {{ request()->routeIs('circulation.*') ? 'active' : '' }}">
            <i class="fas fa-exchange-alt"></i> Circulation
        </a>
        <a href="{{ route('members.index') }}" class="sidebar-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Members
        </a>
        <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
@if(auth()->check() && auth()->user()->role === 'admin')
        <a href="{{ route('activity-log.index') }}" class="sidebar-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
            <i class="fas fa-list-alt"></i> Activity Log
        </a>
        @endif
        @if(auth()->check() && auth()->user()->role === 'admin')
        <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> User Management
        </a>
        @endif
    </nav>

    <div class="sidebar-bottom">
        @auth
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        @endauth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </button>
        </form>
    </div>
</aside>

{{-- SIDEBAR OVERLAY (Mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

{{-- MAIN --}}
<main class="main-content">
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>

<script>
function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':'exclamation-circle'}"></i> ${msg}`;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburgerBtn');
    sidebar.classList.toggle('open');
    hamburger.classList.toggle('active');
}
// Close sidebar when clicking on a link
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('hamburgerBtn').classList.remove('active');
        }
    });
});
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open'); });
});
@if(session('toast_success'))
    document.addEventListener('DOMContentLoaded', () => showToast('{{ session('toast_success') }}', 'success'));
@endif
</script>
<script>
const uiThemeKey = 'ui-theme';
function setTheme(theme) {
    document.body.dataset.theme = theme;
    localStorage.setItem(uiThemeKey, theme);
    const icon = document.getElementById('themeToggleIcon');
    if (!icon) return;
    icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}
function toggleTheme() {
    setTheme(document.body.dataset.theme === 'dark' ? 'light' : 'dark');
}
(function() {
    const saved = localStorage.getItem(uiThemeKey);
    const defaultTheme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    setTheme(defaultTheme);
})();
</script>
@stack('scripts')
</body>
</html>
