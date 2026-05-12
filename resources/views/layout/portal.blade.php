<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HomeLibrary - @yield('title', 'Welcome')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #07121f;
            --surface: #0f1b33;
            --surface-2: #132344;
            --surface-3: #17294c;
            --text: #f7f9ff;
            --muted: #9bb1d4;
            --accent: #4dd4b6;
            --accent-soft: rgba(77,212,182,0.16);
            --danger: #ff6b6b;
            --radius: 16px;
            --shadow: 0 24px 80px rgba(0,0,0,0.35);
        }
        
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh; 
            background: radial-gradient(circle at top left, rgba(77,212,182,0.12), transparent 30%), 
                        linear-gradient(180deg, #0a1526 0%, #07121f 100%); 
            color: var(--text); 
        }
        
        a { 
            color: inherit; 
            text-decoration: none; 
        }
        
        button { 
            font: inherit; 
        }
        
        /* Header / Navigation */
        .portal-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            gap: 18px; 
            padding: 18px 34px; 
            position: sticky; 
            top: 0; 
            backdrop-filter: blur(20px); 
            background: rgba(7,18,31,0.96); 
            border-bottom: 1px solid rgba(255,255,255,0.08); 
            z-index: 20; 
        }
        
        .portal-brand { 
            display: flex; 
            align-items: center; 
            gap: 14px; 
        }
        
        .brand-mark { 
            width: 42px; 
            height: 42px; 
            border-radius: 16px; 
            background: var(--accent); 
            color: #06131f; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 700; 
            font-size: 18px; 
        }
        
        .brand-title { 
            font-size: 18px; 
            font-weight: 700; 
            letter-spacing: 0.3px; 
        }
        
        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        
        .nav-link { 
            padding: 10px 16px; 
            border-radius: 14px; 
            transition: background .2s, color .2s; 
            color: var(--muted); 
            background: rgba(255,255,255,0.04); 
        }
        
        .nav-link.active, 
        .nav-link:hover { 
            background: rgba(255,255,255,0.16); 
            color: var(--text); 
        }
        
        .portal-actions { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        
        /* Theme Toggle */
        .theme-toggle { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            width: 38px; 
            height: 38px; 
            border-radius: 14px; 
            border: 1px solid rgba(255,255,255,0.18); 
            background: rgba(255,255,255,0.08); 
            color: var(--text); 
            cursor: pointer; 
            transition: all .15s; 
        }
        
        .theme-toggle:hover { 
            background: rgba(255,255,255,0.14); 
        }
        
        /* Search Bar */
        .search-bar { 
            position: relative; 
            min-width: 280px; 
        }
        
        .search-bar input { 
            width: 100%; 
            padding: 10px 12px 10px 40px; 
            border-radius: 999px; 
            border: 1px solid rgba(255,255,255,0.1); 
            background: rgba(255,255,255,0.06); 
            color: var(--text); 
            outline: none; 
        }
        
        .search-bar i { 
            position: absolute; 
            left: 14px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: rgba(255,255,255,0.55); 
        }
        
        /* User Chip */
        .user-chip { 
            border: 1px solid rgba(255,255,255,0.12); 
            padding: 10px 14px; 
            border-radius: 999px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            cursor: pointer; 
            background: rgba(255,255,255,0.08); 
        }
        
        .user-chip span { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            width: 34px; 
            height: 34px; 
            border-radius: 50%; 
            background: rgba(255,255,255,0.14); 
            font-weight: 700; 
        }
        
        /* Notifications */
        .notifications { 
            position: relative; 
        }
        
        .notif-btn { 
            padding: 10px 12px; 
            border-radius: 14px; 
            background: rgba(255,255,255,0.08); 
            border: 1px solid rgba(255,255,255,0.08); 
            cursor: pointer; 
            color: var(--text); 
            position: relative; 
        }
        
        .notif-count { 
            position: absolute; 
            top: 6px; 
            right: 6px; 
            width: 18px; 
            height: 18px; 
            border-radius: 50%; 
            background: #ff6b6b; 
            color: #fff; 
            font-size: 11px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        .notif-dropdown { 
            position: absolute; 
            right: 0; 
            top: calc(100% + 12px); 
            min-width: 320px; 
            border-radius: 18px; 
            background: var(--surface); 
            border: 1px solid rgba(255,255,255,0.08); 
            box-shadow: var(--shadow); 
            overflow: hidden; 
            display: none; 
            z-index: 25;
        }
        
        .notif-dropdown.open { 
            display: block; 
        }
        
        .notif-header { 
            padding: 16px 18px; 
            border-bottom: 1px solid rgba(255,255,255,0.08); 
            font-weight: 700; 
        }
        
        .notif-item { 
            padding: 14px 18px; 
            display: flex; 
            gap: 10px; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
        }
        
        .notif-item:last-child { 
            border-bottom: none; 
        }
        
        .notif-dot { 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            margin-top: 5px; 
            background: var(--accent); 
        }
        
        /* Main Content Container */
        .page-body { 
            max-width: 1400px; 
            width: min(1400px, calc(100% - 48px)); 
            margin: 32px auto 48px; 
            padding: 0 12px; 
        }
        
        /* Hero Section */
        .hero-card { 
            background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.12); 
            border-radius: 30px; 
            padding: 36px; 
            box-shadow: var(--shadow); 
            display: grid; 
            grid-template-columns: minmax(0, 1fr) 360px; 
            gap: 32px; 
            align-items: center; 
            margin-bottom: 32px; 
        }
        
        .hero-copy { 
            max-width: 620px; 
        }
        
        .hero-copy h1 { 
            font-size: 48px; 
            line-height: 1.05; 
            margin-bottom: 18px; 
            letter-spacing: -0.03em; 
        }
        
        .hero-copy p { 
            color: var(--muted); 
            line-height: 1.8; 
            margin-bottom: 26px; 
            max-width: 520px; 
        }
        
        .hero-actions { 
            display: flex; 
            gap: 14px; 
            flex-wrap: wrap; 
        }
        
        .hero-actions .btn { 
            border: none; 
            border-radius: 16px; 
            padding: 14px 24px; 
            font-weight: 700; 
            cursor: pointer; 
            min-width: 150px; 
        }
        
        .btn-primary { 
            background: var(--accent); 
            color: #06131f; 
        }
        
        .btn-primary:hover { 
            background: #41c5aa; 
        }
        
        .btn-secondary { 
            background: rgba(255,255,255,0.08); 
            color: var(--text); 
        }
        
        .btn-secondary:hover { 
            background: rgba(255,255,255,0.14); 
        }
        
        .hero-image { 
            width: 100%; 
            min-height: 320px; 
            border-radius: 24px; 
            background: linear-gradient(180deg, rgba(77,212,182,0.18), rgba(13,33,57,0.95)); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--text); 
            padding: 30px; 
        }
        
        .hero-image div { 
            width: 100%; 
        }
        
        /* Summary Cards */
        .summary-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 18px; 
            margin-bottom: 28px; 
        }
        
        .summary-card { 
            background: rgba(255,255,255,0.06); 
            border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 24px; 
            padding: 28px 26px; 
            box-shadow: var(--shadow); 
            min-height: 130px; 
        }
        
        .summary-card span { 
            display: block; 
            color: var(--muted); 
            margin-bottom: 10px; 
            font-size: 12px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        
        .summary-card h2 { 
            font-size: 34px; 
            margin: 0; 
        }
        
        /* Section Styles */
        .section { 
            margin-bottom: 32px; 
        }
        
        .section-header { 
            display: flex; 
            flex-wrap: wrap; 
            align-items: flex-start; 
            justify-content: space-between; 
            gap: 18px; 
            margin-bottom: 16px; 
        }
        
        .section-header h2 { 
            font-size: 24px; 
            margin: 0; 
        }
        
        .section-header p { 
            margin: 0; 
            color: var(--muted); 
            font-size: 14px; 
        }
        
        .section-header .nav-link { 
            background: transparent; 
            color: var(--accent); 
            font-weight: 600; 
            padding: 8px 14px; 
            border-radius: 14px; 
        }
        
        .section-header .nav-link:hover { 
            background: rgba(77,212,182,0.12); 
            color: var(--text); 
        }
        
        /* Filters */
        .filters-row { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 16px; 
            margin-bottom: 28px; 
            align-items: end; 
        }
        
        .filter-input {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            color: var(--text);
            width: 100%;
            transition: border-color .2s, background .2s;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: rgba(77,212,182,0.45);
            background: rgba(255,255,255,0.1);
        }
        
        .filters-row .btn { 
            min-width: 160px; 
            justify-self: start; 
        }
        
        /* Grid Layouts */
        .featured-grid, 
        .books-grid, 
        .category-grid { 
            display: grid; 
            gap: 22px; 
        }
        
        .featured-grid { 
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
        }
        
        .books-grid { 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
        }
        
        .category-grid { 
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
        }
        
        /* Book Card */
        .book-card { 
            background: var(--surface); 
            border: 1px solid rgba(255,255,255,0.08); 
            border-radius: 24px; 
            overflow: hidden; 
            box-shadow: var(--shadow); 
            transition: transform .24s ease, border-color .24s ease; 
        }
        
        .book-card:hover { 
            transform: translateY(-4px); 
            border-color: rgba(77,212,182,0.3); 
        }
        
        .book-card img, 
        .book-cover-placeholder { 
            width: 100%; 
            aspect-ratio: 1.1 / 1; 
            object-fit: cover; 
            display: block; 
        }
        
        .book-card-body { 
            padding: 22px; 
            display: flex; 
            flex-direction: column; 
            gap: 14px; 
        }
        
        .book-category { 
            display: inline-flex; 
            padding: 6px 12px; 
            border-radius: 999px; 
            background: rgba(77,212,182,0.14); 
            color: var(--accent); 
            font-size: 12px; 
            margin-bottom: 10px; 
            align-self: flex-start;
        }
        
        .book-title { 
            font-size: 18px; 
            line-height: 1.28; 
            margin-bottom: 10px; 
            font-weight: 700;
        }
        
        .book-author { 
            color: var(--muted); 
            font-size: 13px; 
            margin-bottom: 16px; 
        }
        
        .book-meta { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            gap: 10px; 
            margin-bottom: 18px; 
        }
        
        .badge { 
            padding: 8px 14px; 
            border-radius: 999px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        
        .badge.available { 
            background: rgba(77,212,182,0.14); 
            color: var(--accent); 
        }
        
        /* Buttons */
        .btn-sm { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            width: 100%; 
            padding: 14px 16px; 
            background: var(--accent); 
            border: none; 
            border-radius: 16px; 
            font-weight: 700; 
            color: #06131f; 
            cursor: pointer; 
            transition: all 0.2s;
        }
        
        .btn-sm:hover:not(.disabled) {
            background: #41c5aa;
            transform: translateY(-1px);
        }
        
        .btn-sm.disabled { 
            opacity: 0.45; 
            cursor: not-allowed; 
            background: rgba(255,255,255,0.08); 
            color: var(--text); 
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--text);
        }
        
        .btn-outline:hover {
            background: rgba(255,255,255,0.08);
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        
        .modal-overlay.open { 
            display: flex; 
        }
        
        .modal {
            width: 100%;
            max-width: 520px;
            background: var(--surface);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 28px 80px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 20px 0 20px;
        }
        
        .modal-title { 
            font-size: 18px; 
            font-weight: 700; 
        }
        
        .modal-close { 
            background: none; 
            border: none; 
            color: var(--text); 
            font-size: 22px; 
            cursor: pointer; 
        }
        
        .modal-body { 
            padding: 20px; 
        }
        
        .modal-footer { 
            display: flex; 
            justify-content: flex-end; 
            gap: 10px; 
            padding: 18px 20px 20px; 
        }
        
        .modal-footer .btn-outline { 
            background: rgba(255,255,255,0.08); 
            color: var(--text); 
            border: 1px solid rgba(255,255,255,0.08); 
        }
        
        .modal-footer .btn-primary { 
            background: var(--accent); 
            color: #06131f; 
        }
        
        /* Form Controls */
        .form-control { 
            width: 100%; 
            padding: 12px 14px; 
            border-radius: 14px; 
            border: 1px solid rgba(255,255,255,0.08); 
            background: rgba(255,255,255,0.05); 
            color: var(--text); 
            outline: none; 
        }
        
        .form-control:focus { 
            border-color: rgba(77,212,182,0.5); 
        }
        
        select.form-control {
            background: rgba(77,212,182,0.14);
            border-color: rgba(77,212,182,0.4);
            color: var(--text);
        }
        
        select.form-control option {
            background: var(--surface);
            color: var(--text);
        }
        
        /* Table Styles */
        .table-wrap { 
            overflow-x: auto; 
            background: rgba(255,255,255,0.04); 
            border: 1px solid rgba(255,255,255,0.08); 
            border-radius: 24px; 
            padding: 18px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 720px; 
        }
        
        table thead tr { 
            border-bottom: 1px solid rgba(255,255,255,0.12); 
        }
        
        table th, 
        table td { 
            padding: 16px 18px; 
            text-align: left; 
            border-bottom: 1px solid rgba(255,255,255,0.08); 
        }
        
        table th { 
            color: var(--muted); 
            font-size: 12px; 
            text-transform: uppercase; 
            letter-spacing: 0.4px; 
        }
        
        table td { 
            color: var(--text); 
            font-size: 14px; 
        }
        
        table tbody tr:hover { 
            background: rgba(255,255,255,0.04); 
        }
        
        /* Status Chips */
        .status-chip { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 8px 12px; 
            border-radius: 999px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        
        .status-active { 
            background: rgba(77,212,182,0.16); 
            color: var(--accent);
            border: 1px solid rgba(77,212,182,0.3);
            transition: all 0.3s ease;
        }

        .status-active:hover {
            background: rgba(77,212,182,0.22);
            border-color: rgba(77,212,182,0.5);
        }
        
        .status-overdue { 
            background: rgba(255,107,107,0.16); 
            color: var(--danger);
            border: 1px solid rgba(255,107,107,0.3);
            animation: pulseOverdue 2s ease-in-out infinite;
        }

        @keyframes pulseOverdue {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(255,107,107,0.2);
            }
            50% {
                box-shadow: 0 0 8px 0 rgba(255,107,107,0.25);
            }
        }
        
        .status-damaged {
            background: linear-gradient(135deg, rgba(255,87,34,0.2), rgba(255,152,0,0.15));
            color: #ff5722;
            border: 1.5px solid rgba(255,87,34,0.4);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            animation: damageAlert 2s ease-in-out infinite;
        }

        .status-damaged::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s infinite;
        }

        .status-damaged i {
            font-size: 14px;
            animation: pulse-icon 1.5s ease-in-out infinite;
        }

        @keyframes damageAlert {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(255,87,34,0.2);
            }
            50% {
                box-shadow: 0 0 12px 0 rgba(255,87,34,0.3);
            }
        }

        @keyframes pulse-icon {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }

        /* Damage alert card variant */
        .damage-alert-card {
            background: linear-gradient(135deg, rgba(255,87,34,0.08), rgba(255,152,0,0.06));
            border: 1.5px solid rgba(255,87,34,0.25);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .damage-alert-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #ff5722, #ff9800);
            animation: pulse-border 2s ease-in-out infinite;
        }

        .damage-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(255,87,34,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff5722;
            font-size: 18px;
            flex-shrink: 0;
        }

        .damage-details {
            flex: 1;
            min-width: 0;
        }

        .damage-label {
            font-size: 13px;
            font-weight: 600;
            color: #ff5722;
            margin-bottom: 3px;
        }

        .damage-reason {
            font-size: 12px;
            color: var(--text-light);
            line-height: 1.3;
        }

        @keyframes pulse-border {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
        
        .status-returned { 
            background: rgba(255,255,255,0.08); 
            color: var(--text); 
        }
        
        /* Toast Messages */
        .toast { 
            position: fixed; 
            right: 24px; 
            bottom: 24px; 
            background: rgba(255,255,255,0.1); 
            color: var(--text); 
            border: 1px solid rgba(255,255,255,0.14); 
            padding: 16px 22px; 
            border-radius: 18px; 
            box-shadow: var(--shadow); 
            z-index: 30; 
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Pagination */
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
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .pagination-simple .pagination-btn:hover:not(.pagination-disabled) {
            background: rgba(77,212,182,0.18);
            border-color: rgba(77,212,182,0.4);
            color: var(--accent);
            transform: translateY(-1px);
        }
        .pagination-simple .pagination-disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .pagination-simple .pagination-info {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            padding: 0 8px;
            user-select: none;
        }
        
        /* Light Theme */
        body[data-theme='light'] {
            background: #eef8f6;
            color: #1f3636;
            --bg: #eef8f6;
            --surface: #ffffff;
            --surface-2: #f4fbfa;
            --surface-3: #e5f3ee;
            --text: #1f3636;
            --muted: #5c7a70;
            --accent: #2f7a6b;
            --accent-soft: rgba(47,122,107,0.16);
            --danger: #c0392b;
            --shadow: 0 18px 60px rgba(0,0,0,0.08);
        }
        
        body[data-theme='light'] .portal-header {
            background: rgba(255,255,255,0.96);
            border-bottom-color: rgba(0,0,0,0.08);
        }
        
        body[data-theme='light'] .search-bar input {
            background: rgba(0,0,0,0.04);
            border-color: rgba(0,0,0,0.1);
        }
        
        body[data-theme='light'] .pagination-simple .pagination-btn {
            border-color: rgba(0,0,0,0.1);
            background: rgba(0,0,0,0.04);
            color: var(--text);
        }
        body[data-theme='light'] .pagination-simple .pagination-btn:hover:not(.pagination-disabled) {
            background: rgba(47,122,107,0.12);
            border-color: rgba(47,122,107,0.35);
            color: var(--accent);
        }
        body[data-theme='light'] .pagination-simple .pagination-info {
            color: var(--muted);
        }
        
        /* Responsive */
        @media (max-width: 960px) {
            .portal-header { 
                flex-wrap: wrap; 
                padding: 16px; 
            }
            .nav-links { 
                flex-wrap: wrap; 
                justify-content: center;
            }
            .summary-grid { 
                grid-template-columns: repeat(2, minmax(0, 1fr)); 
            }
            .hero-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero-actions {
                justify-content: center;
            }
        }
        
        @media (max-width: 720px) {
            .summary-grid, 
            .featured-grid, 
            .books-grid, 
            .category-grid { 
                grid-template-columns: 1fr; 
            }
            .search-bar { 
                width: 100%; 
                min-width: auto;
            }
            .portal-actions {
                flex-wrap: wrap;
                width: 100%;
            }
            .page-body {
                width: calc(100% - 24px);
                padding: 0 8px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Navigation Header --}}
    <nav class="portal-header">
        <div class="portal-brand">
            <div class="brand-mark">L</div>
            <div>
                <div class="brand-title">Libraria</div>
                <div style="font-size:12px; color:var(--muted)">Your personal library portal</div>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="{{ route('portal.home') }}" class="nav-link {{ request()->routeIs('portal.home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('portal.collection') }}" class="nav-link {{ request()->routeIs('portal.collection') ? 'active' : '' }}">Collection</a>
            <a href="{{ route('portal.transactions') }}" class="nav-link {{ request()->routeIs('portal.transactions') ? 'active' : '' }}">Transaction</a>
        </div>
        
        <div class="portal-actions">
            <button id="themeToggleButton" class="theme-toggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme">
                <i id="themeToggleIcon" class="fas fa-sun"></i>
            </button>
            
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search books..." onfocus="window.location='{{ route('portal.collection') }}'">
            </div>
            
            <div class="notifications">
                @php
                    $unreadCount = 0;
                    $notifList = collect();
                    if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                        $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                            ->whereNull('read_at')->count();
                        $notifList = \App\Models\Notification::where('user_id', auth()->id())
                            ->latest()->take(8)->get();
                    }
                @endphp
                <button class="notif-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="notif-count">{{ $unreadCount }}</span>
                    @endif
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header" style="display:flex;justify-content:space-between;align-items:center">
                        <span>Notifications</span>
                        @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}" style="display:inline">
                            @csrf
                            <button type="submit" style="background:none;border:none;color:var(--accent);font-size:11px;cursor:pointer">Mark all read</button>
                        </form>
                        @endif
                    </div>
                    @forelse($notifList as $note)
                        @php
                            $transactionId = is_array($note->data) ? ($note->data['transaction_id'] ?? null) : null;
                            $notifLink = route('portal.transactions');

                            if ($note->type === 'payment_success' && $transactionId) {
                                $notifLink = route('receipt.show', $transactionId);
                            } elseif ($note->type === 'book_damaged') {
                                $notifLink = route('portal.fines');
                            } elseif ($note->type === 'overdue_reminder') {
                                $notifLink = route('portal.transactions');
                            } elseif (in_array($note->type, ['borrow_success','return_approved','rejected'], true)) {
                                $notifLink = route('portal.transactions');
                            }
                        @endphp

                        <div class="notif-item"
                             style="opacity:{{ $note->read_at ? '0.6' : '1' }}"
                             data-notif-link="{{ $notifLink }}"
                             onclick="handleNotifClick(event, this)">
                            <div class="notif-dot" style="background:{{ $note->read_at ? 'var(--muted)' : 'var(--accent)' }}"></div>
                            <div style="flex:1">
                                <div style="font-weight:700;font-size:13px"><i class="fas {{ $note->icon }}" style="color:{{ $note->icon_color }};margin-right:6px"></i>{{ $note->title }}</div>
                                <div style="color:var(--muted);font-size:12px;margin-top:4px">{{ $note->message }}</div>
                                <div style="color:var(--muted);font-size:11px;margin-top:4px">{{ $note->created_at->diffForHumans() }}</div>
                            </div>
                            @if(!$note->read_at)
                            <form method="POST" action="{{ route('notifications.read', $note) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        style="background:none;border:none;color:var(--accent);font-size:11px;cursor:pointer;padding:4px"
                                        onclick="event.stopPropagation()">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    @empty
                        <div class="notif-item">
                            <div class="notif-dot" style="background:var(--muted)"></div>
                            <div style="color:var(--muted);font-size:13px">No notifications yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <div class="user-dropdown" style="position:relative">
                <button class="user-chip" onclick="toggleUserDropdown()">
                    <span>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
                    {{ auth()->user()->name ?? 'User' }}
                    <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);min-width:200px;background:var(--surface);border:1px solid rgba(255,255,255,0.08);border-radius:16px;box-shadow:var(--shadow);z-index:25;overflow:hidden">
                    <a href="{{ route('profile') }}" class="dropdown-item" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:var(--text);font-size:13px;transition:background .15s"><i class="fas fa-user" style="width:16px"></i> My Profile</a>
                    <a href="{{ route('portal.fines') }}" class="dropdown-item" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:var(--text);font-size:13px;transition:background .15s">
                        <i class="fas fa-money-bill-wave" style="width:16px"></i> My Fines
                    </a>
                    <div style="height:1px;background:rgba(255,255,255,0.08);margin:4px 0"></div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="dropdown-item" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:var(--danger);font-size:13px;background:none;border:none;width:100%;cursor:pointer;transition:background .15s">
                            <i class="fas fa-sign-out-alt" style="width:16px"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    
    {{-- Main Content --}}
    <div class="page-body">
        @if(session('toast_success'))
            <div class="toast">{{ session('toast_success') }}</div>
        @endif
        @if(session('error'))
            <div class="toast" style="background: rgba(255,107,107,0.18);">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>

    {{-- Book Details Modal --}}
    <div class="modal-overlay" id="bookDetailsModal">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="bookDetailsModalTitle">
            <div class="modal-header">
                <div>
                    <div class="modal-title" id="bookDetailsModalTitle">Book Details</div>
                    <div id="bookDetailsModalSubtitle" style="color:var(--muted);font-size:13px;margin-top:6px">View full description</div>
                </div>
                <button type="button" class="modal-close" onclick="closeBookDetailsModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:18px">
                    <img id="bookDetailsCover" src="" alt="Book cover" style="width:84px;height:110px;border-radius:18px;object-fit:cover;background:rgba(255,255,255,0.06)">
                    <div style="flex:1;min-width:0">
                        <div id="bookDetailsTitle" style="font-size:18px;font-weight:800;margin-bottom:6px;line-height:1.25"></div>
                        <div id="bookDetailsAuthor" style="color:var(--muted);font-size:13px;margin-bottom:14px"></div>
                        <div style="color:var(--text);font-size:13px;line-height:1.7">
                            <div style="color:var(--muted);font-size:12px;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.4px">Description</div>
                            <div id="bookDetailsDescription" style="white-space:pre-wrap"></div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;color:var(--muted);font-size:12px">
                    <span style="padding:6px 10px;border:1px solid rgba(255,255,255,0.1);border-radius:999px;background:rgba(255,255,255,0.04)" id="bookDetailsCategory"></span>
                    <span style="padding:6px 10px;border:1px solid rgba(255,255,255,0.1);border-radius:999px;background:rgba(255,255,255,0.04)" id="bookDetailsIsbn"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeBookDetailsModal()">Close</button>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // Notification Dropdown
        function toggleNotifications() {
            document.getElementById('notifDropdown').classList.toggle('open');
        }

        function handleNotifClick(event, el) {
            const link = el?.dataset?.notifLink;
            if (!link) return;

            // If click originated from a form/button (e.g. mark-as-read), stopPropagation already handles it,
            // but we keep this guard just in case.
            if (event?.target?.closest('form, button')) return;

            // Close dropdown and navigate
            document.getElementById('notifDropdown')?.classList.remove('open');
            window.location.href = link;
        }
        
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notifDropdown');
            if (!event.target.closest('.notifications')) {
                dropdown?.classList.remove('open');
            }
        });
        
        // Theme Management
        const uiThemeKey = 'ui-theme';
        
        function setTheme(theme) {
            document.body.dataset.theme = theme;
            localStorage.setItem(uiThemeKey, theme);
            const icon = document.getElementById('themeToggleIcon');
            if (icon) {
                icon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
        
        function toggleTheme() {
            const currentTheme = document.body.dataset.theme;
            setTheme(currentTheme === 'light' ? 'dark' : 'light');
        }

        function toggleUserDropdown() {
            document.getElementById('userDropdownMenu').style.display =
                document.getElementById('userDropdownMenu').style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdownMenu');
            if (dropdown && !event.target.closest('.user-dropdown')) {
                dropdown.style.display = 'none';
            }
        });
        
        // Initialize Theme
        (function() {
            const saved = localStorage.getItem(uiThemeKey);
            const prefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
            const defaultTheme = saved || (prefersLight ? 'light' : 'dark');
            setTheme(defaultTheme);
        })();
        
        function openBookDetailsModal(details) {
            const cover = document.getElementById('bookDetailsCover');
            const titleEl = document.getElementById('bookDetailsTitle');
            const authorEl = document.getElementById('bookDetailsAuthor');
            const descEl = document.getElementById('bookDetailsDescription');
            const categoryEl = document.getElementById('bookDetailsCategory');
            const isbnEl = document.getElementById('bookDetailsIsbn');

            if (!cover || !titleEl || !authorEl || !descEl || !categoryEl || !isbnEl) return;

            cover.src = details.cover || '';
            cover.style.display = cover.src ? 'block' : 'block';

            titleEl.textContent = details.title || '—';
            authorEl.textContent = details.author || '—';
            descEl.textContent = details.description || '—';

            categoryEl.textContent = details.category ? `Category: ${details.category}` : '';
            isbnEl.textContent = details.isbn ? `ISBN: ${details.isbn}` : '';

            document.getElementById('bookDetailsModal').classList.add('open');
        }

        function closeBookDetailsModal() {
            document.getElementById('bookDetailsModal').classList.remove('open');
        }

        document.getElementById('bookDetailsModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeBookDetailsModal();
        });

        // Book card click-to-view-details (ignore clicks on buttons/forms/links)
        document.addEventListener('click', function(event) {
            const card = event.target.closest('[data-book-details]');
            if (!card) return;

            if (event.target.closest('button, form, a, input, select, textarea, label')) {
                return;
            }

            const details = {
                title: card.dataset.bookTitle || '',
                author: card.dataset.bookAuthor || '',
                description: card.dataset.bookDescription || '',
                cover: card.dataset.bookCover || '',
                category: card.dataset.bookCategory || '',
                isbn: card.dataset.bookIsbn || '',
            };

            openBookDetailsModal(details);
        });

        // Auto-hide toast messages after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.toast').forEach(function(toast) {
                toast.style.opacity = '0';
                setTimeout(function() {
                    toast.remove();
                }, 300);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
