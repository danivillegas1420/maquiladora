-- Crear tabla progreso_bulto_operacion para rastrear progreso por bulto individual

CREATE TABLE IF NOT EXISTS `progreso_bulto_operacion` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bultoId` INT(11) UNSIGNED NOT NULL,
    `operacionControlId` INT(11) UNSIGNED NOT NULL,
    `completado` TINYINT(1) DEFAULT 0,
    `cantidad_completada` INT(11) DEFAULT 0,
    `empleadoId` INT(11) UNSIGNED NULL,
    `fecha_completado` DATETIME NULL,
    `observaciones` TEXT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `bultoId` (`bultoId`),
    KEY `operacionControlId` (`operacionControlId`),
    UNIQUE KEY `unique_bulto_operacion` (`bultoId`, `operacionControlId`),
    CONSTRAINT `progreso_bulto_operacion_bultoId_foreign` 
        FOREIGN KEY (`bultoId`) REFERENCES `bultos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `progreso_bulto_operacion_operacionControlId_foreign` 
        FOREIGN KEY (`operacionControlId`) REFERENCES `operaciones_control` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
