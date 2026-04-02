<?php
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/config/database.php';

iniciarSesion();

if (estaAutenticado()) {
    header('Location: admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        $error = 'Completa todos los campos para continuar.';
    } else {
        try {
            $pdo  = getConexion();
            $stmt = $pdo->prepare('SELECT id, usuario, password_hash, nombre FROM usuarios WHERE usuario = :usuario LIMIT 1');
            $stmt->execute([':usuario' => $usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']      = $user['id'];
                $_SESSION['admin_usuario'] = $user['usuario'];
                $_SESSION['admin_nombre']  = $user['nombre'];
                header('Location: admin/index.php');
                exit;
            } else {
                // Mensaje genérico — no revelar si el usuario existe o no
                $error = 'Credenciales incorrectas. Verifica e inténtalo de nuevo.';
                error_log('[Login] Intento fallido para usuario: ' . htmlspecialchars($usuario));
                // NO repoblar ningún campo por seguridad
            }

        } catch (PDOException $e) {
            error_log('[Login] Error DB: ' . $e->getMessage());
            $error = 'Error de sistema. Inténtalo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Áurea — Acceso Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <style>
        :root { --verde-oscuro:#1a3a2a; --verde-claro:#2e7d52; --dorado:#c9a84c; --dorado-claro:#e8c97a; --crema:#fdf8f0; }
        body { font-family:'Source Sans 3',sans-serif; background:var(--verde-oscuro); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-fondo { position:fixed; inset:0; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%23ffffff' opacity='.04'/%3E%3C/svg%3E") repeat, linear-gradient(135deg,#1a3a2a 0%,#245c3c 100%); }
        .login-box { position:relative; z-index:2; background:var(--crema); border-radius:.75rem; padding:2.5rem 2rem; max-width:420px; width:100%; box-shadow:0 8px 40px rgba(0,0,0,.35); }
        .login-logo { text-align:center; margin-bottom:1.75rem; }
        .login-logo .bi { font-size:3rem; color:var(--verde-oscuro); }
        .login-logo h1 { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--verde-oscuro); margin-top:.5rem; margin-bottom:.15rem; }
        .login-logo p { color:#888; font-size:.85rem; margin:0; }
        .badge-admin { background:var(--verde-oscuro); color:var(--dorado-claro); font-size:.72rem; font-weight:600; padding:.2em .65em; border-radius:2rem; letter-spacing:.08em; text-transform:uppercase; }
        .form-label { font-weight:600; color:var(--verde-oscuro); font-size:.92rem; }
        .form-control { border:2px solid #e8e0d0; border-radius:.5rem; padding:.65rem .9rem; transition:border-color .2s; background:#fff; }
        .form-control:focus { border-color:var(--verde-claro); box-shadow:0 0 0 3px rgba(46,125,82,.15); }
        .btn-login { background:var(--verde-oscuro); color:#fff; border:none; border-radius:.5rem; padding:.7rem; font-weight:600; font-size:1rem; width:100%; transition:background .2s; cursor:pointer; }
        .btn-login:hover { background:var(--verde-claro); }
        .volver { text-align:center; margin-top:1.25rem; font-size:.88rem; }
        .volver a { color:var(--verde-claro); text-decoration:none; }
        .volver a:hover { text-decoration:underline; }
        *:focus-visible { outline:3px solid var(--dorado); outline-offset:3px; border-radius:3px; }
    </style>
</head>
<body>
<div class="login-fondo"></div>

<div class="login-box">
    <div class="login-logo">
        <i class="bi bi-book-half"></i>
        <h1>Librería Áurea</h1>
        <p>Acceso al panel de administración</p>
        <span class="badge-admin mt-1 d-inline-block">Admin</span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!--
      autocomplete="off" en el formulario para evitar que el navegador rellene
      automáticamente con credenciales guardadas.
    -->
    <form method="POST" action="login.php" novalidate autocomplete="off">

        <div class="mb-3">
            <label for="usuario" class="form-label">
                <i class="bi bi-person me-1"></i>Usuario
            </label>
            <input type="text"
                   id="usuario"
                   name="usuario"
                   class="form-control"
                   autocomplete="off"
                   autofocus
                   required
                   placeholder="Ingresa tu usuario">
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-1"></i>Contraseña
            </label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   autocomplete="new-password"
                   required
                   placeholder="Ingresa tu contraseña">
        </div>

        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
        </button>

    </form>

    <div class="volver">
        <a href="index.php"><i class="bi bi-arrow-left me-1"></i>Volver al sitio público</a>
    </div>
</div>

</body>
</html>
