<?php
// ============================================================
//  admin/libros.php — Lista de libros (admin)
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Libros — Admin';

// Variables principales
$libros         = [];   // Lista de libros
$errorDB        = '';   // Mensaje de error
$porPagina      = 10;   // Registros por página

// Página actual (mínimo 1)
$pagina         = max(1, (int)($_GET['pagina'] ?? 1));

// Mensaje de acción (crear, editar, eliminar)
$ok             = $_GET['ok'] ?? '';

try {
    // Conexión a BD
    $pdo = getConexion();

    // =========================
    // PAGINACIÓN
    // =========================

    // Total de registros
    $totalRegistros = (int)$pdo->query('SELECT COUNT(*) FROM titulos')->fetchColumn();

    // Total de páginas
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

    // Ajustar página si excede
    $pagina         = min($pagina, $totalPaginas);

    // Calcular offset
    $offset         = ($pagina - 1) * $porPagina;

    // =========================
    // CONSULTA DE LIBROS
    // =========================

    $stmt = $pdo->prepare(
        "SELECT 
            t.id_titulo, 
            t.titulo, 
            t.tipo, 
            p.nombre_pub AS editorial,
            t.precio, 
            t.total_ventas, 
            t.fecha_pub
         FROM titulos t
         INNER JOIN publicadores p ON t.id_pub = p.id_pub
         ORDER BY t.titulo ASC
         LIMIT :limite OFFSET :offset"
    );

    // Bind como enteros (IMPORTANTE)
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);

    // Ejecutar consulta
    $stmt->execute();

    // Obtener resultados
    $libros = $stmt->fetchAll();

} catch (PDOException $e) {
    // Registrar error en logs
    error_log('Error en admin/libros.php: ' . $e->getMessage());

    // Mensaje para el usuario
    $errorDB = 'Error al cargar los libros.';
}

// Mapa de tipos (clave → etiqueta en español)
$tiposES = [
    'business'=>'Negocios',
    'mod_cook'=>'Cocina Moderna',
    'trad_cook'=>'Cocina Tradicional',
    'popular_comp'=>'Informática',
    'psychology'=>'Psicología',
    'UNDECIDED'=>'Sin categoría'
];

// Incluir layout
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <!-- =========================
         ENCABEZADO + BOTÓN
         ========================= -->
    <div class="d-flex justify-content-between mb-4">
        <h1>Gestión de Libros</h1>

        <!-- Botón crear -->
        <a href="libro_crear.php">Agregar libro</a>
    </div>

    <!-- =========================
         MENSAJES DE ESTADO
         ========================= -->

    <?php if ($ok === 'creado'): ?>
        <div class="alert alert-success">Libro creado correctamente.</div>

    <?php elseif ($ok === 'editado'): ?>
        <div class="alert alert-success">Libro actualizado correctamente.</div>

    <?php elseif ($ok === 'eliminado'): ?>
        <div class="alert alert-warning">Libro eliminado correctamente.</div>
    <?php endif; ?>

    <!-- Error de base de datos -->
    <?php if ($errorDB): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorDB) ?></div>
    <?php endif; ?>

    <!-- Conteo -->
    <p>
        <?= $totalRegistros ?? 0 ?> libro(s) — Página <?= $pagina ?> de <?= $totalPaginas ?? 1 ?>
    </p>

    <!-- =========================
         TABLA DE LIBROS
         ========================= -->

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Editorial</th>
                <th>Precio</th>
                <th>Ventas</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

            <?php if (empty($libros)): ?>
                <tr>
                    <td colspan="8">No hay libros registrados.</td>
                </tr>

            <?php else: ?>

                <?php foreach ($libros as $libro): ?>

                    <?php
                        // Traducir tipo
                        $tipoEs = $tiposES[$libro['tipo']] ?? $libro['tipo'];

                        // Formatear precio
                        $precio = $libro['precio'] !== null 
                            ? '$' . number_format((float)$libro['precio'], 2) 
                            : '—';

                        // Formatear ventas
                        $ventas = $libro['total_ventas'] !== null 
                            ? number_format((int)$libro['total_ventas']) 
                            : '—';

                        // Formatear fecha
                        $fecha  = $libro['fecha_pub'] 
                            ? date('d/m/Y', strtotime($libro['fecha_pub'])) 
                            : '—';
                    ?>

                    <tr>

                        <!-- ID -->
                        <td><?= htmlspecialchars($libro['id_titulo']) ?></td>

                        <!-- Título recortado -->
                        <td><?= htmlspecialchars(mb_strimwidth($libro['titulo'], 0, 45, '…')) ?></td>

                        <!-- Tipo -->
                        <td><?= htmlspecialchars($tipoEs) ?></td>

                        <!-- Editorial -->
                        <td><?= htmlspecialchars($libro['editorial']) ?></td>

                        <!-- Precio -->
                        <td><?= $precio ?></td>

                        <!-- Ventas -->
                        <td><?= $ventas ?></td>

                        <!-- Fecha -->
                        <td><?= $fecha ?></td>

                        <!-- Acciones -->
                        <td>

                            <!-- Editar -->
                            <a href="libro_editar.php?id=<?= urlencode($libro['id_titulo']) ?>">
                                Editar
                            </a>

                            <!-- Eliminar -->
                            <a href="libro_eliminar.php?id=<?= urlencode($libro['id_titulo']) ?>">
                                Eliminar
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

    <!-- =========================
         PAGINACIÓN
         ========================= -->

    <?php if (($totalPaginas ?? 1) > 1): ?>
        <div>

            <!-- Botón anterior -->
            <a href="libros.php?pagina=<?= $pagina - 1 ?>">«</a>

            <!-- Números -->
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                <a href="libros.php?pagina=<?= $p ?>"><?= $p ?></a>
            <?php endfor; ?>

            <!-- Botón siguiente -->
            <a href="libros.php?pagina=<?= $pagina + 1 ?>">»</a>

        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>