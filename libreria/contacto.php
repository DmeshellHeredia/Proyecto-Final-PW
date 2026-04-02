<?php
// Inicia sesión para manejar datos temporales del formulario
session_start();

// Incluye helpers de autenticación (CSRF, etc.)
require_once __DIR__ . '/helpers/auth.php';


// ---- METADATA DE LA PÁGINA ----
$pageTitle = 'Contacto';
$metaDesc  = 'Contáctanos con tus preguntas, sugerencias o comentarios.';


// ---- ESTADO DEL FORMULARIO ----
// Verifica si el formulario fue enviado correctamente
$enviado = isset($_GET['ok']) && $_GET['ok'] === '1';

// Obtiene código de error si existe
$error   = $_GET['error'] ?? '';


// ---- MENSAJES DE ERROR ----
// Traduce códigos de error a mensajes legibles
$mensajeError = match ($error) {
    'campos_vacios'    => 'Debes completar todos los campos obligatorios.',
    'correo_invalido'  => 'El correo electrónico ingresado no es válido.',
    'nombre_largo'     => 'El nombre no puede superar los 100 caracteres.',
    'correo_largo'     => 'El correo no puede superar los 100 caracteres.',
    'asunto_largo'     => 'El asunto no puede superar los 150 caracteres.',
    'comentario_corto' => 'El comentario debe tener al menos 10 caracteres.',
    'comentario_largo' => 'El comentario no puede superar los 2000 caracteres.',
    'spam'             => 'Tu mensaje fue detectado como spam. Inténtalo de nuevo.',
    'limite'           => 'Por favor espera un momento antes de enviar otro mensaje.',
    'db'               => 'Ocurrió un error al guardar tu mensaje. Inténtalo nuevamente.',
    default            => ''
};


// ---- RECUPERAR DATOS DEL FORMULARIO ----
// Mantiene los datos si hubo error (mejora UX)
$formulario = $_SESSION['form_contacto'] ?? [
    'nombre'=>'',
    'correo'=>'',
    'asunto'=>'',
    'comentario'=>''
];

// Asigna valores individuales
$valorNombre     = $formulario['nombre'];
$valorCorreo     = $formulario['correo'];
$valorAsunto     = $formulario['asunto'];
$valorComentario = $formulario['comentario'];


// Si se envió correctamente, limpia los datos guardados
if ($enviado) {
    unset($_SESSION['form_contacto']);
    $valorNombre = $valorCorreo = $valorAsunto = $valorComentario = '';
}


// ---- SEGURIDAD: TOKEN CSRF ----
// Genera token para prevenir ataques CSRF
$csrfToken = generarTokenCSRF();


// ---- INCLUDES DE LAYOUT ----
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- HEADER -->
<!-- Sección visual superior -->
<section class="hero hero-small" aria-label="Formulario de contacto">
    <div class="container position-relative">

        <!-- Breadcrumb (navegación) -->
        <nav aria-label="Ruta de navegación" class="mb-2">
            <ol class="breadcrumb breadcrumb-blanco">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-blanco-70">Inicio</a>
                </li>
                <li class="breadcrumb-item active text-dorado-claro">Contacto</li>
            </ol>
        </nav>

        <!-- Título principal -->
        <h1 class="hero-title mb-1">
            <i class="bi bi-envelope me-2"></i>
            Formulario de <span>Contacto</span>
        </h1>

        <!-- Subtítulo -->
        <p class="hero-subtitle mb-0">
            Escríbenos con tus preguntas, sugerencias o comentarios.
        </p>
    </div>
</section>


<!-- CONTENIDO PRINCIPAL -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">

            <div class="col-lg-7">

                <!-- MENSAJE DE ERROR -->
                <?php if ($mensajeError): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4"
                         role="alert">
                        <?= htmlspecialchars($mensajeError) ?>
                    </div>
                <?php endif; ?>


                <!-- MENSAJE DE ÉXITO -->
                <?php if ($enviado): ?>
                    <div class="alert alert-success">
                        <strong>¡Mensaje enviado con éxito!</strong>
                    </div>
                <?php endif; ?>


                <!-- TARJETA DEL FORMULARIO -->
                <div class="form-card">

                    <!-- FORMULARIO -->
                    <form id="formContacto"
                          action="guardar_contacto.php"
                          method="POST"
                          novalidate>

                        <!-- TOKEN CSRF -->
                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- HONEYPOT (ANTI-SPAM) -->
                        <!-- Campo oculto que los bots suelen llenar -->
                        <div style="position:absolute;left:-9999px;">
                            <input type="text" name="website">
                        </div>


                        <!-- CAMPO: NOMBRE -->
                        <input type="text"
                               name="nombre"
                               value="<?= htmlspecialchars($valorNombre) ?>"
                               required maxlength="100">


                        <!-- CAMPO: CORREO -->
                        <input type="email"
                               name="correo"
                               value="<?= htmlspecialchars($valorCorreo) ?>"
                               required maxlength="100">


                        <!-- CAMPO: ASUNTO -->
                        <input type="text"
                               name="asunto"
                               value="<?= htmlspecialchars($valorAsunto) ?>"
                               required maxlength="150">


                        <!-- CAMPO: COMENTARIO -->
                        <textarea name="comentario"
                                  required maxlength="2000"><?= htmlspecialchars($valorComentario) ?></textarea>


                        <!-- BOTÓN -->
                        <button type="submit">
                            Enviar mensaje
                        </button>

                    </form>
                </div>
            </div>


            <!-- INFO DE CONTACTO -->
            <div class="col-lg-4">
                <div class="info-card">
                    <h5>Información de contacto</h5>

                    <!-- Datos estáticos -->
                    <ul>
                        <li>Email: LibreriaAurea@academica.edu</li>
                        <li>Tel: +1 (809) 555-0100</li>
                        <li>Ubicación: Santo Domingo</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</section>


<?php
// Footer del sitio
require_once __DIR__ . '/includes/footer.php';
?>