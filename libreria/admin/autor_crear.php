<?php
// ============================================================
//  admin/autor_crear.php — Formulario para crear un nuevo autor
// ============================================================

// Incluye archivo de autenticación (control de sesiones/roles)
require_once __DIR__ . '/../helpers/auth.php';

// Incluye configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador, si no redirige al login
requerirAdmin('../login.php');

// Variables básicas para la vista
$basePath  = '../';
$pageTitle = 'Crear Autor — Admin';

// Array para almacenar errores de validación
$errores   = [];

// Valores iniciales del formulario (para mantener datos si hay errores)
$v = [
    'id_autor'=>'',
    'apellido'=>'',
    'nombre'=>'',
    'telefono'=>'',
    'direccion'=>'',
    'ciudad'=>'',
    'estado'=>'',
    'pais'=>'USA',
    'cod_postal'=>''
];

// Verifica si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoge y limpia los datos enviados desde el formulario
    $v = [
        'id_autor'   => trim($_POST['id_autor']   ?? ''),
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

    // Validar ID obligatorio
    if ($v['id_autor'] === '')
        $errores[] = 'El ID del autor es obligatorio.';

    // Validar formato del ID (tipo SSN: 123-45-6789)
    if (!preg_match('/^\d{3}-\d{2}-\d{4}$/', $v['id_autor']))
        $errores[] = 'El ID debe tener el formato 123-45-6789.';

    // Validar nombre obligatorio
    if ($v['nombre'] === '')
        $errores[] = 'El nombre es obligatorio.';

    // Validar longitud del nombre
    if (strlen($v['nombre']) > 15)
        $errores[] = 'El nombre no puede superar 15 caracteres.';

    // Validar apellido obligatorio
    if ($v['apellido'] === '')
        $errores[] = 'El apellido es obligatorio.';

    // Validar longitud del apellido
    if (strlen($v['apellido']) > 15)
        $errores[] = 'El apellido no puede superar 15 caracteres.';

    // Validar ciudad obligatoria
    if ($v['ciudad'] === '')
        $errores[] = 'La ciudad es obligatoria.';

    // Validar país obligatorio
    if ($v['pais'] === '')
        $errores[] = 'El país es obligatorio.';

    // =========================
    // INSERT EN BD
    // =========================

    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Obtener conexión PDO
            $pdo  = getConexion();

            // Preparar consulta SQL (evita inyección SQL)
            $stmt = $pdo->prepare(
                "INSERT INTO autores 
                (id_autor, apellido, nombre, telefono, direccion, ciudad, estado, pais, cod_postal)
                VALUES 
                (:id_autor, :apellido, :nombre, :telefono, :direccion, :ciudad, :estado, :pais, :cod_postal)"
            );

            // Ejecutar consulta con parámetros
            $stmt->execute([
                ':id_autor'   => $v['id_autor'],
                ':apellido'   => $v['apellido'],
                ':nombre'     => $v['nombre'],
                ':telefono'   => $v['telefono'],
                ':direccion'  => $v['direccion'],
                ':ciudad'     => $v['ciudad'],
                ':estado'     => $v['estado'],
                ':pais'       => $v['pais'],
                ':cod_postal' => (int)$v['cod_postal'], // conversión a entero
            ]);

            // Redirigir después de guardar correctamente
            header('Location: autores.php?ok=creado');
            exit;

        } catch (PDOException $e) {

            // Error de clave duplicada (ID existente)
            if ($e->getCode() === '23000') {
                $errores[] = 'Ya existe un autor con el ID "' . htmlspecialchars($v['id_autor']) . '".';
            } else {
                // Registrar error en log del servidor
                error_log('Error en autor_crear.php: ' . $e->getMessage());
                $errores[] = 'Error al guardar el autor.';
            }
        }
    }
}

// Incluye cabecera y navbar del admin
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-4" style="max-width:720px;">

    <!-- Breadcrumb (navegación) -->
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="autores.php">Autores</a></li>
            <li class="breadcrumb-item active">Crear autor</li>
        </ol>
    </nav>

    <!-- Título -->
    <h1 class="section-title mb-4">
        <i class="bi bi-person-plus me-2"></i>Crear nuevo autor
    </h1>

    <!-- Mostrar errores si existen -->
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
    <div class="form-card">
        <form method="POST" action="autor_crear.php" novalidate>

            <!-- Campos del formulario -->
            <!-- Cada input usa htmlspecialchars para evitar XSS -->

            <!-- Botones -->
            <!-- Guardar y Cancelar -->

        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>