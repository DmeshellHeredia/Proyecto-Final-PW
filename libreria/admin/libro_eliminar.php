<?php
// ============================================================
//  admin/libro_eliminar.php — Confirmar y eliminar un libro
// ============================================================

// Incluye el archivo de autenticación
require_once __DIR__ . '/../helpers/auth.php';

// Incluye la configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador; si no, redirige al login
requerirAdmin('../login.php');

// Variables generales para la vista
$basePath  = '../';
$pageTitle = 'Eliminar Libro — Admin';

// Obtiene el ID del libro desde la URL
$idTitulo = trim($_GET['id'] ?? '');

// Variables para almacenar datos del libro y posibles errores
$libro    = null;
$error    = '';

// Si no se recibe ID, redirige al listado de libros
if ($idTitulo === '') {
    header('Location: libros.php');
    exit;
}

try {
    // Obtiene la conexión a la base de datos
    $pdo  = getConexion();

    // Busca el libro por ID para mostrar sus datos antes de eliminar
    $stmt = $pdo->prepare('SELECT id_titulo, titulo, tipo FROM titulos WHERE id_titulo = :id LIMIT 1');
    $stmt->execute([':id' => $idTitulo]);

    // Recupera los datos del libro
    $libro = $stmt->fetch();

    // Si el libro no existe, redirige al listado
    if (!$libro) {
        header('Location: libros.php');
        exit;
    }

} catch (PDOException $e) {
    // Si ocurre un error al consultar, redirige por seguridad
    header('Location: libros.php');
    exit;
}

// Confirmación de eliminación mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        // Prepara la consulta para eliminar el libro por ID
        $stmt = $pdo->prepare('DELETE FROM titulos WHERE id_titulo = :id');
        $stmt->execute([':id' => $idTitulo]);

        // Redirige al listado con mensaje de éxito
        header('Location: libros.php?ok=eliminado');
        exit;

    } catch (PDOException $e) {
        // Guarda el error en el log del servidor
        error_log('Error al eliminar libro: ' . $e->getMessage());

        // Mensaje amigable para mostrar en pantalla
        $error = 'No se pudo eliminar el libro. Puede tener datos relacionados (autores, ventas).';
    }
}

// Incluye cabecera y barra de navegación del panel admin
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-5" style="max-width:560px;">

    <!-- Migas de pan para navegación -->
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="libros.php">Libros</a></li>
            <li class="breadcrumb-item active">Eliminar</li>
        </ol>
    </nav>

    <!-- Tarjeta principal de confirmación -->
    <div class="form-card" style="border-top-color:#dc3545;">

        <!-- Encabezado visual de advertencia -->
        <div class="text-center mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:3rem;" aria-hidden="true"></i>
            <h2 class="mt-3 text-verde">Confirmar eliminación</h2>
        </div>

        <!-- Mostrar error si ocurrió un problema -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Información del libro a eliminar -->
        <p class="text-muted text-center mb-1">Estás a punto de eliminar el libro:</p>
        <div class="text-center mb-4">
            <strong class="text-verde" style="font-size:1.1rem;">
                <?= htmlspecialchars($libro['titulo']) ?>
            </strong>
            <br>
            <span class="badge-tipo mt-1 d-inline-block"><?= htmlspecialchars($libro['id_titulo']) ?></span>
        </div>

        <!-- Advertencia sobre relaciones en la base de datos -->
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-info-circle-fill me-1"></i>
            <strong>Nota:</strong> Si el libro tiene registros de autores o ventas relacionados, la eliminación podría fallar.
        </div>

        <!-- Formulario de confirmación -->
        <form method="POST" action="libro_eliminar.php?id=<?= urlencode($idTitulo) ?>">
            <div class="d-flex gap-2 mt-3">

                <!-- Botón para confirmar eliminación -->
                <button type="submit" name="confirmar" value="1" class="btn btn-danger flex-fill">
                    <i class="bi bi-trash me-2"></i>Sí, eliminar
                </button>

                <!-- Botón para cancelar -->
                <a href="libros.php" class="btn btn-outline-secondary flex-fill" style="border-radius:.5rem;">
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