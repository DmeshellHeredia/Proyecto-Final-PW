<?php
// ============================================================
//  Configuración de la base de datos (versión con .env)
// ============================================================

// ----------------------------------------------------------
// Cargar variables del archivo .env
// ----------------------------------------------------------

// Ruta al archivo .env (un nivel arriba del directorio actual)
$envPath = __DIR__ . '/../.env';

// Verifica si el archivo existe antes de intentar leerlo
if (file_exists($envPath)) {

    // Lee el archivo línea por línea, ignorando líneas vacías
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        // Ignora comentarios (líneas que empiezan con #)
        if (str_starts_with(trim($line), '#')) continue;

        // Divide la línea en clave y valor (KEY=VALUE)
        [$key, $value] = explode('=', $line, 2);

        // Guarda las variables en el arreglo global $_ENV
        $_ENV[trim($key)] = trim($value);
    }
}

// ----------------------------------------------------------
// Definir constantes desde variables de entorno
// ----------------------------------------------------------

// Define constantes usando valores del .env o valores por defecto
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');     // Servidor de base de datos
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');          // Puerto de MySQL
define('DB_NAME', $_ENV['DB_NAME'] ?? 'dblibreria');    // Nombre de la base de datos
define('DB_USER', $_ENV['DB_USER'] ?? 'root');          // Usuario de conexión
define('DB_PASS', $_ENV['DB_PASS'] ?? '');              // Contraseña
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4'); // Codificación de caracteres

/**
 * Crea y retorna una conexión PDO al servidor MySQL.
 *
 * @return PDO Objeto de conexión listo para usar
 * @throws PDOException Si ocurre un error al conectar
 */
function getConexion(): PDO
{
    // Construcción del DSN (Data Source Name)
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    // Opciones de configuración para PDO
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa consultas preparadas reales
    ];

    // Retorna la instancia de conexión PDO
    return new PDO($dsn, DB_USER, DB_PASS, $opciones);
}