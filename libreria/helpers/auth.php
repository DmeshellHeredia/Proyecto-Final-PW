<?php
// ============================================================
//  helpers/auth.php — Funciones de autenticación por sesión
// ============================================================

/**
 * Inicia la sesión de PHP si todavía no ha sido iniciada.
 * Esto evita errores por intentar abrir la sesión varias veces.
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Comprueba si existe una sesión válida de administrador.
 *
 * @return bool True si el usuario está autenticado, false en caso contrario.
 */
function estaAutenticado(): bool
{
    iniciarSesion();

    // Se considera autenticado si existe el ID del admin
    // y además hay un nombre de usuario asociado en sesión.
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_usuario']);
}

/**
 * Obliga a que el usuario esté autenticado como administrador.
 * Si no lo está, lo redirige al formulario de login.
 *
 * @param string $rutaLogin Ruta hacia la página de inicio de sesión.
 */
function requerirAdmin(string $rutaLogin = '../login.php'): void
{
    if (!estaAutenticado()) {
        header('Location: ' . $rutaLogin);
        exit;
    }
}

/**
 * Genera un token CSRF y lo guarda en sesión si todavía no existe.
 * Este token se usa para proteger formularios contra ataques CSRF.
 *
 * @return string Token CSRF listo para insertarse en formularios.
 */
function generarTokenCSRF(): string
{
    iniciarSesion();

    // Solo genera un nuevo token si todavía no existe uno en sesión
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verifica que el token CSRF enviado por POST coincida con el token guardado en sesión.
 * Si no coincide, se bloquea la petición con error 403.
 */
function verificarCSRF(): void
{
    iniciarSesion();

    // Obtiene el token enviado desde el formulario
    $token = $_POST['csrf_token'] ?? '';

    // Compara de forma segura para evitar ataques por timing
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token de seguridad inválido. Vuelve atrás e intenta de nuevo.');
    }

    // Invalida el token después de usarlo para evitar reutilización
    unset($_SESSION['csrf_token']);
}

/**
 * Verifica un campo honeypot anti-spam.
 * Si el campo oculto tiene contenido, probablemente la petición proviene de un bot.
 *
 * @param string $campo Nombre del campo honeypot.
 * @return bool True si pasa la validación, false si parece spam.
 */
function verificarHoneypot(string $campo = 'website'): bool
{
    return empty($_POST[$campo]);
}

/**
 * Aplica un control simple de frecuencia por sesión.
 * Evita que el usuario envíe formularios repetidamente en poco tiempo.
 *
 * @param string $clave Nombre de la clave usada en sesión.
 * @param int $segundos Tiempo mínimo requerido entre envíos.
 * @return bool True si se permite el envío, false si debe esperar.
 */
function verificarRateLimit(string $clave = 'ultimo_contacto', int $segundos = 60): bool
{
    iniciarSesion();

    $ahora = time();
    $ultimo = $_SESSION[$clave] ?? 0;

    // Si no ha pasado el tiempo mínimo, se bloquea el envío
    if (($ahora - $ultimo) < $segundos) {
        return false;
    }

    // Guarda el momento actual como último envío válido
    $_SESSION[$clave] = $ahora;
    return true;
}

/**
 * Devuelve el nombre del administrador autenticado.
 * Se escapa con htmlspecialchars para prevenir XSS al mostrarlo en HTML.
 *
 * @return string Nombre seguro para salida en vista.
 */
function nombreAdmin(): string
{
    return htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin');
}