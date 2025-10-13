-- Essential tables import for Nordic Skin
-- This file contains only the essential tables with proper MySQL syntax

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop existing tables if they exist (be careful!)
-- DROP TABLE IF EXISTS `images`;
-- DROP TABLE IF EXISTS `product_variants`;
-- DROP TABLE IF EXISTS `products`;
-- DROP TABLE IF EXISTS `categories`;
-- DROP TABLE IF EXISTS `users`;

-- Create categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255),
  `description` TEXT,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `gender` ENUM('male', 'female', 'unisex') NOT NULL DEFAULT 'unisex',
  `price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_variants table
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `color` VARCHAR(255),
  `size` VARCHAR(255),
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create images table (this is the most important for your image issue)
CREATE TABLE IF NOT EXISTS `images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `url` VARCHAR(500) NOT NULL,
  `alt_text` VARCHAR(255),
  `sort_order` INT NOT NULL DEFAULT 0,
  `imageable_type` VARCHAR(255) NOT NULL,
  `imageable_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255),
  `remember_token` VARCHAR(100),
  `email_verified_at` TIMESTAMP NULL,
  `email_verification_code` VARCHAR(255),
  `email_verification_code_created_at` TIMESTAMP NULL,
  `password_reset_code` VARCHAR(255),
  `auth0_user_id` VARCHAR(255),
  `role` VARCHAR(255) NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

