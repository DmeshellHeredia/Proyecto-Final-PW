<!-- ============================================================
     FOOTER ADMINISTRATIVO
     ------------------------------------------------------------
     Pie de página del panel de administración.
     Incluye identidad visual, año dinámico y estilo corporativo.
============================================================ -->
<footer class="mt-auto py-3" style="background:var(--verde-oscuro);border-top:2px solid var(--dorado);">
    
    <!-- Contenedor fluido para ocupar todo el ancho -->
    <div class="container-fluid">
        
        <!-- Texto centrado con opacidad reducida para estilo discreto -->
        <p class="text-center mb-0 small" style="color:rgba(255,255,255,.4);">
            
            <!-- Icono de seguridad (Bootstrap Icons) -->
            <i class="bi bi-shield-lock me-1"></i>
            
            <!-- Nombre del sistema + contexto + año dinámico generado por PHP -->
            Librería Áurea &mdash; Panel de Administración &mdash; <?= date('Y') ?>
        </p>

    </div>
</footer>

<!-- ============================================================
     SCRIPTS
     ------------------------------------------------------------
     Carga de librerías JS necesarias para el funcionamiento UI
============================================================ -->

<!-- Bootstrap 5 JS Bundle:
     Incluye Popper + componentes interactivos (modal, collapse, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>

<!-- JavaScript personalizado:
     Maneja lógica de la app (buscador, validaciones, UI, etc.) -->
<script src="../assets/js/app.js"></script>

</body>
</html>