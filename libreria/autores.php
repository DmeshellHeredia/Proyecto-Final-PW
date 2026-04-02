<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Directorio de Autores';
$metaDesc  = 'Conoce a todos los autores del catálogo de Librería Áurea.';
$autores   = [];
$errorDB   = '';

// ---- Paginación ----
$porPagina = 10;
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));

// ---- Filtros ----
$filtroPais   = trim($_GET['pais'] ?? '');
$filtroCiudad = trim($_GET['ciudad'] ?? '');

// ---- Ordenamiento ----
$camposPermitidos = ['apellido', 'nombre', 'ciudad', 'pais'];
$dirPermitidos    = ['ASC', 'DESC'];

$orden  = in_array($_GET['orden'] ?? '', $camposPermitidos, true) ? $_GET['orden'] : 'apellido';
$dirRaw = $_GET['dir'] ?? 'ASC';
$dir    = in_array(strtoupper($dirRaw), $dirPermitidos, true) ? strtoupper($dirRaw) : 'ASC';

$hayFiltros = $filtroPais !== '' || $filtroCiudad !== null && $filtroCiudad !== '';

$totalRegistros = 0;
$totalPaginas   = 1;
$offset         = 0;
$paises         = [];
$ciudades       = [];

try {
    $pdo = getConexion();

    $paises = $pdo->query("SELECT DISTINCT pais FROM autores ORDER BY pais ASC")->fetchAll(PDO::FETCH_COLUMN);
    $ciudades = $pdo->query("SELECT DISTINCT ciudad FROM autores ORDER BY ciudad ASC")->fetchAll(PDO::FETCH_COLUMN);

    $condiciones = [];
    $params      = [];

    if ($filtroPais !== '') {
        $condiciones[]  = 'pais = :pais';
        $params[':pais'] = $filtroPais;
    }

    if ($filtroCiudad !== '') {
        $condiciones[]    = 'ciudad = :ciudad';
        $params[':ciudad'] = $filtroCiudad;
    }

    $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM autores $where");
    $stmtTotal->execute($params);
    $totalRegistros = (int)$stmtTotal->fetchColumn();

    $totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));
    $pagina       = min($pagina, $totalPaginas);
    $offset       = ($pagina - 1) * $porPagina;

    $sql = "SELECT nombre, apellido, telefono, ciudad, pais
            FROM autores
            $where
            ORDER BY $orden $dir
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $autores = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Error en autores.php: ' . $e->getMessage());
    $errorDB = 'No fue posible cargar el directorio de autores. Inténtalo más tarde.';
}

