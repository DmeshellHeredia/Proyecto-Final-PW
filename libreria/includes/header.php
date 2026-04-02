<?php
// ============================================================
//  LAYOUT / HEAD GLOBAL
// ------------------------------------------------------------
//  Configuración base del documento HTML:
//  - título de página
//  - descripción SEO
//  - detección de contexto (público/admin)
//  - carga de estilos, fuentes e íconos
//  - aplicación temprana del tema visual
// ============================================================

// Título por defecto de la página si no fue definido previamente
$pageTitle = $pageTitle ?? 'Portal';

// Descripción SEO por defecto
$metaDesc  = $metaDesc ?? 'Librería Áurea — Portal académico de libros y autores. Consulta nuestro catálogo bibliográfico.';

// Ruta base opcional para soportar distintas ubicaciones de archivos
$basePath  = $basePath ?? '';

// ------------------------------------------------------------
// Detectar si la ruta actual pertenece al panel administrativo
// ------------------------------------------------------------

// Normaliza separadores de ruta para compatibilidad entre sistemas
$rutaActual = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');

// Determina si la URL actual pertenece al directorio /admin/
$esAdmin    = str_contains($rutaActual, '/admin/');

// ------------------------------------------------------------
// Clave de almacenamiento del tema
// ------------------------------------------------------------

// Se usa una clave distinta en localStorage para separar
// el tema visual del sitio público y el del panel admin
$claveTema = $esAdmin ? 'tema_admin' : 'tema_publico';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- =====================================================
         SEO BÁSICO
         -----------------------------------------------------
         Título y descripción para buscadores
    ====================================================== -->
    <title>Librería Áurea &mdash; <?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta name="robots" content="index, follow">

    <!-- =====================================================
         OPEN GRAPH
         -----------------------------------------------------
         Metadatos para compartir en redes sociales
    ====================================================== -->
    <meta property="og:title"       content="Librería Áurea — <?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="es_DO">

    <!-- Favicon inline en formato SVG con emoji -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">

    <!-- =====================================================
         LIBRERÍAS EXTERNAS
         -----------------------------------------------------
         Framework CSS, iconos y tipografías
    ====================================================== -->

    <!-- Bootstrap 5 desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Google Fonts: tipografías del proyecto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap"
          rel="stylesheet">

    <!-- Hoja de estilos personalizada del proyecto -->
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/styles.css">

    <!-- =====================================================
         TEMA VISUAL
         -----------------------------------------------------
         Aplica el tema guardado en localStorage antes de que
         se renderice la página, evitando el "flash" visual
         entre modo claro y oscuro.
    ====================================================== -->
    <script>
        (function () {
            // Clave según contexto: admin o público
            var claveTema = <?= json_encode($claveTema) ?>;

            // Recupera el tema guardado o usa light por defecto
            var tema = localStorage.getItem(claveTema) || 'light';

            // Aplica el tema al elemento raíz del documento
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
</head>
<body>