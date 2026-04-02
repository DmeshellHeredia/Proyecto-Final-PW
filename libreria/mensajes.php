<?php
// ============================================================
// mensajes.php
// Punto de acceso protegido para la gestión de mensajes.
// Actualmente redirige al login si se intenta acceder directamente.
// ============================================================


// ---- SEGURIDAD BÁSICA ----
// Este archivo NO permite acceso directo.
// Redirige al usuario al login para evitar acceso no autorizado.
header('Location: login.php');


// ---- FINALIZAR EJECUCIÓN ----
// Se asegura de que no se ejecute ningún código adicional.
exit;