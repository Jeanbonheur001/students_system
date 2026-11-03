-- Optional manual setup (the PHP app creates all of this automatically).
-- Import in phpMyAdmin if you prefer.

CREATE DATABASE IF NOT EXISTS practice_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practice_db;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    reg_number    VARCHAR(30)  NOT NULL UNIQUE,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(30)  NULL,
    password_hash VARCHAR(255) NOT NULL,
    photo         VARCHAR(255) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reg_number  VARCHAR(30)  NOT NULL UNIQUE,
    full_name   VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NULL,
    department  VARCHAR(100) NOT NULL DEFAULT 'ICT',
    year_level  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(120) NOT NULL,
    ip_address VARCHAR(45)  NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO students (reg_number, full_name, email, phone, department, year_level) VALUES
('25RP14880','Alice Uwase','alice.uwase@example.com','0788000001','ICT',1),
('25RP14881','Eric Mugisha','eric.mugisha@example.com','0788000002','ICT',1),
('25RP14882','Diane Ingabire','diane.ingabire@example.com','0788000003','Networking',1),
('25RP14883','Patrick Habimana','patrick.habimana@example.com','0788000004','Software Dev',1),
('25RP14884','Grace Mukamana','grace.mukamana@example.com','0788000005','ICT',1),
('25RP14885','Joseph Niyonzima','joseph.niyonzima@example.com','0788000006','Networking',1),
('25RP14886','Sandrine Uwimana','sandrine.uwimana@example.com','0788000007','Software Dev',1),
('25RP14887','Claude Nshuti','claude.nshuti@example.com','0788000008','ICT',1),
('25RP14888','Aline Mutesi','aline.mutesi@example.com','0788000009','Networking',1),
('25RP14890','David Kamanzi','david.kamanzi@example.com','0788000010','Software Dev',1);
