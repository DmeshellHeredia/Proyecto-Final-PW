<?php
// ============================================================
//  admin/libro_editar.php — Editar un libro existente
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Editar Libro — Admin';

// Variables principales
$errores      = [];
$publicadores = [];
$libro        = null;

// Obtener ID del libro desde la URL
$idTitulo     = trim($_GET['id'] ?? '');

// Tipos de categorías disponibles
$tiposES = [
    'business'=>'Negocios',
    'mod_cook'=>'Cocina Moderna',
    'trad_cook'=>'Cocina Tradicional',
    'popular_comp'=>'Informática',
    'psychology'=>'Psicología',
    'UNDECIDED'=>'Sin categoría'
];

// Si no hay ID, redirige
if ($idTitulo === '') {
    header('Location: libros.php');
    exit;
}

// =========================
// CARGAR DATOS DEL LIBRO
// =========================
try {
    $pdo = getConexion();

    // Obtener lista de editoriales
    $publicadores = $pdo->query(
        'SELECT id_pub, nombre_pub FROM publicadores ORDER BY nombre_pub'
    )->fetchAll();

    // Buscar libro por ID
    $stmt = $pdo->prepare('SELECT * FROM titulos WHERE id_titulo = :id LIMIT 1');
    $stmt->execute([':id' => $idTitulo]);

    $libro = $stmt->fetch();

    // Si no existe, redirige
    if (!$libro) {
        header('Location: libros.php');
        exit;
    }

} catch (PDOException $e) {
    // Registrar error y redirigir
    error_log('Error al cargar libro en editar: ' . $e->getMessage());
    header('Location: libros.php');
    exit;
}

// =========================
// VALORES INICIALES DEL FORM
// =========================
$v = [
    'titulo'       => $libro['titulo'],
    'tipo'         => $libro['tipo'],
    'id_pub'       => $libro['id_pub'],
    'precio'       => $libro['precio'] ?? '',
    'avance'       => $libro['avance'] ?? '',
    'total_ventas' => $libro['total_ventas'] ?? '',
    'notas'        => $libro['notas'],
    
    // Convertir fecha DATETIME a formato YYYY-MM-DD
    'fecha_pub'    => $libro['fecha_pub'] 
                        ? date('Y-m-d', strtotime($libro['fecha_pub'])) 
                        : '',
    
    'contrato'     => $libro['contrato'],
];

// =========================
// PROCESAR FORMULARIO
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leer y limpiar datos
    $v = [
        'titulo'       => trim($_POST['titulo']       ?? ''),
        'tipo'         => trim($_POST['tipo']         ?? ''),
        'id_pub'       => trim($_POST['id_pub']       ?? ''),
        'precio'       => trim($_POST['precio']       ?? ''),
        'avance'       => trim($_POST['avance']       ?? ''),
        'total_ventas' => trim($_POST['total_ventas'] ?? ''),
        'notas'        => trim($_POST['notas']        ?? ''),
        'fecha_pub'    => trim($_POST['fecha_pub']    ?? ''),
        'contrato'     => isset($_POST['contrato']) ? '1' : '0',
    ];

    // =========================
    // VALIDACIONES
    // =========================

    if ($v['titulo'] === '')
        $errores[] = 'El título es obligatorio.';

    if (strlen($v['titulo']) > 60)
        $errores[] = 'El título no puede superar 60 caracteres.';

    if ($v['tipo'] === '')
        $errores[] = 'Debes seleccionar un tipo.';

    if ($v['id_pub'] === '')
        $errores[] = 'Debes seleccionar una editorial.';

    if ($v['fecha_pub'] === '')
        $errores[] = 'La fecha de publicación es obligatoria.';

    // =========================
    // ACTUALIZAR EN BD
    // =========================

    if (empty($errores)) {
        try {
            // Preparar consulta UPDATE
            $stmt = $pdo->prepare(
                "UPDATE titulos SET
                    titulo       = :titulo,
                    tipo         = :tipo,
                    id_pub       = :id_pub,
                    precio       = :precio,
                    avance       = :avance,
                    total_ventas = :total_ventas,
                    notas        = :notas,
                    fecha_pub    = :fecha_pub,
                    contrato     = :contrato
                 WHERE id_titulo = :id_titulo"
            );

            // Ejecutar consulta
            $stmt->execute([
                ':titulo'       => $v['titulo'],
                ':tipo'         => $v['tipo'],
                ':id_pub'       => $v['id_pub'],

                // Convertir valores numéricos o null
                ':precio'       => $v['precio'] !== '' ? (float)$v['precio'] : null,
                ':avance'       => $v['avance'] !== '' ? (float)$v['avance'] : null,
                ':total_ventas' => $v['total_ventas'] !== '' ? (int)$v['total_ventas'] : null,

                ':notas'        => $v['notas'],

                // Agrega hora fija a la fecha
                ':fecha_pub'    => $v['fecha_pub'] . ' 12:00:00',

                ':contrato'     => $v['contrato'],

                // ID del libro a actualizar
                ':id_titulo'    => $idTitulo,
            ]);

            // Redirigir con mensaje de éxito
            header('Location: libros.php?ok=editado');
            exit;

        } catch (PDOException $e) {
            // Registrar error
            error_log('Error al editar libro: ' . $e->getMessage());

            $errores[] = 'Error al guardar los cambios. Inténtalo de nuevo.';
        }
    }
}

// Incluir layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-4" style="max-width:780px;">

    <!-- Breadcrumb -->
    <nav>
        <ol class="breadcrumb">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="libros.php">Libros</a></li>
            <li>Editar</li>
        </ol>
    </nav>

    <!-- Título -->
    <h1>Editar libro</h1>

    <!-- Mostrar ID -->
    <p>ID: <code><?= htmlspecialchars($idTitulo) ?></code></p>

    <!-- Mostrar errores -->
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST">

        <!-- Inputs con htmlspecialchars para evitar XSS -->

        <!-- Botones: Guardar / Cancelar -->

    </form>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>