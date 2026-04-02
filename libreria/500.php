<?php
// ============================================================
//  ERROR 500 — ERROR INTERNO DEL SERVIDOR
// ------------------------------------------------------------
//  Este archivo se muestra cuando ocurre un fallo inesperado
//  en el servidor (errores de código, base de datos, etc.).
// ============================================================

// Envía el código HTTP 500 (error interno del servidor)
http_response_code(500);

// Título de la página para el layout
$pageTitle = 'Error del servidor';

// Carga el encabezado global del sitio
require_once __DIR__ . '/includes/header.php';

// Carga la barra de navegación principal
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- ============================================================
     SECCIÓN PRINCIPAL DEL ERROR 500
     ------------------------------------------------------------
     Mensaje amigable para el usuario cuando ocurre un error
     interno del sistema.
============================================================ -->
<section class="hero hero-small" aria-label="Error del servidor">
    <div class="container position-relative text-center">

        <!-- Código de error -->
        <h1 class="hero-title mb-2" style="font-size:5rem;">500</h1>

        <!-- Mensaje explicativo -->
        <p class="hero-subtitle">
            Ocurrió un error interno. Inténtalo más tarde.
        </p>

        <!-- Acción principal -->
        <a href="index.php" class="btn btn-principal mt-3 px-5">
            <i class="bi bi-house-door me-2"></i>Volver al inicio
        </a>
    </div>
</section>

<?php
// Carga el pie de página global del sitio
require_once __DIR__ . '/includes/footer.php';
?>