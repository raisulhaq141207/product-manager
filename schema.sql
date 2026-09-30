CREATE DATABASE IF NOT EXISTS product_manager
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE product_manager;

CREATE TABLE IF NOT EXISTS products (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  category   VARCHAR(50)   NOT NULL,
  price      DECIMAL(12,2) NOT NULL,
  stock      INT UNSIGNED  NOT NULL DEFAULT 0,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_products_name (name)   -- lapisan kedua untuk nama unik
) ENGINE=InnoDB;
