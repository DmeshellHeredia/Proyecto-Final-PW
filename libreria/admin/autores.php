<?php
// ============================================================
//  admin/autores.php — Lista de autores (admin)
// ============================================================

// Incluye autenticación y conexión a la base de datos
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';

// Verifica que el usuario sea administrador
requerirAdmin('../login.php');

// Variables de la vista
$basePath  = '../';
$pageTitle = 'Autores — Admin';

// Variables principales
$autores        = [];   // Lista de autores
$errorDB        = '';   // Mensaje de error
$porPagina      = 10;   // Cantidad de registros por página

// Obtiene la página actual (mínimo 1)
$pagina         = max(1, (int)($_GET['pagina'] ?? 1));

// Mensaje de éxito (crear, editar, eliminar)
$ok             = $_GET['ok'] ?? '';

try {
    // Conexión a la base de datos
    $pdo = getConexion();

    // =========================
    // PAGINACIÓN
    // =========================

    // Total de registros en la tabla
    $totalRegistros = (int)$pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();

    // Total de páginas (ceil redondea hacia arriba)
    $totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));

    // Evita que la página sea mayor que el total
    $pagina         = min($pagina, $totalPaginas);

    // Calcula el offset (desde dónde empezar)
    $offset         = ($pagina - 1) * $porPagina;

    // =========================
    // CONSULTA DE AUTORES
    // =========================

    $stmt = $pdo->prepare(
        "SELECT id_autor, nombre, apellido, telefono, ciudad, estado, pais, cod_postal
         FROM autores 
         ORDER BY apellido ASC, nombre ASC
         LIMIT :limite OFFSET :offset"
    );

    // Bind de parámetros como enteros (IMPORTANTE para LIMIT/OFFSET)
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);

    // Ejecuta la consulta
    $stmt->execute();

    // Obtiene todos los autores
    $autores = $stmt->fetchAll();

} catch (PDOException $e) {
    // Guarda error en log
    error_log('Error en admin/autores.php: ' . $e->getMessage());

    // Mensaje para el usuario
    $errorDB = 'Error al cargar los autores.';
}

// Incluye cabecera y navbar
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <!-- Encabezado + botón -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="section-title mb-0">
            <i class="bi bi-people me-2"></i>Gestión de Autores
        </h1>

        <!-- Botón para crear nuevo autor -->
        <a href="autor_crear.php" class="btn btn-principal">
            <i class="bi bi-person-plus me-2"></i>Agregar autor
        </a>
    </div>

    <!-- =========================
         MENSAJES DE ÉXITO
         ========================= -->

    <?php if ($ok === 'creado'): ?>
        <div class="alert alert-success">
            Autor creado correctamente.
        </div>

    <?php elseif ($ok === 'editado'): ?>
        <div class="alert alert-success">
            Autor actualizado correctamente.
        </div>

    <?php elseif ($ok === 'eliminado'): ?>
        <div class="alert alert-warning">
            Autor eliminado correctamente.
        </div>
    <?php endif; ?>

    <!-- Mostrar error si falla la BD -->
    <?php if ($errorDB): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorDB) ?></div>
    <?php endif; ?>

    <!-- Conteo de resultados -->
    <p class="conteo-resultados mb-3">
        <?= $totalRegistros ?? 0 ?> autor(es) — Página <?= $pagina ?> de <?= $totalPaginas ?? 1 ?>
    </p>

    <!-- =========================
         TABLA DE AUTORES
         ========================= -->

    <div class="tabla-principal table-responsive mb-4">
        <table class="table table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    <th>País</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <!-- Si no hay autores -->
                <?php if (empty($autores)): ?>
                    <tr>
                        <td colspan="7">No hay autores registrados.</td>
                    </tr>

                <?php else: ?>

                    <!-- Recorrer autores -->
                    <?php foreach ($autores as $autor): ?>
                        <tr>

                            <!-- ID -->
                            <td><?= htmlspecialchars($autor['id_autor']) ?></td>

                            <!-- Nombre + avatar -->
                            <td>
                                <!-- Primera letra como avatar -->
                                <span>
                                    <?= strtoupper(mb_substr($autor['nombre'], 0, 1)) ?>
                                </span>

                                <!-- Nombre -->
                                <?= htmlspecialchars($autor['nombre']) ?>
                            </td>

                            <!-- Apellido -->
                            <td><?= htmlspecialchars($autor['apellido']) ?></td>

                            <!-- Teléfono -->
                            <td><?= htmlspecialchars($autor['telefono']) ?></td>

                            <!-- Ciudad -->
                            <td><?= htmlspecialchars($autor['ciudad']) ?></td>

                            <!-- País -->
                            <td><?= htmlspecialchars($autor['pais']) ?></td>

                            <!-- Acciones -->
                            <td>

                                <!-- Editar -->
                                <a href="autor_editar.php?id=<?= urlencode($autor['id_autor']) ?>">
                                    Editar
                                </a>

                                <!-- Eliminar -->
                                <a href="autor_eliminar.php?id=<?= urlencode($autor['id_autor']) ?>">
                                    Eliminar
                                </a>

                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>
    </div>

    <!-- =========================
         PAGINACIÓN
         ========================= -->

    <?php if (($totalPaginas ?? 1) > 1): ?>
        <nav>
            <ul class="pagination">

                <!-- Botón anterior -->
                <li class="<?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a href="autores.php?pagina=<?= $pagina - 1 ?>">&laquo;</a>
                </li>

                <!-- Números de página -->
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <li class="<?= $p === $pagina ? 'active' : '' ?>">
                        <a href="autores.php?pagina=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Botón siguiente -->
                <li class="<?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                    <a href="autores.php?pagina=<?= $pagina + 1 ?>">&raquo;</a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php
// Incluye el footer
require_once __DIR__ . '/../includes/admin_footer.php';
?>