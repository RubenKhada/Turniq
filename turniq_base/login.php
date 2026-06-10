<?php
/*
 * login.php — Pantalla de inicio de sesión
 * Punto de entrada al sistema
 */
require_once 'config/app.php';
require_once 'config/database.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Por favor ingresa tu correo y contraseña.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, role, password_hash FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $db->close();
            redirect('dashboard.php');
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turniq — Iniciar sesión</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/ui/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/theme.php">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg); }
        .login-wrap { width:100%; max-width:400px; padding:24px; display:flex; flex-direction:column; align-items:center; gap:28px; }
        .login-logo { display:flex; flex-direction:column; align-items:center; gap:8px; }
        .login-logo svg { width:72px; height:72px; }
        .login-logo-text { font-size:32px; font-weight:200; letter-spacing:-1px; color:#fff; }
        .login-logo-text span { color:var(--accent); }
        .login-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-xl); padding:32px; width:100%; }
        .login-card h2 { font-size:17px; font-weight:400; color:var(--text); margin-bottom:22px; text-align:center; }
        .login-version { font-size:11px; color:var(--text-dim); letter-spacing:3px; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-logo">
        <svg viewBox="0 0 680 420" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(340,210)">
                <polygon points="0,-110 95,-55 95,55 0,110 -95,55 -95,-55" fill="#1A0A2E" stroke="#2E1060" stroke-width="1"/>
                <polygon points="0,-110 95,-55 0,-10 -95,-55" fill="#2D1260"/>
                <polygon points="95,-55 95,55 0,110 0,-10" fill="#130824"/>
                <polygon points="-95,-55 0,-10 0,110 -95,55" fill="#1F0D40"/>
                <polygon points="0,-70 60,-35 60,35 0,70 -60,35 -60,-35" fill="#1A0A2E"/>
                <polygon points="0,-70 60,-35 0,0 -60,-35" fill="#4A1A8C"/>
                <polygon points="60,-35 60,35 0,70 0,0" fill="#1E0840"/>
                <polygon points="-60,-35 0,0 0,70 -60,35" fill="#311060"/>
                <polygon points="0,-30 25,-15 25,15 0,30 -25,15 -25,-15" fill="#1A0A2E"/>
                <polygon points="0,-30 25,-15 0,0 -25,-15" fill="#9D4EDD"/>
                <polygon points="25,-15 25,15 0,30 0,0" fill="#3A1272"/>
                <polygon points="-25,-15 0,0 0,30 -25,15" fill="#5A1EA8"/>
            </g>
        </svg>
        <div class="login-logo-text">Turn<span>iq</span></div>
    </div>
    <div class="login-card">
        <h2>Iniciar sesión</h2>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" placeholder="admin@turniq.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary w-100" style="margin-top:4px;justify-content:center;padding:12px">
                Entrar
            </button>
        </form>
    </div>
    <div class="login-version">TURNIQ v<?= APP_VERSION ?></div>
</div>
</body>
</html>