<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Catálogo de Libros';
$metaDesc  = 'Explora el catálogo completo de libros de Librería Áurea. Filtra por categoría, editorial y precio.';
$libros    = [];
$errorDB   = '';

// ---- Paginación ----
$porPagina = 10;
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));

// ---- Filtros: usar isset() ANTES de acceder al valor (evita warnings) ----
$filtroTipo      = trim($_GET['tipo']      ?? '');
$filtroEditorial = trim($_GET['editorial'] ?? '');
$filtroPrecioMin = (isset($_GET['precio_min']) && $_GET['precio_min'] !== '') ? (float)$_GET['precio_min'] : null;
$filtroPrecioMax = (isset($_GET['precio_max']) && $_GET['precio_max'] !== '') ? (float)$_GET['precio_max'] : null;

// ---- Ordenamiento ----
$camposPermitidos = ['titulo', 'precio', 'total_ventas', 'fecha_pub'];
$dirPermitidos    = ['ASC', 'DESC'];
$orden  = in_array($_GET['orden'] ?? '', $camposPermitidos) ? $_GET['orden'] : 'titulo';
$dirRaw = $_GET['dir'] ?? 'ASC';
$dir    = in_array(strtoupper($dirRaw), $dirPermitidos) ? strtoupper($dirRaw) : 'ASC';

$hayFiltros = $filtroTipo !== '' || $filtroEditorial !== '' || $filtroPrecioMin !== null || $filtroPrecioMax !== null;

$tiposES = [
    'business'     => 'Negocios',
    'mod_cook'     => 'Cocina Moderna',
    'trad_cook'    => 'Cocina Tradicional',
    'popular_comp' => 'Informática',
    'psychology'   => 'Psicología',
    'UNDECIDED'    => 'Sin categoría',
];

$totalRegistros = 0;
$totalPaginas   = 1;
$tipos          = [];
$editoriales    = [];

try {
    $pdo = getConexion();

    $tipos       = $pdo->query("SELECT DISTINCT tipo FROM titulos ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);
    $editoriales = $pdo->query("SELECT id_pub, nombre_pub FROM publicadores ORDER BY nombre_pub")->fetchAll();

    $condiciones = [];
    $params      = [];

    if ($filtroTipo !== '') {
        $condiciones[] = 't.tipo = :tipo';
        $params[':tipo'] = $filtroTipo;
    }
    if ($filtroEditorial !== '') {
        $condiciones[] = 'p.id_pub = :editorial';
        $params[':editorial'] = $filtroEditorial;
    }
    if ($filtroPrecioMin !== null) {
        $condiciones[] = 't.precio >= :precio_min';
        $params[':precio_min'] = $filtroPrecioMin;
    }
    if ($filtroPrecioMax !== null) {
        $condiciones[] = 't.precio <= :precio_max';
        $params[':precio_max'] = $filtroPrecioMax;
    }

    $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM titulos t INNER JOIN publicadores p ON t.id_pub = p.id_pub $where");
    $stmtTotal->execute($params);
    $totalRegistros = (int)$stmtTotal->fetchColumn();
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));
    $pagina         = min($pagina, $totalPaginas);
    $offset         = ($pagina - 1) * $porPagina;

    $sql = "SELECT t.id_titulo, t.titulo, t.tipo, p.nombre_pub AS editorial,
                   t.precio, t.total_ventas, t.fecha_pub
            FROM titulos t INNER JOIN publicadores p ON t.id_pub = p.id_pub
            $where
            ORDER BY t.$orden $dir
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) $stmt->bindValue($key, $val);
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmt->execute();
    $libros = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Error en libros.php: ' . $e->getMessage());
    $errorDB = 'No fue posible cargar el catálogo. Inténtalo más tarde.';
}

