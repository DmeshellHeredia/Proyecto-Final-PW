<?php
// Incluye el archivo de autenticación para validar acceso
require_once __DIR__ . '/../helpers/auth.php';

// Incluye la configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador; si no, redirige al login
requerirAdmin('../login.php');

// Variables generales para la vista
$basePath  = '../';
$pageTitle = 'Eliminar Autor — Admin';

// Obtiene el ID del autor desde la URL
$idAutor = trim($_GET['id'] ?? '');

// Variable para mostrar errores en pantalla
$error   = '';

// Si no se recibe ID, redirige al listado de autores
if ($idAutor === '') {
    header('Location: autores.php');
    exit;
}

try {
    // Obtiene la conexión a la base de datos
    $pdo  = getConexion();

    // Busca el autor por ID para mostrar confirmación antes de eliminar
    $stmt = $pdo->prepare('SELECT id_autor, nombre, apellido FROM autores WHERE id_autor = :id LIMIT 1');
    $stmt->execute([':id' => $idAutor]);

    // Recupera los datos del autor
    $autor = $stmt->fetch();

    // Si el autor no existe, vuelve al listado
    if (!$autor) {
        header('Location: autores.php');
        exit;
    }
} catch (PDOException $e) {
    // Si ocurre un error en la consulta, redirige por seguridad
    header('Location: autores.php');
    exit;
}

// Si el formulario fue enviado y se confirmó la eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        // Prepara la consulta para eliminar el autor por ID
        $stmt = $pdo->prepare('DELETE FROM autores WHERE id_autor = :id');
        $stmt->execute([':id' => $idAutor]);

        // Redirige al listado con mensaje de éxito
        header('Location: autores.php?ok=eliminado');
        exit;
    } catch (PDOException $e) {
        // Guarda el error en el log del servidor
        error_log('Error al eliminar autor: ' . $e->getMessage());

        // Mensaje amigable para mostrar al usuario
        $error = 'No se pudo eliminar el autor. Puede tener registros relacionados.';
    }
}

// Incluye la cabecera y la barra de navegación del panel admin
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-5" style="max-width:560px;">

    <!-- Migas de pan para navegación -->
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="autores.php">Autores</a></li>
            <li class="breadcrumb-item active">Eliminar</li>
        </ol>
    </nav>

    <!-- Tarjeta de confirmación con estilo de peligro -->
    <div class="form-card" style="border-top-color:#dc3545;">
        <div class="text-center mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:3rem;"></i>
            <h2 class="mt-3 text-verde">Confirmar eliminación</h2>
        </div>

        <!-- Muestra error si ocurrió algún problema al eliminar -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Muestra el autor que será eliminado -->
        <p class="text-muted text-center mb-1">Estás a punto de eliminar al autor:</p>
        <div class="text-center mb-4">
            <strong class="text-verde" style="font-size:1.1rem;">
                <?= htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']) ?>
            </strong>
            <br>
            <span class="badge-tipo mt-1 d-inline-block"><?= htmlspecialchars($idAutor) ?></span>
        </div>

        <!-- Advertencia sobre relaciones en base de datos -->
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-info-circle-fill me-1"></i>
            Si el autor tiene títulos relacionados, la eliminación podría fallar.
        </div>

        <!-- Formulario de confirmación -->
        <form method="POST" action="autor_eliminar.php?id=<?= urlencode($idAutor) ?>">
            <div class="d-flex gap-2 mt-3">
                <!-- Botón para confirmar eliminación -->
                <button type="submit" name="confirmar" value="1" class="btn btn-danger flex-fill">
                    <i class="bi bi-trash me-2"></i>Sí, eliminar
                </button>

                <!-- Botón para cancelar y volver al listado -->
                <a href="autores.php" class="btn btn-outline-secondary flex-fill" style="border-radius:.5rem;">
                    <i class="bi bi-x-lg me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

<?php
// Incluye el pie de página del panel admin
require_once __DIR__ . '/../includes/admin_footer.php';
?>