<?php
// ============================================================
//  NAVBAR PRINCIPAL — SITIO PÚBLICO
// ------------------------------------------------------------
//  Barra de navegación del frontend.
//  Incluye:
//  - navegación entre secciones
//  - detección de enlace activo
//  - accesibilidad (ARIA)
//  - control de tema (modo oscuro)
// ============================================================

// Obtiene el nombre del archivo actual (ej: index.php)
$archivoActual = basename($_SERVER['PHP_SELF']);

/**
 * Determina si un enlace del menú está activo.
 *
 * @param string $archivo Nombre del archivo del enlace
 * @param string $actual  Archivo actual
 * @return string Clase CSS 'active' o vacío
 */
function esActivo(string $archivo, string $actual): string {
    return $archivo === $actual ? 'active' : '';
}
?>

<!-- ============================================================
     NAVBAR PRINCIPAL
============================================================ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top"
     id="navbarPrincipal"
     aria-label="Navegación principal">

    <div class="container">

        <!-- ================= MARCA / LOGO ================= -->
        <a class="navbar-brand d-flex align-items-center gap-2"
           href="index.php"
           aria-label="Librería Áurea — Inicio">

            <!-- Icono representativo -->
            <i class="bi bi-book-half fs-4" aria-hidden="true"></i>

            <!-- Nombre del sitio -->
            <span>Librería<strong>Áurea</strong></span>
        </a>

        <!-- ================= BOTÓN RESPONSIVE ================= -->
        <!-- Se muestra en pantallas pequeñas -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu"
                aria-controls="navMenu"
                aria-expanded="false"
                aria-label="Abrir o cerrar menú de navegación">

            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ================= MENÚ DE NAVEGACIÓN ================= -->
        <div class="collapse navbar-collapse" id="navMenu">

            <!-- Menú alineado a la derecha -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-1" role="menubar">

                <!-- Inicio -->
                <li class="nav-item" role="none">
                    <a class="nav-link <?= esActivo('index.php', $archivoActual) ?>"
                       href="index.php"
                       role="menuitem">
                        <i class="bi bi-house-door me-1" aria-hidden="true"></i>Inicio
                    </a>
                </li>

                <!-- Libros -->
                <li class="nav-item" role="none">
                    <a class="nav-link <?= esActivo('libros.php', $archivoActual) ?>"
                       href="libros.php"
                       role="menuitem">
                        <i class="bi bi-journals me-1" aria-hidden="true"></i>Libros
                    </a>
                </li>

                <!-- Autores -->
                <li class="nav-item" role="none">
                    <a class="nav-link <?= esActivo('autores.php', $archivoActual) ?>"
                       href="autores.php"
                       role="menuitem">
                        <i class="bi bi-people me-1" aria-hidden="true"></i>Autores
                    </a>
                </li>

                <!-- Contacto -->
                <li class="nav-item" role="none">
                    <a class="nav-link <?= esActivo('contacto.php', $archivoActual) ?>"
                       href="contacto.php"
                       role="menuitem">
                        <i class="bi bi-envelope me-1" aria-hidden="true"></i>Contacto
                    </a>
                </li>

                <!-- ================= BOTÓN MODO OSCURO ================= -->
                <li class="nav-item d-flex align-items-center ms-2" role="none">

                    <!-- Botón que alterna tema claro/oscuro -->
                    <button id="btnTema"
                            class="btn btn-sm btn-outline-warning rounded-circle p-1"
                            aria-label="Alternar modo oscuro"
                            title="Cambiar tema"
                            style="width:34px;height:34px;line-height:1;">

                        <!-- Icono dinámico (JS lo cambia) -->
                        <i class="bi bi-moon-fill"
                           id="iconoTema"
                           aria-hidden="true"></i>
                    </button>
                </li>

            </ul>
        </div>
    </div>
</nav>