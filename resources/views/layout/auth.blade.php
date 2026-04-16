<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libraria - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --teal-dark: #1a3a3a; --teal-mid: #2d5a5a; --teal-accent: #3d7a6e;
            --gold: #c9a84c; --gold-light: #e8c97a;
            --cream: #f5f0e8; --white: #ffffff;
            --text-dark: #1a2520; --text-mid: #4a5550; --text-light: #8a9590;
            --danger: #c0392b; --danger-light: #fde8e6;
            --radius: 8px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--teal-dark);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(ellipse at 20% 50%, rgba(61,122,110,0.3) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.15) 0%, transparent 50%);
        }
        .auth-container {
            background: var(--white); border-radius: 16px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.4);
            overflow: hidden; width: 100%; max-width: 440px;
        }
        .auth-header {
            background: var(--teal-dark); padding: 32px 36px 24px;
            text-align: center;
        }
        .auth-logo {
            width: 52px; height: 52px; border-radius: 12px;
            background: var(--gold); margin: 0 auto 12px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif;
            font-size: 24px; color: var(--teal-dark); font-weight: 700;
        }
        .auth-brand { font-family: 'DM Serif Display', serif; color: var(--white); font-size: 22px; margin-bottom: 4px; }
        .auth-tagline { color: rgba(255,255,255,0.55); font-size: 12px; }
        .auth-body { padding: 28px 36px 32px; }
        .auth-title { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
        .auth-subtitle { font-size: 13px; color: var(--text-light); margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-mid); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid rgba(0,0,0,0.12);
            border-radius: var(--radius); font-family: inherit; font-size: 14px;
            color: var(--text-dark); outline: none; transition: all .15s;
        }
        .form-control:focus { border-color: var(--teal-accent); box-shadow: 0 0 0 3px rgba(61,122,110,0.1); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 12px; color: var(--danger); margin-top: 4px; }
        .btn-auth {
            width: 100%; padding: 12px;
            background: var(--teal-dark); color: var(--white);
            border: none; border-radius: var(--radius);
            font-family: inherit; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all .15s; margin-top: 4px;
        }
        .btn-auth:hover { background: var(--teal-mid); }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-light); }
        .auth-footer a { color: var(--teal-accent); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }
        .theme-toggle {
            position: fixed; top: 16px; right: 16px; z-index: 30;
            width: 40px; height: 40px; border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.12);
            color: var(--white); display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .15s;
        }
        .theme-toggle:hover { background: rgba(255,255,255,0.18); }
        body[data-theme='light'] {
            background: #eef8f6;
            color: #1a2520;
            --teal-dark: #1a3a3a;
            --teal-mid: #2d5a5a;
            --teal-accent: #3d7a6e;
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --cream: #ffffff;
            --white: #ffffff;
            --text-dark: #1a2520;
            --text-mid: #5f705f;
            --text-light: #8a9590;
            --danger-light: #fde8e6;
        }
        .alert-danger {
            background: var(--danger-light); color: var(--danger);
            padding: 10px 14px; border-radius: var(--radius);
            font-size: 13px; margin-bottom: 16px;
            border: 1px solid rgba(192,57,43,0.2);
        }
        .divider { display: flex; align-items: center; gap: 10px; margin: 20px 0; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background: rgba(0,0,0,0.1); }
        .divider span { font-size: 12px; color: var(--text-light); }
    </style>
</head>
<body>
<button id="themeToggleButton" class="theme-toggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme">
    <i id="themeToggleIcon" class="fas fa-sun"></i>
</button>
<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">L</div>
        <div class="auth-brand">Libraria</div>
        <div class="auth-tagline">Library Management System</div>
    </div>
    <div class="auth-body">
        @if($errors->any())
            <div class="alert-danger">
                @foreach($errors->all() as $err) {{ $err }}<br> @endforeach
            </div>
        @endif
        @yield('content')
    </div>
</div>
<script>
const uiThemeKey = 'ui-theme';
function setTheme(theme) {
    document.body.dataset.theme = theme;
    localStorage.setItem(uiThemeKey, theme);
    const icon = document.getElementById('themeToggleIcon');
    if (!icon) return;
    icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
}
function toggleTheme() {
    setTheme(document.body.dataset.theme === 'light' ? 'dark' : 'light');
}
(function() {
    const saved = localStorage.getItem(uiThemeKey);
    const defaultTheme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    setTheme(defaultTheme);
})();
</script>
</body>
</html>
