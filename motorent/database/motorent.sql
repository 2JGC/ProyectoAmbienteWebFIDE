
-- MotoRent Costa Rica - Script de base de datos
=

CREATE DATABASE IF NOT EXISTS motorent
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE motorent;

-- Tabla: usuarios

CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    apellidos       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL, -- hash generado con password_hash()
    telefono        VARCHAR(20)  NULL,
    cedula          VARCHAR(20)  NULL,
    rol             ENUM('cliente','administrador') NOT NULL DEFAULT 'cliente',
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    foto_perfil     VARCHAR(500) NULL,
    fecha_registro  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Tabla: motocicletas

CREATE TABLE motocicletas (
    id_moto         INT AUTO_INCREMENT PRIMARY KEY,
    marca           VARCHAR(60)  NOT NULL,
    modelo          VARCHAR(60)  NOT NULL,
    anio            YEAR         NOT NULL,
    cilindraje      INT          NOT NULL,
    categoria       VARCHAR(50)  NOT NULL,   -- Scooter, Naked, Enduro, etc.
    precio_dia      DECIMAL(10,2) NOT NULL,
    descripcion     TEXT NULL,
    imagen          VARCHAR(500) NULL,
    placa           VARCHAR(20)  NOT NULL UNIQUE,
    estado          ENUM('disponible','reservada','mantenimiento') NOT NULL DEFAULT 'disponible',
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Tabla: reservas

CREATE TABLE reservas (
    id_reserva      INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    id_moto         INT NOT NULL,
    fecha_inicio    DATE NOT NULL,
    fecha_fin       DATE NOT NULL,
    total_dias      INT NOT NULL,
    total_pago      DECIMAL(10,2) NOT NULL,
    estado          ENUM('pendiente','confirmada','en_curso','finalizada','cancelada') NOT NULL DEFAULT 'pendiente',
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_reserva_moto    FOREIGN KEY (id_moto)    REFERENCES motocicletas(id_moto) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Tabla: publicaciones (galería comunitaria)

CREATE TABLE publicaciones (
    id_publicacion  INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    titulo          VARCHAR(150) NOT NULL,
    descripcion     TEXT NULL,
    imagen          VARCHAR(500) NOT NULL,
    calificacion    TINYINT UNSIGNED NOT NULL DEFAULT 5,
    estado          ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_publicacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_publicacion_calificacion CHECK (calificacion BETWEEN 1 AND 5)
) ENGINE=InnoDB;


-- Tabla: contactos

CREATE TABLE contactos (
    id_contacto     INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    asunto          VARCHAR(150) NOT NULL,
    mensaje         TEXT NOT NULL,
    leido           TINYINT(1) NOT NULL DEFAULT 0,
    fecha_envio     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Tabla: password_resets

CREATE TABLE password_resets (
    id_reset        INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(150) NOT NULL,
    token           VARCHAR(255) NOT NULL,
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expira    DATETIME NOT NULL,
    usado           TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;


-- Datos iniciales: administrador por defecto
-- password: Admin123* 

INSERT INTO usuarios (nombre, apellidos, email, password, rol, estado)
VALUES ('Administrador', 'MotoRent', 'admin@motorent.cr',
'$2y$10$LeIP4swX6RmD356WJFMMPerTRCMH6dMP1ZjrOExuY//XgGX96s.rS', 'administrador', 'activo');
-- NOTA: el hash de ejemplo corresponde a "Admin123*"


-- Datos iniciales: motocicletas de ejemplo

INSERT INTO motocicletas (marca, modelo, anio, cilindraje, categoria, precio_dia, descripcion, imagen, placa, estado) VALUES
('Yamaha', 'XTZ 125', 2023, 125, 'Enduro', 18000.00, 'Ideal para caminos de lastre y ciudad.', 'default-moto.jpg', 'MOT-001', 'disponible'),
('Honda', 'CB 190R', 2022, 184, 'Naked', 20000.00, 'Ágil y económica, perfecta para la ciudad.', 'default-moto.jpg', 'MOT-002', 'disponible'),
('Suzuki', 'Gixxer 250', 2023, 249, 'Deportiva', 25000.00, 'Buen equilibrio entre potencia y manejo.', 'default-moto.jpg', 'MOT-003', 'disponible');


-- Migración para bases de datos creadas ANTES de agregar la calificación por
-- estrellas a la galería. Si la tabla `publicaciones` ya existe sin esta
-- columna, ejecutar únicamente la siguiente línea en phpMyAdmin:
-- ALTER TABLE publicaciones ADD COLUMN calificacion TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER imagen;
