<?php
// ============================================================
// guardar_contacto.php
// Recibe, valida y guarda el formulario de contacto.
// Incluye protección CSRF, honeypot anti-spam y rate limiting.
// ============================================================

// Inicia sesión para usar variables temporales ($_SESSION)
session_start();

// Incluye la conexión a la base de datos
require_once __DIR__ . '/config/database.php';

// Incluye funciones auxiliares de autenticación/seguridad
require_once __DIR__ . '/helpers/auth.php';


// ---- Funciones auxiliares ----

/**
 * Guarda temporalmente los datos del formulario en sesión.
 * Sirve para repoblar el formulario si ocurre un error.
 */
function guardarEnSesion(string $n, string $c, string $a, string $co): void {
    $_SESSION['form_contacto'] = [
        'nombre' => $n,
        'correo' => $c,
        'asunto' => $a,
        'comentario' => $co
    ];
}

/**
 * Limpia los datos temporales del formulario guardados en sesión.
 */
function limpiarSesion(): void {
    unset($_SESSION['form_contacto']);
}

/**
 * Redirige al formulario con un código de error.
 * Antes de redirigir, guarda los datos ingresados para no perderlos.
 */
function error(string $codigo, string $n='', string $c='', string $a='', string $co=''): void {
    guardarEnSesion($n, $c, $a, $co);
    header('Location: contacto.php?error=' . urlencode($codigo));
    exit;
}


// ---- 1. Solo aceptar solicitudes POST ----
// Evita que se acceda directamente al archivo desde la URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}


// ---- 2. Verificar token CSRF ----
// Obtiene el token enviado por el formulario
$tokenRecibido = $_POST['csrf_token'] ?? '';

// Obtiene el token almacenado en sesión
$tokenSesion   = $_SESSION['csrf_token'] ?? '';

// Compara ambos tokens de forma segura
if (!hash_equals($tokenSesion, $tokenRecibido)) {
    // Invalida el token de sesión por seguridad
    unset($_SESSION['csrf_token']);

    // Trata el intento como sospechoso
    error('spam');
}

// Invalida el token después de usarlo para evitar reutilización
unset($_SESSION['csrf_token']);


// ---- 3. Honeypot anti-spam ----
// Si el campo oculto viene lleno, probablemente es un bot.
if (!empty($_POST['website'])) {
    // Simula éxito silencioso para no dar pistas al bot
    header('Location: contacto.php?ok=1');
    exit;
}


// ---- 4. Rate limiting ----
// Permite solo 1 mensaje por cada 60 segundos por sesión.
$ahora  = time();
$ultimo = $_SESSION['ultimo_contacto'] ?? 0;

if (($ahora - $ultimo) < 60) {
    error('limite');
}


// ---- 5. Recoger y limpiar datos del formulario ----
// Usa trim() para eliminar espacios al inicio y al final.
$nombre     = trim($_POST['nombre'] ?? '');
$correo     = trim($_POST['correo'] ?? '');
$asunto     = trim($_POST['asunto'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');

// Guarda los datos en sesión por si luego falla alguna validación
guardarEnSesion($nombre, $correo, $asunto, $comentario);


// ---- 6. Validar campos obligatorios ----
// Verifica que ningún campo requerido esté vacío.
if ($nombre === '' || $correo === '' || $asunto === '' || $comentario === '') {
    error('campos_vacios', $nombre, $correo, $asunto, $comentario);
}


// ---- 7. Validar formato del correo electrónico ----
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    error('correo_invalido', $nombre, $correo, $asunto, $comentario);
}


// ---- 8. Validar longitudes ----
// Evita datos demasiado largos o demasiado cortos.
if (mb_strlen($nombre) > 100) {
    error('nombre_largo', $nombre, $correo, $asunto, $comentario);
}

if (mb_strlen($correo) > 100) {
    error('correo_largo', $nombre, $correo, $asunto, $comentario);
}

if (mb_strlen($asunto) > 150) {
    error('asunto_largo', $nombre, $correo, $asunto, $comentario);
}

if (mb_strlen($comentario) < 10) {
    error('comentario_corto', $nombre, $correo, $asunto, $comentario);
}

if (mb_strlen($comentario) > 2000) {
    error('comentario_largo', $nombre, $correo, $asunto, $comentario);
}


// ---- 9. Insertar en la base de datos ----
// Usa PDO y sentencias preparadas para evitar SQL Injection.
try {
    // Obtiene la conexión PDO
    $pdo = getConexion();

    // Prepara la consulta SQL
    $stmt = $pdo->prepare(
        "INSERT INTO contacto (fecha, correo, nombre, asunto, comentario)
         VALUES (NOW(), :correo, :nombre, :asunto, :comentario)"
    );

    // Asocia cada valor a su parámetro correspondiente
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':asunto', $asunto, PDO::PARAM_STR);
    $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);

    // Ejecuta la inserción
    $stmt->execute();

    // Guarda el momento del último envío exitoso
    $_SESSION['ultimo_contacto'] = time();

    // Limpia los datos temporales del formulario
    limpiarSesion();

    // Redirige con confirmación de éxito
    header('Location: contacto.php?ok=1');
    exit;

} catch (PDOException $e) {
    // Registra el error técnico en logs del servidor
    error_log('Error en guardar_contacto.php: ' . $e->getMessage());

    // Redirige con error amigable para el usuario
    error('db', $nombre, $correo, $asunto, $comentario);
}