function urlAutores(array $extra = []): string {
    global $filtroPais, $filtroCiudad, $orden, $dir;

    $p = array_filter([
        'pais'   => $filtroPais,
        'ciudad' => $filtroCiudad,
        'orden'  => $orden !== 'apellido' ? $orden : '',
        'dir'    => $dir !== 'ASC' ? $dir : '',
    ], fn($v) => $v !== '' && $v !== null);

    return 'autores.php?' . http_build_query(array_merge($p, $extra));
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="hero hero-small" aria-label="Directorio de autores">
    <div class="container position-relative">
        <nav aria-label="Ruta de navegación" class="mb-2">
            <ol class="breadcrumb breadcrumb-blanco">
                <li class="breadcrumb-item"><a href="index.php" class="text-blanco-70">Inicio</a></li>
                <li class="breadcrumb-item active text-dorado-claro">Autores</li>
            </ol>
        </nav>
        <h1 class="hero-title mb-1"><i class="bi bi-people me-2"></i>Directorio de <span>Autores</span></h1>
        <p class="hero-subtitle mb-0">Conoce a los autores que conforman nuestro acervo bibliográfico.</p>
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
                    data-bs-target="#panelFiltrosAutores"
                    aria-expanded="<?= $hayFiltros ? 'true' : 'false' ?>"
                    aria-controls="panelFiltrosAutores">
                <i class="bi bi-funnel me-1"></i>Filtros y ordenamiento
                <?php if ($hayFiltros): ?>
                    <span class="badge ms-1" style="background:var(--dorado);color:var(--verde-oscuro);font-size:.7rem;">activos</span>
                <?php endif; ?>
            </button>
            <?php if ($hayFiltros): ?>
                <a href="autores.php" class="btn btn-sm btn-link text-muted text-decoration-none">
                    <i class="bi bi-x-circle me-1"></i>Limpiar filtros
                </a>
            <?php endif; ?>
        </div>

        <!-- Panel colapsable de filtros -->
        <div class="collapse <?= $hayFiltros ? 'show' : '' ?>" id="panelFiltrosAutores">
            <form method="GET" action="autores.php" class="form-card py-3 px-4 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-md-3">
                        <label for="filtroPais" class="form-label small">País</label>
                        <select id="filtroPais" name="pais" class="form-select form-select-sm">
                            <option value="">Todos los países</option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?= htmlspecialchars($pais) ?>" <?= $filtroPais === $pais ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pais) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="filtroCiudad" class="form-label small">Ciudad</label>
                        <select id="filtroCiudad" name="ciudad" class="form-select form-select-sm">
                            <option value="">Todas las ciudades</option>
                            <?php foreach ($ciudades as $ciudad): ?>
                                <option value="<?= htmlspecialchars($ciudad) ?>" <?= $filtroCiudad === $ciudad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ciudad) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="orden" class="form-label small">Ordenar por</label>
                        <select id="orden" name="orden" class="form-select form-select-sm">
                            <option value="apellido" <?= $orden === 'apellido' ? 'selected' : '' ?>>Apellido</option>
                            <option value="nombre" <?= $orden === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                            <option value="ciudad" <?= $orden === 'ciudad' ? 'selected' : '' ?>>Ciudad</option>
                            <option value="pais" <?= $orden === 'pais' ? 'selected' : '' ?>>País</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="dir" class="form-label small">Dirección</label>
                        <select id="dir" name="dir" class="form-select form-select-sm">
                            <option value="ASC" <?= $dir === 'ASC' ? 'selected' : '' ?>>Ascendente ↑</option>
                            <option value="DESC" <?= $dir === 'DESC' ? 'selected' : '' ?>>Descendente ↓</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-0">
                    <div class="col-sm-4 col-md-2">
                        <button type="submit" class="btn btn-principal btn-sm w-100">
                            <i class="bi bi-funnel me-1"></i>Aplicar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Encabezado + buscador JS en tiempo real -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3">
            <h2 class="section-title mb-0">Listado de Autores</h2>
            <div class="buscador-wrap">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search"
                       id="campoBusquedaAutor"
                       class="form-control"
                       placeholder="Buscar por nombre, apellido, ciudad o país…"
                       aria-label="Buscar en la tabla de autores"
                       autocomplete="off">
            </div>
        </div>

        <p class="conteo-resultados mb-3">
            <span id="conteoAutores">
                Mostrando <?= count($autores) ?> de <?= $totalRegistros ?> registros
                (página <?= $pagina ?> de <?= $totalPaginas ?>)
            </span>
        </p>

        <div class="tabla-principal table-responsive">
            <table class="table table-hover mb-0" aria-label="Directorio de autores">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="bi bi-person me-1"></i>Nombre</th>
                        <th>Apellido</th>
                        <th><i class="bi bi-telephone me-1"></i>Teléfono</th>
                        <th><i class="bi bi-geo-alt me-1"></i>Ciudad</th>
                        <th><i class="bi bi-globe me-1"></i>País</th>
                    </tr>
                </thead>
                <tbody id="tbodyAutores">
                    <?php if (empty($autores)): ?>
                        <tr class="fila-vacia">
                            <td colspan="6">No se encontraron autores con los filtros seleccionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($autores as $i => $autor): ?>
                            <tr>
                                <td class="text-muted"><?= $offset + $i + 1 ?></td>
                                <td>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-2 fw-bold avatar-autor" aria-hidden="true">
                                        <?= strtoupper(mb_substr($autor['nombre'], 0, 1)) ?>
                                    </span>
                                    <span class="fw-semibold text-verde"><?= htmlspecialchars($autor['nombre']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($autor['apellido']) ?></td>
                                <td>
                                    <a href="tel:<?= htmlspecialchars($autor['telefono']) ?>" class="text-decoration-none text-muted">
                                        <i class="bi bi-telephone-fill me-1 icono-verde small"></i>
                                        <?= htmlspecialchars($autor['telefono']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($autor['ciudad']) ?></td>
                                <td><span class="badge-tipo"><?= htmlspecialchars($autor['pais']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <nav aria-label="Páginas del directorio" class="mt-4">
            <ul class="pagination justify-content-center flex-wrap">
                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= urlAutores(['pagina' => $pagina - 1]) ?>">&laquo;</a>
                </li>
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= urlAutores(['pagina' => $p]) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= urlAutores(['pagina' => $pagina + 1]) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>