<?php
// ============================================================
//  admin/libro_crear.php — Formulario para crear un nuevo libro
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Crear Libro — Admin';

// Variables principales
$errores      = []; // Lista de errores de validación
$publicadores = []; // Lista de editoriales
$ok           = false;

// Tipos de libros disponibles (clave => nombre visible)
$tiposES = [
    'business'     => 'Negocios',
    'mod_cook'     => 'Cocina Moderna',
    'trad_cook'    => 'Cocina Tradicional',
    'popular_comp' => 'Informática',
    'psychology'   => 'Psicología',
    'UNDECIDED'    => 'Sin categoría'
];

// =========================
// CARGAR EDITORIALES
// =========================
try {
    $pdo = getConexion();

    // Obtener lista de publicadores (editoriales)
    $publicadores = $pdo->query(
        'SELECT id_pub, nombre_pub FROM publicadores ORDER BY nombre_pub'
    )->fetchAll();

} catch (PDOException $e) {
    // Si falla, mostrar error
    $errores[] = 'No se pudieron cargar las editoriales.';
}

// =========================
// VALORES INICIALES DEL FORM
// =========================
$v = [
    'id_titulo'    => '',
    'titulo'       => '',
    'tipo'         => '',
    'id_pub'       => '',
    'precio'       => '',
    'avance'       => '',
    'total_ventas' => '',
    'notas'        => '',
    'fecha_pub'    => date('Y-m-d'), // fecha actual por defecto
    'contrato'     => '1',           // marcado por defecto
];

// =========================
// PROCESAR FORMULARIO
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leer y limpiar datos del formulario
    $v = [
        'id_titulo'    => trim($_POST['id_titulo']    ?? ''),
        'titulo'       => trim($_POST['titulo']       ?? ''),
        'tipo'         => trim($_POST['tipo']         ?? ''),
        'id_pub'       => trim($_POST['id_pub']       ?? ''),
        'precio'       => trim($_POST['precio']       ?? ''),
        'avance'       => trim($_POST['avance']       ?? ''),
        'total_ventas' => trim($_POST['total_ventas'] ?? ''),
        'notas'        => trim($_POST['notas']        ?? ''),
        'fecha_pub'    => trim($_POST['fecha_pub']    ?? ''),
        'contrato'     => isset($_POST['contrato']) ? '1' : '0', // checkbox
    ];

    // =========================
    // VALIDACIONES
    // =========================

    if ($v['id_titulo'] === '')
        $errores[] = 'El ID del título es obligatorio.';

    if (strlen($v['id_titulo']) > 6)
        $errores[] = 'El ID no puede tener más de 6 caracteres.';

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
    // INSERTAR EN BD
    // =========================

    if (empty($errores)) {
        try {
            // Preparar consulta INSERT
            $stmt = $pdo->prepare(
                "INSERT INTO titulos 
                (id_titulo, titulo, tipo, id_pub, precio, avance, total_ventas, notas, fecha_pub, contrato)
                VALUES 
                (:id_titulo, :titulo, :tipo, :id_pub, :precio, :avance, :total_ventas, :notas, :fecha_pub, :contrato)"
            );

            // Ejecutar consulta con datos
            $stmt->execute([

                // Convertir ID a mayúsculas
                ':id_titulo'    => strtoupper($v['id_titulo']),

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
            ]);

            // Redirigir al listado con mensaje de éxito
            header('Location: libros.php?ok=creado');
            exit;

        } catch (PDOException $e) {

            // Error por ID duplicado
            if ($e->getCode() === '23000') {
                $errores[] = 'Ya existe un libro con el ID "' . htmlspecialchars(strtoupper($v['id_titulo'])) . '".';
            } else {
                // Registrar error en logs
                error_log('Error en libro_crear.php: ' . $e->getMessage());

                $errores[] = 'Error al guardar el libro. Inténtalo de nuevo.';
            }
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
            <li>Crear libro</li>
        </ol>
    </nav>

    <!-- Título -->
    <h1>Crear nuevo libro</h1>

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

        <!-- Inputs del formulario -->
        <!-- Se usa htmlspecialchars para evitar XSS -->

        <!-- Botones -->
        <!-- Guardar / Cancelar -->

    </form>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>