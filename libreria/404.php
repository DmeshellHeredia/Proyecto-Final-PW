<?php
// ============================================================
//  ERROR 404 — PÁGINA NO ENCONTRADA
// ------------------------------------------------------------
//  Este archivo se muestra cuando el recurso solicitado
//  no existe o la URL no coincide con una ruta válida.
// ============================================================

// Envía el código HTTP 404 al navegador y a los buscadores
http_response_code(404);

// Título de la página para el <title> del layout
$pageTitle = 'Página no encontrada';

// Carga el encabezado global del sitio
require_once __DIR__ . '/includes/header.php';

// Carga la barra de navegación principal
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- ============================================================
     SECCIÓN PRINCIPAL DEL ERROR 404
     ------------------------------------------------------------
     Mensaje visual simple para informar al usuario que la
     página no existe y ofrecer una salida clara.
============================================================ -->
<section class="hero hero-small" aria-label="Error 404">
    <div class="container position-relative text-center">

        <!-- Código de error -->
        <h1 class="hero-title mb-2" style="font-size:5rem;">404</h1>

        <!-- Mensaje explicativo -->
        <p class="hero-subtitle">
            La página que buscas no existe o fue movida.
        </p>

        <!-- Acción principal para volver al inicio -->
        <a href="index.php" class="btn btn-principal mt-3 px-5">
            <i class="bi bi-house-door me-2"></i>Volver al inicio
        </a>
    </div>
</section>

<?php
// Carga el pie de página global del sitio
require_once __DIR__ . '/includes/footer.php';
?>