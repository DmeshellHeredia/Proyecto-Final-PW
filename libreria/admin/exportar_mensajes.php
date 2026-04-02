<?php
// ============================================================
//  admin/exportar_mensajes.php — Exportar mensajes a CSV
// ============================================================

// Incluye autenticación y base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Solo administradores pueden acceder
requerirAdmin('../login.php');

// Obtener filtros desde la URL (GET)
$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

// =========================
// DESCARGA DIRECTA CSV
// =========================
if (isset($_GET['descargar'])) {

    try {
        // Conexión a BD
        $pdo    = getConexion();

        // Arrays para condiciones dinámicas
        $params = [];
        $conds  = [];

        // Filtro por fecha desde
        if ($fechaDesde !== '') {
            $conds[] = 'DATE(fecha) >= :desde';
            $params[':desde'] = $fechaDesde;
        }

        // Filtro por fecha hasta
        if ($fechaHasta !== '') {
            $conds[] = 'DATE(fecha) <= :hasta';
            $params[':hasta'] = $fechaHasta;
        }

        // Construcción del WHERE dinámico
        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

        // Consulta de mensajes
        $stmt = $pdo->prepare(
            "SELECT id, fecha, nombre, correo, asunto, comentario 
             FROM contacto 
             $where 
             ORDER BY fecha DESC"
        );

        $stmt->execute($params);
        $mensajes = $stmt->fetchAll();

        // =========================
        // CONFIGURAR DESCARGA CSV
        // =========================

        // Nombre del archivo con fecha/hora
        $filename = 'mensajes_' . date('Y-m-d_H-i-s') . '.csv';

        // Cabeceras HTTP para forzar descarga
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM para compatibilidad con Excel (UTF-8)
        echo "\xEF\xBB\xBF";

        // Abrir salida como archivo
        $out = fopen('php://output', 'w');

        // Escribir cabecera del CSV
        fputcsv($out, ['ID', 'Fecha', 'Nombre', 'Correo', 'Asunto', 'Comentario'], ';');

        // Recorrer mensajes y escribir filas
        foreach ($mensajes as $m) {
            fputcsv($out, [
                $m['id'],
                $m['fecha'],
                $m['nombre'],
                $m['correo'],
                $m['asunto'],
                $m['comentario'],
            ], ';');
        }

        // Cerrar archivo
        fclose($out);

        // Terminar ejecución para evitar HTML extra
        exit;

    } catch (PDOException $e) {

        // Registrar error en logs
        error_log('Error en exportar_mensajes.php: ' . $e->getMessage());

        // Redirigir en caso de error
        header('Location: mensajes.php');
        exit;
    }
}

// =========================
// VISTA PREVIA (ANTES DE EXPORTAR)
// =========================

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Exportar Mensajes — Admin';

try {
    $pdo    = getConexion();
    $params = [];
    $conds  = [];

    // Reutiliza filtros
    if ($fechaDesde !== '') {
        $conds[] = 'DATE(fecha) >= :desde';
        $params[':desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '') {
        $conds[] = 'DATE(fecha) <= :hasta';
        $params[':hasta'] = $fechaHasta;
    }

    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

    // Solo contar registros (más eficiente que traerlos todos)
    $stmtC = $pdo->prepare("SELECT COUNT(*) FROM contacto $where");
    $stmtC->execute($params);

    $total = (int)$stmtC->fetchColumn();

} catch (PDOException $e) {
    // Si falla, mostrar 0 resultados
    $total = 0;
}

// Incluir layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container py-5" style="max-width:680px;">

    <!-- Título -->
    <h1 class="section-title mb-4">
        Exportar mensajes
    </h1>

    <div class="form-card">

        <!-- =========================
             FILTROS
             ========================= -->

        <form method="GET" action="exportar_mensajes.php">
            <!-- Inputs tipo fecha -->
        </form>

        <!-- =========================
             RESULTADO
             ========================= -->

        <!-- Muestra cantidad de registros encontrados -->
        <p>Coincidencias: <?= $total ?></p>

        <!-- Botón de descarga -->
        <a href="exportar_mensajes.php?descargar=1">
            Descargar CSV
        </a>

        <!-- Botón volver -->
        <a href="mensajes.php">Volver</a>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>