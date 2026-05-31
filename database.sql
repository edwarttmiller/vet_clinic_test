CREATE DATABASE IF NOT EXISTS vet_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vet_clinic;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    animal_type VARCHAR(50) NOT NULL,
    complaint TEXT NOT NULL,
    visit_date DATE NOT NULL,
    payment_method ENUM('cash', 'card') NOT NULL,
    status ENUM('new', 'confirmed', 'accepted', 'done') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    review_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Администратор (пароль VetNet2026, проверяется напрямую в login.php для роли admin)
INSERT INTO users (login, password, full_name, phone, email, role)
VALUES ('Admin', 'VetNet2026', 'Администратор', '8(000)000-00-00', 'admin@vet.ru', 'admin');