function urlLibros(array $extra = []): string {
    global $filtroTipo, $filtroEditorial, $filtroPrecioMin, $filtroPrecioMax, $orden, $dir;
    $p = array_filter([
        'tipo'       => $filtroTipo,
        'editorial'  => $filtroEditorial,
        'precio_min' => $filtroPrecioMin !== null ? (string)$filtroPrecioMin : '',
        'precio_max' => $filtroPrecioMax !== null ? (string)$filtroPrecioMax : '',
        'orden'      => $orden !== 'titulo' ? $orden : '',
        'dir'        => $dir !== 'ASC' ? $dir : '',
    ], fn($v) => $v !== '' && $v !== null);
    return 'libros.php?' . http_build_query(array_merge($p, $extra));
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="hero hero-small" aria-label="Catálogo de libros">
    <div class="container position-relative">
        <nav aria-label="Ruta de navegación" class="mb-2">
            <ol class="breadcrumb breadcrumb-blanco">
                <li class="breadcrumb-item"><a href="index.php" class="text-blanco-70">Inicio</a></li>
                <li class="breadcrumb-item active text-dorado-claro">Libros</li>
            </ol>
        </nav>
        <h1 class="hero-title mb-1"><i class="bi bi-journals me-2"></i>Catálogo de <span>Libros</span></h1>
        <p class="hero-subtitle mb-0">Consulta y filtra todos los títulos disponibles.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">

        <?php if ($errorDB): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorDB) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Botón para abrir/cerrar panel de filtros -->
        <div class="mb-3 d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#panelFiltros"
                    aria-expanded="<?= $hayFiltros ? 'true' : 'false' ?>"
                    aria-controls="panelFiltros">
                <i class="bi bi-funnel me-1"></i>Filtros y ordenamiento
                <?php if ($hayFiltros): ?>
                    <span class="badge ms-1" style="background:var(--dorado);color:var(--verde-oscuro);font-size:.7rem;">activos</span>
                <?php endif; ?>
            </button>
            <?php if ($hayFiltros): ?>
                <a href="libros.php" class="btn btn-sm btn-link text-muted text-decoration-none">
                    <i class="bi bi-x-circle me-1"></i>Limpiar filtros
                </a>
            <?php endif; ?>
        </div>

        <!-- Panel colapsable de filtros -->
        <div class="collapse <?= $hayFiltros ? 'show' : '' ?>" id="panelFiltros">
            <form method="GET" action="libros.php" class="form-card py-3 px-4 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-md-3">
                        <label for="filtroTipo" class="form-label small">Categoría</label>
                        <select id="filtroTipo" name="tipo" class="form-select form-select-sm">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= htmlspecialchars($t) ?>" <?= $filtroTipo === $t ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tiposES[$t] ?? $t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label for="filtroEditorial" class="form-label small">Editorial</label>
                        <select id="filtroEditorial" name="editorial" class="form-select form-select-sm">
                            <option value="">Todas las editoriales</option>
                            <?php foreach ($editoriales as $ed): ?>
                                <option value="<?= htmlspecialchars($ed['id_pub']) ?>" <?= $filtroEditorial === $ed['id_pub'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ed['nombre_pub']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <label for="precioMin" class="form-label small">Precio mín. ($)</label>
                        <input type="number" id="precioMin" name="precio_min" class="form-control form-control-sm"
                               min="0" step="0.01" placeholder="0.00"
                               value="<?= $filtroPrecioMin !== null ? $filtroPrecioMin : '' ?>">
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <label for="precioMax" class="form-label small">Precio máx. ($)</label>
                        <input type="number" id="precioMax" name="precio_max" class="form-control form-control-sm"
                               min="0" step="0.01" placeholder="999"
                               value="<?= $filtroPrecioMax !== null ? $filtroPrecioMax : '' ?>">
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <button type="submit" class="btn btn-principal btn-sm w-100">
                            <i class="bi bi-funnel me-1"></i>Aplicar
                        </button>
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-0">
                    <div class="col-sm-6 col-md-4">
                        <label for="orden" class="form-label small">Ordenar por</label>
                        <select id="orden" name="orden" class="form-select form-select-sm">
                            <option value="titulo"       <?= $orden==='titulo'       ? 'selected':'' ?>>Título</option>
                            <option value="precio"       <?= $orden==='precio'       ? 'selected':'' ?>>Precio</option>
                            <option value="total_ventas" <?= $orden==='total_ventas' ? 'selected':'' ?>>Total ventas</option>
                            <option value="fecha_pub"    <?= $orden==='fecha_pub'    ? 'selected':'' ?>>Fecha publicación</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label for="dir" class="form-label small">Dirección</label>
                        <select id="dir" name="dir" class="form-select form-select-sm">
                            <option value="ASC"  <?= $dir==='ASC'  ? 'selected':'' ?>>Ascendente ↑</option>
                            <option value="DESC" <?= $dir==='DESC' ? 'selected':'' ?>>Descendente ↓</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Encabezado + buscador JS en tiempo real -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3">
            <h2 class="section-title mb-0">Listado de Títulos</h2>
            <div class="buscador-wrap">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="campoBusqueda" class="form-control"
                       placeholder="Buscar por título, editorial o categoría…"
                       aria-label="Buscar en la tabla" autocomplete="off">
            </div>
        </div>

        <p class="conteo-resultados mb-3">
            <span id="conteoLibros">
                Mostrando <?= count($libros) ?> de <?= $totalRegistros ?> registros
                (página <?= $pagina ?> de <?= $totalPaginas ?>)
            </span>
        </p>

        <div class="tabla-principal table-responsive">
            <table class="table table-hover mb-0" aria-label="Catálogo de libros">
                <thead>
                    <tr>
                        <th>#</th><th><i class="bi bi-book me-1"></i>Título</th>
                        <th>Tipo</th><th>Editorial</th><th>Precio</th><th>Ventas</th><th>Fecha Pub.</th>
                    </tr>
                </thead>
                <tbody id="tbodyLibros">
                    <?php if (empty($libros)): ?>
                        <tr class="fila-vacia"><td colspan="7">No se encontraron libros con los filtros seleccionados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($libros as $i => $libro):
                            $tipoEs = $tiposES[$libro['tipo']] ?? $libro['tipo'];
                            $fecha  = $libro['fecha_pub'] ? date('d/m/Y', strtotime($libro['fecha_pub'])) : '—';
                            $precio = $libro['precio'] !== null ? '$'.number_format((float)$libro['precio'],2) : '—';
                            $ventas = $libro['total_ventas'] !== null ? number_format((int)$libro['total_ventas']) : '—';
                        ?>
                            <tr>
                                <td class="text-muted"><?= $offset+$i+1 ?></td>
                                <td><span class="fw-semibold text-verde"><?= htmlspecialchars($libro['titulo']) ?></span></td>
                                <td><span class="badge-tipo"><?= htmlspecialchars($tipoEs) ?></span></td>
                                <td><?= htmlspecialchars($libro['editorial']) ?></td>
                                <td class="precio"><?= $precio ?></td>
                                <td>
                                    <span class="fw-semibold"><?= $ventas ?></span>
                                    <?php if ($libro['total_ventas'] !== null && $libro['total_ventas'] > 10000): ?>
                                        <i class="bi bi-fire text-danger ms-1" title="Bestseller"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= $fecha ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="small text-muted mt-3 mb-4">
            <i class="bi bi-fire text-danger me-1"></i>Títulos con más de 10,000 ventas totales.
        </p>

        <?php if ($totalPaginas > 1): ?>
        <nav aria-label="Páginas del catálogo">
            <ul class="pagination justify-content-center flex-wrap">
                <li class="page-item <?= $pagina<=1?'disabled':'' ?>">
                    <a class="page-link" href="<?= urlLibros(['pagina'=>$pagina-1]) ?>">&laquo;</a>
                </li>
                <?php for ($p=1;$p<=$totalPaginas;$p++): ?>
                    <li class="page-item <?= $p===$pagina?'active':'' ?>">
                        <a class="page-link" href="<?= urlLibros(['pagina'=>$p]) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina>=$totalPaginas?'disabled':'' ?>">
                    <a class="page-link" href="<?= urlLibros(['pagina'=>$pagina+1]) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
