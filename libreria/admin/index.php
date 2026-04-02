<?php
// ============================================================
//  admin/index.php — Dashboard principal del administrador
// ============================================================

// Incluye autenticación y conexión a la BD
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Dashboard — Admin';

// Variables para estadísticas
$totalLibros = 0;
$totalAutores = 0;
$totalMensajes = 0;

// Lista de mensajes recientes
$mensajesRecientes = [];

// Variable de error
$errorDB = '';

try {
    // Obtener conexión
    $pdo = getConexion();

    // =========================
    // CONSULTAS DE RESUMEN
    // =========================

    // Total de libros
    $totalLibros = $pdo->query('SELECT COUNT(*) FROM titulos')->fetchColumn();

    // Total de autores
    $totalAutores = $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();

    // Total de mensajes
    $totalMensajes = $pdo->query('SELECT COUNT(*) FROM contacto')->fetchColumn();

    // Últimos 5 mensajes
    $mensajesRecientes = $pdo->query(
        "SELECT id, fecha, nombre, correo, asunto 
         FROM contacto 
         ORDER BY fecha DESC 
         LIMIT 5"
    )->fetchAll();

} catch (PDOException $e) {

    // Registrar error en logs
    error_log('Error en admin/index.php: ' . $e->getMessage());

    // Mensaje para el usuario
    $errorDB = 'Error al cargar los datos del panel.';
}

// Incluir layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <!-- =========================
         ENCABEZADO
         ========================= -->

    <div class="mb-4">
        <h1 class="section-title mb-1">
            Dashboard
        </h1>

        <!-- Muestra nombre del admin y fecha actual -->
        <p class="text-muted small mb-0">
            Bienvenido, <strong><?= nombreAdmin() ?></strong>
            — <?= date('d/m/Y H:i') ?>
        </p>
    </div>

    <!-- Mostrar error si ocurre -->
    <?php if ($errorDB): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errorDB) ?>
        </div>
    <?php endif; ?>

    <!-- =========================
         TARJETAS DE RESUMEN
         ========================= -->

    <div class="row g-4 mb-4">

        <!-- Tarjeta: Libros -->
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div><?= $totalLibros ?></div>
                <div>Libros registrados</div>

                <!-- Enlace a gestión -->
                <a href="libros.php">Gestionar</a>
            </div>
        </div>

        <!-- Tarjeta: Autores -->
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div><?= $totalAutores ?></div>
                <div>Autores registrados</div>
                <a href="autores.php">Gestionar</a>
            </div>
        </div>

        <!-- Tarjeta: Mensajes -->
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div><?= $totalMensajes ?></div>
                <div>Mensajes recibidos</div>
                <a href="mensajes.php">Ver mensajes</a>
            </div>
        </div>

        <!-- Tarjeta: Exportación -->
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div>CSV</div>
                <div>Exportación de datos</div>
                <a href="exportar_mensajes.php">Exportar</a>
            </div>
        </div>

    </div>

    <!-- =========================
         ACCIONES + MENSAJES
         ========================= -->

    <div class="row g-4">

        <!-- Acciones rápidas -->
        <div class="col-lg-4">
            <div class="form-card">

                <h5>Acciones rápidas</h5>

                <div class="d-grid gap-2">

                    <!-- Crear libro -->
                    <a href="libro_crear.php">Agregar libro</a>

                    <!-- Crear autor -->
                    <a href="autor_crear.php">Agregar autor</a>

                    <!-- Ver mensajes -->
                    <a href="mensajes.php">Ver mensajes</a>

                    <!-- Exportar -->
                    <a href="exportar_mensajes.php">Exportar mensajes CSV</a>

                </div>
            </div>
        </div>

        <!-- Mensajes recientes -->
        <div class="col-lg-8">
            <div class="form-card">

                <h5>Últimos mensajes</h5>

                <?php if (empty($mensajesRecientes)): ?>

                    <!-- Si no hay mensajes -->
                    <p>No hay mensajes recibidos aún.</p>

                <?php else: ?>

                    <!-- Tabla de mensajes -->
                    <table>

                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Asunto</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($mensajesRecientes as $m): ?>
                                <tr>

                                    <!-- Fecha formateada -->
                                    <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>

                                    <!-- Nombre -->
                                    <td><?= htmlspecialchars($m['nombre']) ?></td>

                                    <!-- Asunto recortado -->
                                    <td><?= htmlspecialchars(mb_strimwidth($m['asunto'], 0, 40, '…')) ?></td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>

                <?php endif; ?>

            </div>
        </div>

    </div>

</div>

<?php
// Footer
require_once __DIR__ . '/../includes/admin_footer.php';
?>