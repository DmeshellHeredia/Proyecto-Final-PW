<!-- ============================================================
     FOOTER DEL SITIO PÚBLICO
     ------------------------------------------------------------
     Pie de página principal con información del proyecto,
     navegación rápida y tecnologías utilizadas.
============================================================ -->
<footer class="site-footer mt-auto pt-5 pb-3">
    <div class="container">
        <div class="row gy-4">

            <!-- ================= DESCRIPCIÓN DEL PROYECTO ================= -->
            <div class="col-md-4">
                <h6 class="footer-brand mb-2">
                    <!-- Identidad visual del sitio -->
                    <i class="bi bi-book-half me-1" aria-hidden="true"></i> Librería Áurea
                </h6>

                <!-- Breve descripción del sistema -->
                <p class="footer-text small">
                    Portal académico de gestión de libros y autores.
                    Desarrollado con PHP, MySQL y Bootstrap.
                </p>
            </div>

            <!-- ================= NAVEGACIÓN RÁPIDA ================= -->
            <div class="col-md-4">
                <h6 class="footer-heading mb-2">Navegación</h6>

                <!-- Enlaces principales del sitio -->
                <ul class="list-unstyled footer-links small">
                    <li><a href="index.php"><i class="bi bi-chevron-right" aria-hidden="true"></i> Inicio</a></li>
                    <li><a href="libros.php"><i class="bi bi-chevron-right" aria-hidden="true"></i> Libros</a></li>
                    <li><a href="autores.php"><i class="bi bi-chevron-right" aria-hidden="true"></i> Autores</a></li>
                    <li><a href="contacto.php"><i class="bi bi-chevron-right" aria-hidden="true"></i> Contacto</a></li>
                </ul>
            </div>

            <!-- ================= TECNOLOGÍAS DEL PROYECTO ================= -->
            <div class="col-md-4">
                <h6 class="footer-heading mb-2">Tecnologías</h6>

                <!-- Badges con stack tecnológico -->
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge tech-badge">PHP 8</span>
                    <span class="badge tech-badge">MySQL / PDO</span>
                    <span class="badge tech-badge">Bootstrap 5</span>
                    <span class="badge tech-badge">JavaScript</span>
                    <span class="badge tech-badge">XAMPP</span>
                </div>
            </div>

        </div>

        <!-- Línea divisoria visual -->
        <hr class="footer-divider mt-4">

        <!-- Copyright + acceso discreto al panel administrador -->
        <p class="text-center footer-copy small mb-0">
            &copy; <?= date('Y') ?> Librería Áurea &mdash; Proyecto académico con fines educativos.

            <!-- Acceso al login del administrador -->
            <a href="login.php"
               class="footer-copy ms-2"
               style="opacity:.4;"
               aria-label="Acceso administrador">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
            </a>
        </p>
    </div>
</footer>

<!-- ============================================================
     SCRIPTS DEL SITIO
     ------------------------------------------------------------
     Carga de librerías y lógica personalizada del frontend
============================================================ -->

<!-- Bootstrap 5 JS Bundle:
     Incluye componentes interactivos como modal, collapse, dropdown, etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>

<!-- Script principal de la aplicación:
     Usa $basePath para adaptarse a distintas rutas del proyecto -->
<script src="<?= $basePath ?? '' ?>assets/js/app.js"></script>

</body>
</html>