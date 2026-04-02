<?php
// ============================================================
//  autor_editar.php — Editar un autor existente
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables básicas de la vista
$basePath  = '../';
$pageTitle = 'Editar Autor — Admin';
$errores   = [];

// Obtener ID del autor desde la URL (?id=...)
$idAutor   = trim($_GET['id'] ?? '');

// Si no hay ID, redirige
if ($idAutor === '') {
    header('Location: autores.php');
    exit;
}

// =========================
// OBTENER DATOS DEL AUTOR
// =========================
try {
    $pdo  = getConexion();

    // Buscar autor por ID
    $stmt = $pdo->prepare('SELECT * FROM autores WHERE id_autor = :id LIMIT 1');
    $stmt->execute([':id' => $idAutor]);

    // Obtener resultado
    $autor = $stmt->fetch();

    // Si no existe, redirige
    if (!$autor) {
        header('Location: autores.php');
        exit;
    }

} catch (PDOException $e) {
    // Si ocurre error, redirige por seguridad
    header('Location: autores.php');
    exit;
}

// =========================
// VALORES INICIALES DEL FORM
// =========================
$v = [
    'apellido'   => $autor['apellido'],
    'nombre'     => $autor['nombre'],
    'telefono'   => $autor['telefono'],
    'direccion'  => $autor['direccion'],
    'ciudad'     => $autor['ciudad'],
    'estado'     => $autor['estado'],
    'pais'       => $autor['pais'],
    'cod_postal' => $autor['cod_postal'],
];

// =========================
// PROCESAR FORMULARIO
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoger y limpiar datos del formulario
    $v = [
        'apellido'   => trim($_POST['apellido']   ?? ''),
        'nombre'     => trim($_POST['nombre']     ?? ''),
        'telefono'   => trim($_POST['telefono']   ?? ''),
        'direccion'  => trim($_POST['direccion']  ?? ''),
        'ciudad'     => trim($_POST['ciudad']     ?? ''),
        'estado'     => trim($_POST['estado']     ?? ''),
        'pais'       => trim($_POST['pais']       ?? ''),
        'cod_postal' => trim($_POST['cod_postal'] ?? ''),
    ];

    // =========================
    // VALIDACIONES
    // =========================

    if ($v['nombre'] === '')
        $errores[] = 'El nombre es obligatorio.';

    if (strlen($v['nombre']) > 15)
        $errores[] = 'El nombre no puede superar 15 caracteres.';

    if ($v['apellido'] === '')
        $errores[] = 'El apellido es obligatorio.';

    if (strlen($v['apellido']) > 15)
        $errores[] = 'El apellido no puede superar 15 caracteres.';

    if ($v['ciudad'] === '')
        $errores[] = 'La ciudad es obligatoria.';

    if ($v['pais'] === '')
        $errores[] = 'El país es obligatorio.';

    // =========================
    // ACTUALIZAR EN BD
    // =========================

    if (empty($errores)) {
        try {
            // Preparar consulta UPDATE
            $stmt = $pdo->prepare(
                "UPDATE autores SET 
                    apellido   = :apellido,
                    nombre     = :nombre,
                    telefono   = :telefono,
                    direccion  = :direccion,
                    ciudad     = :ciudad,
                    estado     = :estado,
                    pais       = :pais,
                    cod_postal = :cod_postal
                 WHERE id_autor = :id_autor"
            );

            // Ejecutar con parámetros
            $stmt->execute([
                ':apellido'   => $v['apellido'],
                ':nombre'     => $v['nombre'],
                ':telefono'   => $v['telefono'],
                ':direccion'  => $v['direccion'],
                ':ciudad'     => $v['ciudad'],
                ':estado'     => $v['estado'],
                ':pais'       => $v['pais'],
                ':cod_postal' => (int)$v['cod_postal'],
                ':id_autor'   => $idAutor,
            ]);

            // Redirigir al listado con mensaje de éxito
            header('Location: autores.php?ok=editado');
            exit;

        } catch (PDOException $e) {
            // Registrar error y mostrar mensaje
            error_log('Error al editar autor: ' . $e->getMessage());
            $errores[] = 'Error al guardar los cambios.';
        }
    }
}

// =========================
// VISTA
// =========================

// Cabecera y navbar
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-4" style="max-width:720px;">

    <!-- Breadcrumb -->
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="autores.php">Autores</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <!-- Título -->
    <h1 class="section-title mb-1">
        <i class="bi bi-pencil-square me-2"></i>Editar autor
    </h1>

    <!-- Mostrar ID -->
    <p class="text-muted mb-4 small">
        ID: <code><?= htmlspecialchars($idAutor) ?></code>
    </p>

    <!-- Mostrar errores -->
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <strong>Errores:</strong>
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" action="autor_editar.php?id=<?= urlencode($idAutor) ?>">
        <!-- Inputs con htmlspecialchars para evitar XSS -->
        <!-- Botones: Guardar cambios / Cancelar -->
    </form>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>