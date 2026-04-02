<?php
// ============================================================
// NAVBAR ADMINISTRATIVO — Librería Áurea
// ------------------------------------------------------------
// Barra de navegación principal del panel admin.
// Incluye navegación, estado activo, usuario, tema y logout.
// ============================================================

// Obtiene el nombre del archivo actual (ej: index.php)
$archivoAdmin = basename($_SERVER['PHP_SELF']);

/**
 * Determina si un enlace del menú debe marcarse como activo.
 *
 * @param string $archivo Archivo del enlace
 * @param string $actual  Archivo actual
 * @return string Clase CSS 'active' o vacío
 */
function esActivoAdmin(string $archivo, string $actual): string {
    return $archivo === $actual ? 'active' : '';
}
?>

<!-- ============================================================
     NAVBAR PRINCIPAL
============================================================ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top"
     id="navbarAdmin"
     aria-label="Administración">

    <div class="container-fluid px-4">

        <!-- Logo + Branding -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-book-half"></i>
            <span>Librería<strong>Áurea</strong></span>

            <!-- Badge que indica entorno ADMIN -->
            <span class="badge ms-1"
                  style="background:var(--dorado);color:var(--verde-oscuro);font-size:.65rem;letter-spacing:.06em;">
                ADMIN
            </span>
        </a>

        <!-- Botón hamburguesa (responsive) -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navAdmin"
                aria-controls="navAdmin"
                aria-expanded="false"
                aria-label="Menú de administración">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido colapsable -->
        <div class="collapse navbar-collapse" id="navAdmin">

            <!-- ================= MENÚ IZQUIERDO ================= -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= esActivoAdmin('index.php', $archivoAdmin) ?>" href="index.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <!-- Mensajes -->
                <li class="nav-item">
                    <a class="nav-link <?= esActivoAdmin('mensajes.php', $archivoAdmin) ?>" href="mensajes.php">
                        <i class="bi bi-chat-dots me-1"></i>Mensajes
                    </a>
                </li>

                <!-- ================= DROPDOWN LIBROS ================= -->
                <li class="nav-item dropdown">
                    <span class="d-flex align-items-center">

                        <!-- Link principal -->
                        <a class="nav-link pe-1 <?= in_array($archivoAdmin, ['libros.php','libro_crear.php','libro_editar.php','libro_eliminar.php']) ? 'active' : '' ?>"
                           href="libros.php">
                            <i class="bi bi-journals me-1"></i>Libros
                        </a>

                        <!-- Botón dropdown -->
                        <a class="nav-link px-1 dropdown-toggle dropdown-toggle-split"
                           href="#"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           aria-label="Opciones de libros">
                        </a>

                        <!-- Menú desplegable -->
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="libros.php">
                                <i class="bi bi-list-ul me-2"></i>Ver todos</a></li>

                            <li><a class="dropdown-item" href="libro_crear.php">
                                <i class="bi bi-plus-circle me-2"></i>Crear libro</a></li>
                        </ul>
                    </span>
                </li>

                <!-- ================= DROPDOWN AUTORES ================= -->
                <li class="nav-item dropdown">
                    <span class="d-flex align-items-center">

                        <a class="nav-link pe-1 <?= in_array($archivoAdmin, ['autores.php','autor_crear.php','autor_editar.php','autor_eliminar.php']) ? 'active' : '' ?>"
                           href="autores.php">
                            <i class="bi bi-people me-1"></i>Autores
                        </a>

                        <a class="nav-link px-1 dropdown-toggle dropdown-toggle-split"
                           href="#"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           aria-label="Opciones de autores">
                        </a>

                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="autores.php">
                                <i class="bi bi-list-ul me-2"></i>Ver todos</a></li>

                            <li><a class="dropdown-item" href="autor_crear.php">
                                <i class="bi bi-plus-circle me-2"></i>Crear autor</a></li>
                        </ul>
                    </span>
                </li>

                <!-- Exportación -->
                <li class="nav-item">
                    <a class="nav-link <?= esActivoAdmin('exportar_mensajes.php', $archivoAdmin) ?>" href="exportar_mensajes.php">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Exportar
                    </a>
                </li>

            </ul>

            <!-- ================= MENÚ DERECHO ================= -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-1 align-items-center">

                <!-- Enlace al sitio público -->
                <li class="nav-item">
                    <a class="nav-link" href="../index.php" target="_blank" title="Ver sitio público">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Sitio público
                    </a>
                </li>

                <!-- Botón modo oscuro -->
                <li class="nav-item d-flex align-items-center ms-2">
                    <button id="btnTemaAdmin"
                            class="btn btn-sm btn-outline-warning rounded-circle p-1"
                            aria-label="Alternar modo oscuro del panel admin"
                            title="Cambiar tema del admin"
                            style="width:34px;height:34px;">
                        <i class="bi bi-moon-fill" id="iconoTemaAdmin"></i>
                    </button>
                </li>

                <!-- Nombre del usuario autenticado -->
                <li class="nav-item">
                    <span class="nav-link small" style="color:var(--dorado-claro);">
                        <i class="bi bi-person-circle me-1"></i><?= nombreAdmin() ?>
                    </span>
                </li>

                <!-- Botón logout (abre modal) -->
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#modalCerrarSesion"
                       role="button"
                       aria-label="Cerrar sesión">
                        <i class="bi bi-box-arrow-right me-1"></i>Salir
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- ============================================================
     MODAL: CONFIRMACIÓN DE CIERRE DE SESIÓN
============================================================ -->

<!-- Modal Bootstrap para confirmar logout -->
<div class="modal fade" id="modalCerrarSesion" tabindex="-1"
     aria-labelledby="modalLogoutTitulo"
     aria-modal="true"
     role="dialog">

    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">

        <div class="modal-content"
             style="border:none;border-radius:.75rem;border-top:4px solid var(--dorado);overflow:hidden;">

            <!-- Header -->
            <div class="modal-header border-0 pb-0" style="background:var(--verde-oscuro);">
                <h5 class="modal-title text-white ms-auto me-auto ps-4"
                    id="modalLogoutTitulo">
                    Cerrar sesión
                </h5>

                <!-- Botón cerrar -->
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Cancelar"></button>
            </div>

            <!-- Body -->
            <div class="modal-body text-center py-4" style="background:var(--verde-oscuro);">

                <!-- Icono -->
                <div class="mb-3">
                    <span style="display:inline-flex;align-items:center;justify-content:center;
                                 width:64px;height:64px;border-radius:50%;
                                 background:rgba(201,168,76,.15);border:2px solid var(--dorado);">
                        <i class="bi bi-box-arrow-right" style="font-size:1.75rem;color:var(--dorado-claro);"></i>
                    </span>
                </div>

                <!-- Mensaje -->
                <p class="mb-1" style="color:rgba(255,255,255,.9);font-weight:500;">
                    ¿Estás seguro de que deseas salir?
                </p>

                <p class="mb-0 small" style="color:rgba(255,255,255,.55);">
                    Tu sesión se cerrará y serás redirigido al login.
                </p>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 justify-content-center gap-3 py-4"
                 style="background:var(--verde-oscuro);">

                <!-- Cancelar -->
                <button type="button"
                        class="btn btn-outline-light px-4"
                        data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancelar
                </button>

                <!-- Confirmar logout -->
                <a href="../logout.php"
                   class="btn px-4"
                   style="background:var(--dorado);color:var(--verde-oscuro);font-weight:700;">
                    <i class="bi bi-box-arrow-right me-1"></i>Sí, salir
                </a>

            </div>

        </div>
    </div>
</div>