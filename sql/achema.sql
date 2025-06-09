-- Usar la base de datos (asumiendo que mi_base ya existe)
USE mi_base;

-- T// ... (USE mi_base; va aquí arriba) ...

-- Tabla de Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insertar roles básicos si no existen (incluido 'empleado')
INSERT IGNORE INTO roles (nombre_rol, descripcion) VALUES
('admin', 'Administrador con todos los permisos'),
('cliente', 'Cliente que puede solicitar citas y ver su historial'),
('empleado', 'Empleado que puede gestionar citas y servicios asignados');

-- ... (el resto del script para modificar usuarios, añadir otras tablas, etc.) ...