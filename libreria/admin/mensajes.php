<?php
// ============================================================
//  admin/mensajes.php — Mensajes de contacto (admin protegido)
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Mensajes — Admin';

// Variables principales
$mensajes   = [];   // Lista de mensajes
$errorDB    = '';   // Mensaje de error
$porPagina  = 10;   // Cantidad por página

// Página actual
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));

// =========================
// FILTROS
// =========================

// Filtro por fechas
$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

try {
    $pdo = getConexion();

    $params      = []; // Parámetros para query
    $condiciones = []; // Condiciones dinámicas

    // Filtro "desde"
    if ($fechaDesde !== '') {
        $condiciones[] = 'DATE(fecha) >= :desde';
        $params[':desde'] = $fechaDesde;
    }

    // Filtro "hasta"
    if ($fechaHasta !== '') {
        $condiciones[] = 'DATE(fecha) <= :hasta';
        $params[':hasta'] = $fechaHasta;
    }

    // Construir WHERE dinámico
    $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

    // =========================
    // PAGINACIÓN
    // =========================

    // Contar registros
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM contacto $where");
    $stmtTotal->execute($params);

    $totalRegistros = (int)$stmtTotal->fetchColumn();

    // Total de páginas
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

    // Ajustar página
    $pagina         = min($pagina, $totalPaginas);

    // Offset
    $offset         = ($pagina - 1) * $porPagina;

    // =========================
    // CONSULTA PRINCIPAL
    // =========================

    $sql  = "SELECT id, fecha, nombre, correo, asunto, comentario
             FROM contacto $where
             ORDER BY fecha DESC
             LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // Bind dinámico de filtros
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    // Bind paginación
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);

    // Ejecutar
    $stmt->execute();

    // Obtener mensajes
    $mensajes = $stmt->fetchAll();

} catch (PDOException $e) {
    // Log del error
    error_log('Error en admin/mensajes.php: ' . $e->getMessage());

    // Mensaje al usuario
    $errorDB = 'No fue posible cargar los mensajes.';
}

// =========================
// FUNCIÓN AUXILIAR
// =========================

// Genera URL manteniendo filtros
function urlMensajes(array $extra = []): string {
    global $fechaDesde, $fechaHasta;

    // Mantiene filtros actuales
    $params = array_filter([
        'desde' => $fechaDesde,
        'hasta' => $fechaHasta
    ]);

    // Agrega parámetros extra (ej: página)
    $params = array_merge($params, $extra);

    return 'mensajes.php?' . http_build_query($params);
}

// Incluir layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <!-- =========================
         ENCABEZADO
         ========================= -->
    <div class="d-flex justify-content-between mb-4">

        <h1>Mensajes recibidos</h1>

        <!-- Botón exportar -->
        <a href="exportar_mensajes.php">
            Exportar CSV
        </a>
    </div>

    <!-- Mostrar error -->
    <?php if ($errorDB): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorDB) ?></div>
    <?php endif; ?>

    <!-- =========================
         FILTRO POR FECHA
         ========================= -->
    <form method="GET">

        <!-- Inputs tipo date -->
        <!-- Permiten filtrar por rango -->

    </form>

    <!-- Conteo -->
    <p>
        <?= $totalRegistros ?> mensaje(s) — Página <?= $pagina ?> de <?= $totalPaginas ?>
    </p>

    <!-- =========================
         TABLA DE MENSAJES
         ========================= -->

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Asunto</th>
                <th>Comentario</th>
            </tr>
        </thead>

        <tbody>

            <?php if (empty($mensajes)): ?>
                <tr>
                    <td colspan="6">No hay mensajes.</td>
                </tr>

            <?php else: ?>

                <?php foreach ($mensajes as $i => $m): ?>
                    <tr>

                        <!-- Número relativo -->
                        <td><?= $offset + $i + 1 ?></td>

                        <!-- Fecha -->
                        <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>

                        <!-- Nombre -->
                        <td><?= htmlspecialchars($m['nombre']) ?></td>

                        <!-- Correo (clickeable) -->
                        <td>
                            <a href="mailto:<?= htmlspecialchars($m['correo']) ?>">
                                <?= htmlspecialchars($m['correo']) ?>
                            </a>
                        </td>

                        <!-- Asunto -->
                        <td><?= htmlspecialchars($m['asunto']) ?></td>

                        <!-- Comentario -->
                        <td><?= nl2br(htmlspecialchars($m['comentario'])) ?></td>

                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

    <!-- =========================
         PAGINACIÓN
         ========================= -->

    <?php if ($totalPaginas > 1): ?>

        <!-- Botones anterior / siguiente + páginas -->

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>