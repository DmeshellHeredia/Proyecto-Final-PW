<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Inicio';
$metaDesc  = 'Librería Áurea — Explora nuestro catálogo académico de libros y autores.';

$errorDB = '';
$totalLibros = $totalAutores = $totalEditoriales = $totalCategorias = 0;
$libroMasVendido = $autorMasTitulos = $categoriaMasLibros = null;

try {
    $pdo = getConexion();
    $totalLibros      = $pdo->query('SELECT COUNT(*) FROM titulos')->fetchColumn();
    $totalAutores     = $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();
    $totalEditoriales = $pdo->query('SELECT COUNT(DISTINCT id_pub) FROM titulos')->fetchColumn();
    $totalCategorias  = $pdo->query('SELECT COUNT(DISTINCT tipo) FROM titulos')->fetchColumn();
    $libroMasVendido  = $pdo->query("SELECT t.titulo, t.total_ventas, p.nombre_pub AS editorial FROM titulos t INNER JOIN publicadores p ON t.id_pub=p.id_pub WHERE t.total_ventas IS NOT NULL ORDER BY t.total_ventas DESC LIMIT 1")->fetch();
    $autorMasTitulos  = $pdo->query("SELECT a.nombre, a.apellido, COUNT(ta.id_titulo) AS num_titulos FROM autores a INNER JOIN titulo_autor ta ON a.id_autor=ta.id_autor GROUP BY a.id_autor,a.nombre,a.apellido ORDER BY num_titulos DESC LIMIT 1")->fetch();
    $categoriaMasLibros = $pdo->query("SELECT tipo, COUNT(*) AS cantidad FROM titulos GROUP BY tipo ORDER BY cantidad DESC LIMIT 1")->fetch();
} catch (PDOException $e) {
    error_log('Error en index.php: ' . $e->getMessage());
    $totalLibros = $totalAutores = $totalEditoriales = $totalCategorias = 'N/D';
    $errorDB = 'No fue posible cargar el resumen del catálogo en este momento.';
}

