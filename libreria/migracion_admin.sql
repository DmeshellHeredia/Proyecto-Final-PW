-- ============================================================
-- Librería Áurea — Migración de administración
-- Crea la tabla de usuarios administradores del sistema
-- ============================================================


-- ---- CREACIÓN DE TABLA: usuarios ----
-- Almacena credenciales y datos básicos de administradores
CREATE TABLE IF NOT EXISTS `usuarios` (

  -- ID único del usuario (clave primaria)
  `id` INT NOT NULL AUTO_INCREMENT,

  -- Nombre de usuario (login)
  `usuario` VARCHAR(50) NOT NULL,

  -- Hash de la contraseña (NO almacenar texto plano)
  `password_hash` VARCHAR(255) NOT NULL,

  -- Nombre completo o identificador visible
  `nombre` VARCHAR(100) NOT NULL DEFAULT 'Administrador',

  -- Fecha de creación del usuario
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- Clave primaria
  PRIMARY KEY (`id`),

  -- Restricción de unicidad para evitar usuarios duplicados
  UNIQUE KEY `usuario_unico` (`usuario`)

)
ENGINE=InnoDB -- Soporte de transacciones y claves foráneas
DEFAULT CHARSET=utf8mb4 -- Soporte completo de Unicode (incluye emojis)
COLLATE=utf8mb4_unicode_ci; -- Comparación de texto compatible con Unicode


-- ============================================================
-- NOTAS IMPORTANTES
-- ============================================================

-- 1. Nunca guardar contraseñas en texto plano.
--    Siempre usar password_hash() en PHP.

-- 2. El campo password_hash está preparado para algoritmos modernos
--    como bcrypt o Argon2.

-- 3. La clave única en 'usuario' evita duplicados.

-- 4. InnoDB permite escalar a relaciones con otras tablas (roles, logs, etc.).


-- ============================================================
-- CONFIGURACIÓN INICIAL
-- ============================================================

-- IMPORTANTE:
-- Ejecuta setup_admin.php para crear el primer usuario administrador.
-- Ejemplo de URL:
-- http://localhost/libreria/setup_admin.php

-- Luego ELIMINA ese archivo por seguridad.