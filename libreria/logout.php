<?php
// ============================================================
// logout.php
// Cierra la sesión del administrador de forma segura
// ============================================================


// ---- INCLUDES ----
// Carga funciones de autenticación (manejo de sesión)
require_once __DIR__ . '/helpers/auth.php';


// ---- INICIAR SESIÓN ----
// Necesario para poder acceder y destruir datos de sesión
iniciarSesion();


// ---- LIMPIAR VARIABLES DE SESIÓN ----
// Elimina todas las variables almacenadas en $_SESSION
session_unset();


// ---- DESTRUIR SESIÓN ----
// Elimina completamente la sesión del servidor
session_destroy();


// ---- REDIRECCIÓN ----
// Envía al usuario al login después de cerrar sesión
header('Location: login.php');
exit;