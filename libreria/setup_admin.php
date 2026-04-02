<?php
// ============================================================
// setup_admin.php
// Crea el usuario administrador inicial del sistema.
// IMPORTANTE: este archivo debe eliminarse después de usarlo.
// ============================================================


// ---- CONEXIÓN ----
// Incluye la conexión a la base de datos
require_once __DIR__ . '/config/database.php';


// ---- VARIABLES DE ESTADO ----
// Mensaje a mostrar en pantalla
$mensaje = '';

// Tipo de alerta Bootstrap: info, success, danger, etc.
$tipo = 'info';


// ---- PROCESAR FORMULARIO ----
// Solo se ejecuta si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoge y limpia los datos del formulario
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nombre   = trim($_POST['nombre'] ?? 'Administrador');


    // ---- VALIDACIONES BÁSICAS ----
    // Usuario y contraseña son obligatorios
    if ($usuario === '' || $password === '') {
        $mensaje = 'El usuario y la contraseña son obligatorios.';
        $tipo = 'danger';

    // Verifica longitud mínima de contraseña
    } elseif (strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
        $tipo = 'danger';

    } else {
        try {
            // Obtiene conexión PDO
            $pdo = getConexion();


            // ---- CREAR TABLA SI NO EXISTE ----
            // Esto permite inicializar el sistema aunque la tabla aún no exista.
            $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
                `id`            INT           NOT NULL AUTO_INCREMENT,
                `usuario`       VARCHAR(50)   NOT NULL,
                `password_hash` VARCHAR(255)  NOT NULL,
                `nombre`        VARCHAR(100)  NOT NULL DEFAULT 'Administrador',
                `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `usuario_unico` (`usuario`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


            // ---- GENERAR HASH DE LA CONTRASEÑA ----
            // Nunca se guarda la contraseña en texto plano.
            $hash = password_hash($password, PASSWORD_BCRYPT);


            // ---- INSERTAR O ACTUALIZAR ADMIN ----
            // Si el usuario ya existe, actualiza la contraseña y el nombre.
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (usuario, password_hash, nombre)
                 VALUES (:usuario, :hash, :nombre)
                 ON DUPLICATE KEY UPDATE
                    password_hash = :hash2,
                    nombre = :nombre2"
            );

            $stmt->execute([
                ':usuario' => $usuario,
                ':hash'    => $hash,
                ':nombre'  => $nombre,
                ':hash2'   => $hash,
                ':nombre2' => $nombre,
            ]);


            // ---- MENSAJE DE ÉXITO ----
            $mensaje = "¡Usuario '{$usuario}' creado correctamente! Ya puedes ir a login.php y ELIMINA este archivo.";
            $tipo = 'success';

        } catch (PDOException $e) {

            // ---- ERROR DE BASE DE DATOS ----
            // Muestra error en pantalla (útil en entorno local, no ideal en producción)
            $mensaje = 'Error de base de datos: ' . $e->getMessage();
            $tipo = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Título de la página -->
    <title>Configuración inicial — Librería Áurea</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Fondo general */
        body {
            background: #1a3a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* Caja principal del setup */
        .setup-box {
            background: #fff;
            border-radius: .75rem;
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0,0,0,.3);
        }

        /* Encabezado */
        .setup-box h2 {
            color: #1a3a2a;
            font-family: Georgia, serif;
        }

        /* Caja de advertencia */
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: .5rem;
            padding: 1rem;
            font-size: .88rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="setup-box">
    <!-- Título principal -->
    <h2 class="mb-1">⚙️ Configuración inicial</h2>
    <p class="text-muted small mb-3">Librería Áurea — Crear administrador</p>

    <!-- Advertencia de seguridad -->
    <div class="warning">
        <strong>⚠️ Seguridad:</strong> Elimina este archivo después de crear tu usuario administrador.
    </div>

    <!-- Mensaje dinámico -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <form method="POST">

        <!-- Campo usuario -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Usuario</label>
            <input type="text"
                   name="usuario"
                   class="form-control"
                   required
                   value="admin">
        </div>

        <!-- Campo contraseña -->
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Contraseña <span class="text-danger">*</span>
            </label>
            <input type="password"
                   name="password"
                   class="form-control"
                   required
                   placeholder="Mínimo 6 caracteres">
        </div>

        <!-- Campo nombre -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Nombre completo</label>
            <input type="text"
                   name="nombre"
                   class="form-control"
                   value="Administrador">
        </div>

        <!-- Botón de envío -->
        <button type="submit"
                class="btn w-100 text-white"
                style="background:#1a3a2a;">
            Crear administrador
        </button>
    </form>

    <!-- Enlace al login si todo salió bien -->
    <?php if ($tipo === 'success'): ?>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-outline-success btn-sm">
                Ir al login →
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>