$tiposES = ['business'=>'Negocios','mod_cook'=>'Cocina Moderna','trad_cook'=>'Cocina Tradicional','popular_comp'=>'Informática','psychology'=>'Psicología','UNDECIDED'=>'Sin categoría'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!--
  CAROUSEL: Los controles prev/next e indicadores están DENTRO del div #heroCarousel
  (como requiere Bootstrap 5). El CSS elimina z-index de .hero-bg-carousel para que
  NO cree un contexto de apilamiento propio; así los controles con z-index:10
  quedan por encima de .hero-overlay (z-index:1) y .hero-content (z-index:3).
-->
<section class="hero hero-carousel-full" aria-label="Presentación">

    <div id="heroCarousel"
         class="carousel slide carousel-fade hero-bg-carousel"
         data-bs-ride="carousel"
         data-bs-interval="4500">

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/libro.jpg"  class="d-block w-100 hero-bg-img" alt="Biblioteca clásica">
            </div>
            <div class="carousel-item">
                <img src="assets/img/libro3.jpg" class="d-block w-100 hero-bg-img" alt="Estantería académica">
            </div>
            <div class="carousel-item">
                <img src="assets/img/libro4.jpg" class="d-block w-100 hero-bg-img" alt="Libros antiguos">
            </div>
            <div class="carousel-item">
                <img src="assets/img/libro5.jpg" class="d-block w-100 hero-bg-img" alt="Colección de libros">
            </div>
        </div>

        <!-- Controles DENTRO del carousel — Bootstrap los requiere aquí -->
        <button class="carousel-control-prev" type="button"
                data-bs-target="#heroCarousel" data-bs-slide="prev"
                aria-label="Imagen anterior">
            <span class="carousel-control-prev-icon hero-control-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>

        <button class="carousel-control-next" type="button"
                data-bs-target="#heroCarousel" data-bs-slide="next"
                aria-label="Imagen siguiente">
            <span class="carousel-control-next-icon hero-control-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>

        <div class="carousel-indicators hero-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Imagen 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Imagen 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Imagen 3"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Imagen 4"></button>
        </div>

    </div><!-- /.hero-bg-carousel -->

    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-content position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 col-xl-7">
                <p class="text-uppercase small fw-semibold mb-2" style="color:var(--dorado-claro);letter-spacing:.1em;">
                    <i class="bi bi-mortarboard me-1"></i> Portal Académico
                </p>
                <h1 class="hero-title mb-3">
                    Bienvenido a la<br><span>Librería Áurea</span>
                </h1>
                <p class="hero-subtitle mb-4">
                    Explora nuestro catálogo de títulos y conoce a los autores que dan vida al conocimiento.
                    Una plataforma diseñada para facilitar el acceso al acervo bibliográfico académico.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="libros.php" class="btn btn-principal px-4 py-2">
                        <i class="bi bi-journals me-2"></i>Ver Catálogo
                    </a>
                    <a href="autores.php" class="btn btn-outline-light px-4 py-2 rounded-soft hero-btn-secundario">
                        <i class="bi bi-people me-2"></i>Ver Autores
                    </a>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- ESTADÍSTICAS -->
<section class="py-5">
    <div class="container">

        <?php if (!empty($errorDB)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorDB) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2 class="section-title mb-4">Resumen del Catálogo</h2>

        <div class="row g-4 mb-4">
            <?php
            $stats = [
                ['icon'=>'bi-journal-richtext','value'=>$totalLibros,'label'=>'Libros en catálogo'],
                ['icon'=>'bi-people-fill','value'=>$totalAutores,'label'=>'Autores registrados'],
                ['icon'=>'bi-building','value'=>$totalEditoriales,'label'=>'Editoriales asociadas'],
                ['icon'=>'bi-tags-fill','value'=>$totalCategorias,'label'=>'Categorías disponibles'],
            ];
            foreach ($stats as $s): ?>
                <div class="col-sm-6 col-lg-3 reveal-up">
                    <div class="stat-card h-100">
                        <div class="stat-icon"><i class="bi <?= $s['icon'] ?>"></i></div>
                        <div class="stat-numero" data-objetivo="<?= is_numeric($s['value'])?$s['value']:0 ?>">
                            <?= htmlspecialchars((string)$s['value']) ?>
                        </div>
                        <div class="stat-label"><?= $s['label'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($libroMasVendido || $autorMasTitulos || $categoriaMasLibros): ?>
        <h2 class="section-title mb-4 mt-2">Destacados</h2>
        <div class="row g-4 mb-5">

            <?php if ($libroMasVendido): ?>
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start" style="border-top-color:#e8c97a;">
                    <div class="stat-icon"><i class="bi bi-fire text-danger"></i></div>
                    <div class="small fw-semibold text-uppercase mb-1" style="letter-spacing:.06em;color:var(--texto-gris);">Libro más vendido</div>
                    <div class="fw-bold text-verde" style="font-size:1rem;line-height:1.3;"><?= htmlspecialchars($libroMasVendido['titulo']) ?></div>
                    <div class="small text-muted mt-1"><?= htmlspecialchars($libroMasVendido['editorial']) ?></div>
                    <div class="mt-2 precio"><?= number_format((int)$libroMasVendido['total_ventas']) ?> ventas</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($autorMasTitulos): ?>
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start" style="border-top-color:#2e7d52;">
                    <div class="stat-icon"><i class="bi bi-person-badge icono-verde"></i></div>
                    <div class="small fw-semibold text-uppercase mb-1" style="letter-spacing:.06em;color:var(--texto-gris);">Autor más prolífico</div>
                    <div class="fw-bold text-verde" style="font-size:1rem;"><?= htmlspecialchars($autorMasTitulos['nombre'].' '.$autorMasTitulos['apellido']) ?></div>
                    <div class="mt-2 precio"><?= (int)$autorMasTitulos['num_titulos'] ?> título(s) publicado(s)</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($categoriaMasLibros): ?>
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start" style="border-top-color:#c9a84c;">
                    <div class="stat-icon"><i class="bi bi-bookmark-star icono-dorado"></i></div>
                    <div class="small fw-semibold text-uppercase mb-1" style="letter-spacing:.06em;color:var(--texto-gris);">Categoría más popular</div>
                    <div class="fw-bold text-verde" style="font-size:1rem;"><?= htmlspecialchars($tiposES[$categoriaMasLibros['tipo']] ?? $categoriaMasLibros['tipo']) ?></div>
                    <div class="mt-2 precio"><?= (int)$categoriaMasLibros['cantidad'] ?> libro(s)</div>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8 reveal-up">
                <div class="stat-card text-start h-100 border-top-verde">
                    <h4 class="mb-3 text-verde"><i class="bi bi-info-circle-fill me-2 icono-verde"></i>Sobre este portal</h4>
                    <p class="text-muted mb-3">Sistema de gestión de catálogo bibliográfico académico. Consulta títulos, conoce autores y contáctanos.</p>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2 icono-verde"></i>Catálogo completo con precios y ventas</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2 icono-verde"></i>Directorio de autores con información de contacto</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2 icono-verde"></i>Búsqueda y filtros en tiempo real</li>
                        <li class="mb-0"><i class="bi bi-check-circle-fill me-2 icono-verde"></i>Formulario de contacto con almacenamiento seguro</li>
                    </ul>
                    <div class="mt-4">
                        <a href="libros.php" class="btn btn-principal me-2"><i class="bi bi-arrow-right-circle me-1"></i>Explorar catálogo</a>
                        <a href="contacto.php" class="btn btn-outline-secondary rounded-soft"><i class="bi bi-envelope me-1"></i>Contactar</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 reveal-up">
                <div class="info-card h-100">
                    <h5><i class="bi bi-lightning-fill me-1"></i>Accesos rápidos</h5>
                    <ul class="list-unstyled mt-2">
                        <li class="mb-3"><a href="libros.php" class="text-decoration-none text-blanco-85"><i class="bi bi-journals me-2 text-dorado-claro"></i>Catálogo de libros</a></li>
                        <li class="mb-3"><a href="autores.php" class="text-decoration-none text-blanco-85"><i class="bi bi-people me-2 text-dorado-claro"></i>Directorio de autores</a></li>
                        <li class="mb-3"><a href="contacto.php" class="text-decoration-none text-blanco-85"><i class="bi bi-envelope me-2 text-dorado-claro"></i>Formulario de contacto</a></li>
                    </ul>
                    <hr class="footer-divider">
                    <p class="small mb-0 text-blanco-55"><i class="bi bi-clock me-1"></i>Sistema actualizado: <?= date('d/m/Y') ?></p>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="py-5" style="background:var(--crema-oscura);">
    <div class="container">
        <h2 class="section-title mb-5">Nuestra propuesta</h2>
        <div class="row g-4">
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start">
                    <div class="stat-icon"><i class="bi bi-bullseye"></i></div>
                    <h5 class="fw-semibold text-verde mb-2">Misión</h5>
                    <p class="text-muted mb-0">Facilitar el acceso a información bibliográfica de calidad para apoyar el aprendizaje académico.</p>
                </div>
            </div>
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start">
                    <div class="stat-icon"><i class="bi bi-eye-fill"></i></div>
                    <h5 class="fw-semibold text-verde mb-2">Visión</h5>
                    <p class="text-muted mb-0">Consolidarse como una librería académica digital moderna que conecte docentes, estudiantes e investigadores.</p>
                </div>
            </div>
            <div class="col-md-4 reveal-up">
                <div class="stat-card h-100 text-start">
                    <div class="stat-icon"><i class="bi bi-gear-fill"></i></div>
                    <h5 class="fw-semibold text-verde mb-2">Servicios</h5>
                    <ul class="text-muted mb-0 ps-3">
                        <li>Consulta del catálogo de libros</li>
                        <li>Directorio de autores</li>
                        <li>Búsqueda y filtros avanzados</li>
                        <li>Formulario de contacto</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
