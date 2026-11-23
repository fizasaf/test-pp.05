-- ============================================
-- БД: Theatre MVP
-- СУБД: MySQL
-- ============================================

CREATE DATABASE IF NOT EXISTS theatre_mvp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE theatre_mvp;

-- 1. Роли
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL
);

-- 2. Пользователи
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(64) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  role_id INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 3. Настройки админа
CREATE TABLE admin_setting (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(128) NOT NULL UNIQUE,
  `value` VARCHAR(255) NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Залы
CREATE TABLE hall (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
);

-- 5. Зоны мест
CREATE TABLE seat_zone (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hall_id INT NOT NULL,
  name VARCHAR(128) NOT NULL,
  price_mult DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  FOREIGN KEY (hall_id) REFERENCES hall(id)
);

-- 6. Места
CREATE TABLE seat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hall_id INT NOT NULL,
  seat_zone_id INT NOT NULL,
  `row` INT NOT NULL,
  number INT NOT NULL,
  FOREIGN KEY (hall_id) REFERENCES hall(id),
  FOREIGN KEY (seat_zone_id) REFERENCES seat_zone(id)
);

-- 7. Спектакли
CREATE TABLE performance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  duration_min INT NOT NULL,
  description TEXT,
  genre VARCHAR(128),
  age_rating VARCHAR(16),
  base_price DECIMAL(10,2) NOT NULL
);

-- 8. Сеансы (спектакль + дата + зал)
CREATE TABLE `show` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  performance_id INT NOT NULL,
  hall_id INT NOT NULL,
  started_at DATETIME NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'planned',
  FOREIGN KEY (performance_id) REFERENCES performance(id),
  FOREIGN KEY (hall_id) REFERENCES hall(id)
);

-- 9. Заявки (orders)
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  show_id INT NOT NULL,
  payment_method_id INT,
  status VARCHAR(32) NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  email_to VARCHAR(255),
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (show_id) REFERENCES `show`(id)
);

-- 10. Способы оплаты
CREATE TABLE payment_method (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL
);

-- 11. Платежи
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  method_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  paid_at DATETIME NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  transaction_ref VARCHAR(128),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (method_id) REFERENCES payment_method(id)
);

-- 12. Билеты
CREATE TABLE ticket (
  id INT AUTO_INCREMENT PRIMARY KEY,
  performance_id INT NOT NULL,
  order_id INT NOT NULL,
  seat_id INT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('reserved','sold','returned','blocked') NOT NULL DEFAULT 'reserved',
  FOREIGN KEY (performance_id) REFERENCES performance(id),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (seat_id) REFERENCES seat(id)
);

-- 13. Логи
CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  attempted_login VARCHAR(64),
  user_agent VARCHAR(255),
  ip_address VARCHAR(45),
  reason VARCHAR(255)
);

-- 14. Интеграции
CREATE TABLE integration (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(64) NOT NULL,
  provider VARCHAR(64) NOT NULL
);

-- 15. Бэкапы
CREATE TABLE backup_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  started_at DATETIME NOT NULL,
  finished_at DATETIME,
  status VARCHAR(32) NOT NULL,
  file_path VARCHAR(255),
  message VARCHAR(255)
);

-- ============================================
-- Базовые данные
-- ============================================

INSERT INTO roles (code, name) VALUES
  ('admin', 'Администратор'),
  ('user',  'Пользователь');

INSERT INTO payment_method (code, name) VALUES
  ('card',   'Банковская карта'),
  ('sbp',    'СБП'),
  ('online', 'Онлайн-оплата');

INSERT INTO hall (name) VALUES ('Большой зал'), ('Малый зал');

INSERT INTO seat_zone (hall_id, name, price_mult) VALUES
  (1, 'Партер', 1.50),
  (1, 'Балкон', 1.00),
  (2, 'Партер', 1.20);
