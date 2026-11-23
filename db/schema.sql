-- db/schema.sql
-- База данных: theatre_mvp
CREATE DATABASE IF NOT EXISTS `theatre_mvp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `theatre_mvp`;

-- 1. Роли
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL
);

-- 2. Пользователи
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `login` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(64) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `role_id` INT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

-- 3. Настройки (для админа)
CREATE TABLE `admin_setting` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(128) NOT NULL UNIQUE,
  `value` VARCHAR(255) NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Залы
CREATE TABLE `hall` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
);

-- 5. Спектакли
CREATE TABLE `performance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `duration_min` INT NOT NULL,
  `description` TEXT,
  `genre` VARCHAR(128),
  `age_rating` VARCHAR(16),
  `base_price` DECIMAL(10,2) NOT NULL
);

-- 6. Заявки (Orders)
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `performance_id` INT NOT NULL,
  `tickets_count` INT NOT NULL,
  `payment_method` VARCHAR(32) NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_to` VARCHAR(255),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`performance_id`) REFERENCES `performance`(`id`)
);

-- 7. Логи
CREATE TABLE `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attempted_login` VARCHAR(64),
  `ip_address` VARCHAR(45),
  `reason` VARCHAR(255)
);

-- Тестовые данные (для проверки работы)
INSERT INTO `roles` (`code`, `name`) VALUES 
('admin', 'Администратор'), 
('user', 'Пользователь');

INSERT INTO `hall` (`name`) VALUES ('Большой зал'), ('Малый зал');

INSERT INTO `performance` (`title`, `duration_min`, `description`, `genre`, `age_rating`, `base_price`) VALUES
('Лебединое озеро', 120, 'Классический балет', 'Балет', '6+', 2500.00),
('Щелкунчик', 90, 'Новогодняя сказка', 'Балет', '0+', 2000.00),
('Ревизор', 150, 'Комедия по Гоголю', 'Комедия', '12+', 1500.00);

-- Админ (пароль: admin123)
INSERT INTO `users` (`login`, `password`, `full_name`, `phone`, `email`, `role_id`) VALUES
('admin', '$2y$10$e.wXqWq9Q1yQ1yQ1yQ1yQeQ1yQ1yQ1yQ1yQ1yQ1yQ1yQ1yQ1yQ1y', 'Администратор', '+79990000000', 'admin@theatre.ru', 1);
-- Примечание: пароль 'admin123' нужно будет захэшировать на сервере, этот хэш для примера.
