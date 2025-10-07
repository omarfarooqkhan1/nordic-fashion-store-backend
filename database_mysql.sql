-- MySQL dump generated from SQLite database
-- Generated on: 2025-09-17 18:01:28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `migrations` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `migration` VARCHAR(255) NOT NULL, `batch` INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2024_01_01_000000_create_contact_forms_table', '1'),
('5', '2025_06_17_041930_create_categories_table', '1'),
('6', '2025_06_17_041940_create_products_table', '1'),
('7', '2025_06_17_042107_create_product_variants_table', '1'),
('8', '2025_06_17_042744_create_images_table', '1'),
('9', '2025_06_26_034346_create_carts_table', '1'),
('10', '2025_06_26_034347_create_cart_items_table', '1'),
('11', '2025_06_27_103303_create_addresses_table', '1'),
('12', '2025_06_28_101010_create_orders_table', '1'),
('13', '2025_06_28_101020_create_order_items_table', '1'),
('14', '2025_08_07_062303_add_tracking_number_to_orders_table', '1'),
('15', '2025_08_15_000001_add_shipping_service_to_orders_table', '1'),
('16', '2025_08_15_000002_create_custom_jacket_cart_items_table', '1'),
('17', '2025_08_17_050432_make_address_type_and_label_nullable', '1'),
('18', '2025_08_17_062438_create_product_reviews_table', '1'),
('19', '2025_08_18_000001_add_media_to_product_reviews_table', '1'),
('20', '2025_08_18_000001_add_status_to_product_reviews_table', '1'),
('21', '2025_09_02_163620_create_blogs_table', '1'),
('22', '2025_09_03_175714_create_personal_access_tokens_table', '1'),
('23', '2025_09_04_165233_create_blog_likes_table', '1'),
('24', '2025_09_04_165245_create_blog_views_table', '1');

CREATE TABLE `users` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `password` varchar, `remember_token` varchar, `email_verified_at` datetime, `email_verification_code` varchar, `email_verification_code_created_at` datetime, `password_reset_code` varchar, `auth0_user_id` varchar, `role` VARCHAR(255) NOT NULL default 'customer', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `users`
INSERT INTO `users` (`id`, `name`, `email`, `password`, `remember_token`, `email_verified_at`, `email_verification_code`, `email_verification_code_created_at`, `password_reset_code`, `auth0_user_id`, `role`, `created_at`, `updated_at`) VALUES
('1', 'Super Admin', 'admin@example.com', '$2y$12$Mc5P737lgUGAaMp//nwH8uZPs7/Z1tSSVNWdcaXJGWohaIRA.q8.G', NULL, '2025-09-16 21:21:48', NULL, NULL, NULL, NULL, 'admin', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('2', 'Password Customer', 'customer@example.com', '$2y$12$SQdKUK5S/MJZJ4b9Nz.YoOSkPqHf5wM9fLux6kemEyCU/PwyQCwr6', NULL, '2025-09-16 21:21:48', NULL, NULL, NULL, NULL, 'customer', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('3', 'Auth0 Customer', 'auth0customer@example.com', NULL, NULL, '2025-09-16 21:21:48', NULL, NULL, NULL, 'auth0|sample123456', 'customer', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('4', 'Muhammad Omar Farooq Khan', 'omarfarooqkhan@outlook.com', '$2y$12$EDfhEnmEdTkT6Un/zoAYheXEDSXwqDxBivKc5p9AtlxJ7gKVomLbi', NULL, '2025-09-17 17:44:23', NULL, '2025-09-17 17:43:43', NULL, NULL, 'customer', '2025-09-17 17:43:43', '2025-09-17 17:44:23');

CREATE TABLE `password_reset_tokens` (`email` VARCHAR(255) NOT NULL, `token` VARCHAR(255) NOT NULL, `created_at` datetime, primary key (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (`id` VARCHAR(255) NOT NULL, `user_id` INT, `ip_address` varchar, `user_agent` VARCHAR(255), `payload` VARCHAR(255) not null, `last_activity` INT NOT NULL, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('UL5ISYBXdOibdBNwaD5cEgBbhiXggdVW1aHb3Qls', NULL, '39.51.33.144', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMll3c216RmRLcEVvcjFNektZSlo4RmluTG1WcXpHek5Wb3N2ZFhxTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Nzg6Imh0dHBzOi8vYmFja2VuZC5ub3JkZmxleC5zaG9wL2FwaS9hcGkvYmxvZ3MvbGVhdGhlci1qYWNrZXQtY2FyZS1jb21wbGV0ZS1ndWlkZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1758110986'),
('ffeVPZ3Me4MBmuhKZt0WM20KQmBf9hzEWX3iFuZ8', NULL, '2a02:4780:6:c0de::8', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQlBKdFpGVmw0VWE3bmpvbXlmSW1IWEZiV2hUaUpOc3hlWDM1SVF6YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYmFja2VuZC5ub3JkZmxleC5zaG9wIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1758113926');

CREATE TABLE `cache` (`key` VARCHAR(255) NOT NULL, `value` VARCHAR(255) not null, `expiration` INT NOT NULL, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (`key` VARCHAR(255) NOT NULL, `owner` VARCHAR(255) NOT NULL, `expiration` INT NOT NULL, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `queue` VARCHAR(255) NOT NULL, `payload` VARCHAR(255) not null, `attempts` INT NOT NULL, `reserved_at` INT, `available_at` INT NOT NULL, `created_at` INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (`id` VARCHAR(255) NOT NULL, `name` VARCHAR(255) NOT NULL, `total_jobs` INT NOT NULL, `pending_jobs` INT NOT NULL, `failed_jobs` INT NOT NULL, `failed_job_ids` VARCHAR(255) not null, `options` VARCHAR(255), `cancelled_at` INT, `created_at` INT NOT NULL, `finished_at` INT, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `uuid` VARCHAR(255) NOT NULL, `connection` VARCHAR(255) not null, `queue` VARCHAR(255) not null, `payload` VARCHAR(255) not null, `exception` VARCHAR(255) not null, `failed_at` datetime not null default CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_forms` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `first_name` VARCHAR(255) NOT NULL, `last_name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `subject` VARCHAR(255) NOT NULL, `message` VARCHAR(255) not null, `status` varchar check (`status` in ('new', 'read', 'replied', 'closed')) not null default 'new', `admin_notes` VARCHAR(255), `ip_address` varchar, `user_agent` varchar, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `name` VARCHAR(255) NOT NULL, `slug` varchar, `description` VARCHAR(255), `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `categories`
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('1', 'Clothing', NULL, NULL, '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('2', 'Footwear', NULL, NULL, '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('3', 'Jackets', NULL, NULL, '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('4', 'Accessories', NULL, NULL, '2025-09-16 21:21:48', '2025-09-16 21:21:48');

CREATE TABLE `products` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `category_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `description` VARCHAR(255), `gender` varchar check (`gender` in ('male', 'female', 'unisex')) not null default 'unisex', `price` numeric not null, `created_at` datetime, `updated_at` datetime, foreign key(`category_id`) references `categories`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `products`
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `gender`, `price`, `created_at`, `updated_at`) VALUES
('12', '3', 'Women\'s Classic Black Leather Jacket', 'A sophisticated black leather jacket designed specifically for women. Features a tailored fit with feminine details and premium black leather construction. Perfect for the modern woman who values both style and quality.', 'female', '279.99', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('13', '3', 'Women\'s Modern Black Leather Jacket', 'A contemporary black leather jacket with sleek design elements tailored for women. Features modern cuts and premium leather quality. Ideal for the fashion-forward woman seeking a statement piece.', 'female', '299.99', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('14', '3', 'Women\'s Elegant Black Leather Jacket', 'An elegant black leather jacket with refined styling for women. Features sophisticated design elements and premium leather construction. Perfect for the woman who appreciates timeless elegance and quality craftsmanship.', 'female', '319.99', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('15', '3', 'Women\'s Premium Black Leather Jacket', 'A premium black leather jacket with luxurious details designed for women. Features exceptional craftsmanship and the finest leather quality. The ultimate statement piece for the discerning woman who demands the best.', 'female', '339.99', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('27', '3', 'Men\'s Classic Leather Jacket', 'Classic leather jacket for men. Perfect for winters.', 'male', '150', '2025-09-17 17:32:05', '2025-09-17 17:32:05');

CREATE TABLE `product_variants` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `product_id` INT NOT NULL, `sku` VARCHAR(255) NOT NULL, `color` varchar, `size` varchar, `price_difference` numeric not null default '0', `stock` INT NOT NULL default '0', `created_at` datetime, `updated_at` datetime, foreign key(`product_id`) references `products`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `product_variants`
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `color`, `size`, `price_difference`, `stock`, `created_at`, `updated_at`) VALUES
('37', '12', 'WCBLJ-BLK-XS-001', 'Black', 'XS', '0', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('38', '12', 'WCBLJ-BLK-S-002', 'Black', 'S', '0', '25', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('39', '12', 'WCBLJ-BLK-M-003', 'Black', 'M', '0', '30', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('40', '12', 'WCBLJ-BLK-L-004', 'Black', 'L', '0', '20', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('41', '13', 'WMBLJ-BLK-XS-001', 'Black', 'XS', '0', '12', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('42', '13', 'WMBLJ-BLK-S-002', 'Black', 'S', '0', '22', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('43', '13', 'WMBLJ-BLK-M-003', 'Black', 'M', '0', '28', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('44', '13', 'WMBLJ-BLK-L-004', 'Black', 'L', '0', '18', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('45', '14', 'WEBLJ-BLK-XS-001', 'Black', 'XS', '0', '10', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('46', '14', 'WEBLJ-BLK-S-002', 'Black', 'S', '0', '20', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('47', '14', 'WEBLJ-BLK-M-003', 'Black', 'M', '0', '25', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('48', '14', 'WEBLJ-BLK-L-004', 'Black', 'L', '0', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('49', '15', 'WPBLJ-BLK-XS-001', 'Black', 'XS', '0', '8', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('50', '15', 'WPBLJ-BLK-S-002', 'Black', 'S', '0', '18', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('51', '15', 'WPBLJ-BLK-M-003', 'Black', 'M', '0', '22', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('52', '15', 'WPBLJ-BLK-L-004', 'Black', 'L', '0', '12', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('61', '27', 'men\'s-classic-leather-jacket-l-black', 'Black', 'L', '0', '30', '2025-09-17 17:32:07', '2025-09-17 17:32:07');

CREATE TABLE `images` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `url` VARCHAR(255) NOT NULL, `alt_VARCHAR(255)` varchar, `sort_order` INT NOT NULL default '0', `imageable_type` VARCHAR(255) NOT NULL, `imageable_id` INT NOT NULL, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `images`
INSERT INTO `images` (`id`, `url`, `alt_text`, `sort_order`, `imageable_type`, `imageable_id`, `created_at`, `updated_at`) VALUES
('1', '/storage/images/leather-jacket-black-1-front.jpg', 'Classic Black Leather Jacket Front View', '0', 'App\\Models\\Product', '1', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('2', '/storage/images/leather-jacket-black-1-back.jpg', 'Classic Black Leather Jacket Back View', '1', 'App\\Models\\Product', '1', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('3', '/storage/images/leather-jacket-black-1-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '1', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('4', '/storage/images/leather-jacket-black-1-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '2', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('5', '/storage/images/leather-jacket-black-1-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '3', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('6', '/storage/images/leather-jacket-black-1-front.jpg', 'Black XL Jacket Front', '0', 'App\\Models\\ProductVariant', '4', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('7', '/storage/images/leather-jacket-black-2-front.jpg', 'Modern Black Leather Jacket Front View', '0', 'App\\Models\\Product', '2', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('8', '/storage/images/leather-jacket-black-2-back.jpg', 'Modern Black Leather Jacket Back View', '1', 'App\\Models\\Product', '2', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('9', '/storage/images/leather-jacket-black-2-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '5', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('10', '/storage/images/leather-jacket-black-2-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '6', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('11', '/storage/images/leather-jacket-black-2-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '7', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('12', '/storage/images/leather-jacket-black-3-front.jpg', 'Vintage Black Leather Jacket Front View', '0', 'App\\Models\\Product', '3', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('13', '/storage/images/leather-jacket-black-3-back.jpg', 'Vintage Black Leather Jacket Back View', '1', 'App\\Models\\Product', '3', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('14', '/storage/images/leather-jacket-black-3-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '8', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('15', '/storage/images/leather-jacket-black-3-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '9', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('16', '/storage/images/leather-jacket-black-3-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '10', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('17', '/storage/images/leather-jacket-black-3-front.jpg', 'Black XL Jacket Front', '0', 'App\\Models\\ProductVariant', '11', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('18', '/storage/images/leather-jacket-brown-1-front.jpg', 'Premium Brown Leather Jacket Front View', '0', 'App\\Models\\Product', '4', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('19', '/storage/images/leather-jacket-brown-1-back.jpg', 'Premium Brown Leather Jacket Back View', '1', 'App\\Models\\Product', '4', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('20', '/storage/images/leather-jacket-brown-1-front.jpg', 'Brown S Jacket Front', '0', 'App\\Models\\ProductVariant', '12', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('21', '/storage/images/leather-jacket-brown-1-front.jpg', 'Brown M Jacket Front', '0', 'App\\Models\\ProductVariant', '13', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('22', '/storage/images/leather-jacket-brown-1-front.jpg', 'Brown L Jacket Front', '0', 'App\\Models\\ProductVariant', '14', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('23', '/storage/images/leather-jacket-brown-2-front.jpg', 'Classic Brown Leather Jacket Front View', '0', 'App\\Models\\Product', '5', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('24', '/storage/images/leather-jacket-brown-2-back.jpg', 'Classic Brown Leather Jacket Back View', '1', 'App\\Models\\Product', '5', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('25', '/storage/images/leather-jacket-brown-2-front.jpg', 'Brown S Jacket Front', '0', 'App\\Models\\ProductVariant', '15', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('26', '/storage/images/leather-jacket-brown-2-front.jpg', 'Brown M Jacket Front', '0', 'App\\Models\\ProductVariant', '16', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('27', '/storage/images/leather-jacket-brown-2-front.jpg', 'Brown L Jacket Front', '0', 'App\\Models\\ProductVariant', '17', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('28', '/storage/images/leather-jacket-brown-2-front.jpg', 'Brown XL Jacket Front', '0', 'App\\Models\\ProductVariant', '18', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('29', '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'Dark Brown Leather Jacket Front View', '0', 'App\\Models\\Product', '6', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('30', '/storage/images/leather-jacket-dark-brown-1-back.jpg', 'Dark Brown Leather Jacket Back View', '1', 'App\\Models\\Product', '6', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('31', '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'Dark Brown S Jacket Front', '0', 'App\\Models\\ProductVariant', '19', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('32', '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'Dark Brown M Jacket Front', '0', 'App\\Models\\ProductVariant', '20', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('33', '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'Dark Brown L Jacket Front', '0', 'App\\Models\\ProductVariant', '21', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('34', '/storage/images/leather-jacket-burgundy-2-front.jpg', 'Burgundy Leather Jacket Front View', '0', 'App\\Models\\Product', '7', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('35', '/storage/images/leather-jacket-burgundy-2-back.jpg', 'Burgundy Leather Jacket Back View', '1', 'App\\Models\\Product', '7', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('36', '/storage/images/leather-jacket-burgundy-2-front.jpg', 'Burgundy S Jacket Front', '0', 'App\\Models\\ProductVariant', '22', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('37', '/storage/images/leather-jacket-burgundy-2-front.jpg', 'Burgundy M Jacket Front', '0', 'App\\Models\\ProductVariant', '23', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('38', '/storage/images/leather-jacket-burgundy-2-front.jpg', 'Burgundy L Jacket Front', '0', 'App\\Models\\ProductVariant', '24', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('39', '/storage/images/leather-jacket-burugndy-1-front.jpg', 'Premium Burgundy Leather Jacket Front View', '0', 'App\\Models\\Product', '8', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('40', '/storage/images/leather-jacket-burugndy-1-back.jpg', 'Premium Burgundy Leather Jacket Back View', '1', 'App\\Models\\Product', '8', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('41', '/storage/images/leather-jacket-burugndy-1-front.jpg', 'Burgundy S Jacket Front', '0', 'App\\Models\\ProductVariant', '25', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('42', '/storage/images/leather-jacket-burugndy-1-front.jpg', 'Burgundy M Jacket Front', '0', 'App\\Models\\ProductVariant', '26', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('43', '/storage/images/leather-jacket-burugndy-1-front.jpg', 'Burgundy L Jacket Front', '0', 'App\\Models\\ProductVariant', '27', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('44', '/storage/images/leather-jacket-blue-1-front.jpg', 'Navy Blue Leather Jacket Front View', '0', 'App\\Models\\Product', '9', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('45', '/storage/images/leather-jacket-blue-1-back.jpg', 'Navy Blue Leather Jacket Back View', '1', 'App\\Models\\Product', '9', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('46', '/storage/images/leather-jacket-blue-1-front.jpg', 'Navy Blue S Jacket Front', '0', 'App\\Models\\ProductVariant', '28', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('47', '/storage/images/leather-jacket-blue-1-front.jpg', 'Navy Blue M Jacket Front', '0', 'App\\Models\\ProductVariant', '29', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('48', '/storage/images/leather-jacket-blue-1-front.jpg', 'Navy Blue L Jacket Front', '0', 'App\\Models\\ProductVariant', '30', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('49', '/storage/images/leather-jacket-olive-1-front.jpg', 'Olive Green Leather Jacket Front View', '0', 'App\\Models\\Product', '10', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('50', '/storage/images/leather-jacket-olive-1-back.jpg', 'Olive Green Leather Jacket Back View', '1', 'App\\Models\\Product', '10', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('51', '/storage/images/leather-jacket-olive-1-front.jpg', 'Olive Green S Jacket Front', '0', 'App\\Models\\ProductVariant', '31', '2025-09-16 21:21:48', '2025-09-16 21:21:48'),
('52', '/storage/images/leather-jacket-olive-1-front.jpg', 'Olive Green M Jacket Front', '0', 'App\\Models\\ProductVariant', '32', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('53', '/storage/images/leather-jacket-olive-1-front.jpg', 'Olive Green L Jacket Front', '0', 'App\\Models\\ProductVariant', '33', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('54', '/storage/images/leather-jacket-white-1-front.jpg', 'White Leather Jacket Front View', '0', 'App\\Models\\Product', '11', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('55', '/storage/images/leather-jacket-white-1-back.jpg', 'White Leather Jacket Back View', '1', 'App\\Models\\Product', '11', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('56', '/storage/images/leather-jacket-white-1-front.jpg', 'White S Jacket Front', '0', 'App\\Models\\ProductVariant', '34', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('57', '/storage/images/leather-jacket-white-1-front.jpg', 'White M Jacket Front', '0', 'App\\Models\\ProductVariant', '35', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('58', '/storage/images/leather-jacket-white-1-front.jpg', 'White L Jacket Front', '0', 'App\\Models\\ProductVariant', '36', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('59', '/storage/images/leather-jacket-woman-black-1-1.jpeg', 'Women\'s Classic Black Leather Jacket View 1', '0', 'App\\Models\\Product', '12', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('60', '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'Women\'s Classic Black Leather Jacket View 2', '1', 'App\\Models\\Product', '12', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('61', '/storage/images/leather-jacket-woman-black-1-3.jpeg', 'Women\'s Classic Black Leather Jacket View 3', '2', 'App\\Models\\Product', '12', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('62', '/storage/images/leather-jacket-woman-black-1-front.jpg', 'Black XS Jacket Front', '0', 'App\\Models\\ProductVariant', '37', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('63', '/storage/images/leather-jacket-woman-black-1-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '38', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('64', '/storage/images/leather-jacket-woman-black-1-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '39', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('65', '/storage/images/leather-jacket-woman-black-1-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '40', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('66', '/storage/images/leather-jacket-woman-black-2-1.jpeg', 'Women\'s Modern Black Leather Jacket View 1', '0', 'App\\Models\\Product', '13', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('67', '/storage/images/leather-jacket-woman-black-2-2.jpeg', 'Women\'s Modern Black Leather Jacket View 2', '1', 'App\\Models\\Product', '13', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('68', '/storage/images/leather-jacket-woman-black-2-3.jpeg', 'Women\'s Modern Black Leather Jacket View 3', '2', 'App\\Models\\Product', '13', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('69', '/storage/images/leather-jacket-woman-black-2-front.jpg', 'Black XS Jacket Front', '0', 'App\\Models\\ProductVariant', '41', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('70', '/storage/images/leather-jacket-woman-black-2-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '42', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('71', '/storage/images/leather-jacket-woman-black-2-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '43', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('72', '/storage/images/leather-jacket-woman-black-2-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '44', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('73', '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'Women\'s Elegant Black Leather Jacket View 1', '0', 'App\\Models\\Product', '14', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('74', '/storage/images/leather-jacket-woman-black-3-2.jpeg', 'Women\'s Elegant Black Leather Jacket View 2', '1', 'App\\Models\\Product', '14', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('75', '/storage/images/leather-jacket-woman-black-3-3.jpeg', 'Women\'s Elegant Black Leather Jacket View 3', '2', 'App\\Models\\Product', '14', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('76', '/storage/images/leather-jacket-woman-black-3-front.jpg', 'Black XS Jacket Front', '0', 'App\\Models\\ProductVariant', '45', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('77', '/storage/images/leather-jacket-woman-black-3-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '46', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('78', '/storage/images/leather-jacket-woman-black-3-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '47', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('79', '/storage/images/leather-jacket-woman-black-3-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '48', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('80', '/storage/images/leather-jacket-woman-black-4-1.jpeg', 'Women\'s Premium Black Leather Jacket View 1', '0', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('81', '/storage/images/leather-jacket-woman-black-4-2.jpeg', 'Women\'s Premium Black Leather Jacket View 2', '1', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('82', '/storage/images/leather-jacket-woman-black-4-3.jpeg', 'Women\'s Premium Black Leather Jacket View 3', '2', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('83', '/storage/images/leather-jacket-woman-black-4-4jpeg.jpeg', 'Women\'s Premium Black Leather Jacket View 4', '3', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('84', '/storage/images/leather-jacket-woman-black-4-5.jpeg', 'Women\'s Premium Black Leather Jacket View 5', '4', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('85', '/storage/images/leather-jacket-woman-black-4-6.jpeg', 'Women\'s Premium Black Leather Jacket View 6', '5', 'App\\Models\\Product', '15', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('86', '/storage/images/leather-jacket-woman-black-4-front.jpg', 'Black XS Jacket Front', '0', 'App\\Models\\ProductVariant', '49', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('87', '/storage/images/leather-jacket-woman-black-4-front.jpg', 'Black S Jacket Front', '0', 'App\\Models\\ProductVariant', '50', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('88', '/storage/images/leather-jacket-woman-black-4-front.jpg', 'Black M Jacket Front', '0', 'App\\Models\\ProductVariant', '51', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('89', '/storage/images/leather-jacket-woman-black-4-front.jpg', 'Black L Jacket Front', '0', 'App\\Models\\ProductVariant', '52', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('90', 'https://nordflex.shop/storage/images/products/2025/09/classic_black_leather_jacket_1758128548_68cae9a44797d', '20250912_0057_Model in Leather Jacket_remix_01k4x5h8d8efpvrccwjgxydzh2.png', '1', 'App\\Models\\Product', '20', '2025-09-17 17:02:28', '2025-09-17 17:02:28'),
('91', 'https://nordflex.shop/storage/images/products/2025/09/men_s_classic_leather_jacket_1758129896_68caeee8cae15', 'Product image', '1', 'App\\Models\\Product', '26', '2025-09-17 17:24:56', '2025-09-17 17:24:56'),
('92', 'https://nordflex.shop/storage/images/men_s_classic_leather_jacket_1758130360_68caf0b80ddbe', 'Product image', '1', 'App\\Models\\Product', '27', '2025-09-17 17:32:40', '2025-09-17 17:32:40');

CREATE TABLE `carts` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `user_id` INT, `session_id` varchar, `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `carts`
INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
('1', NULL, 'e5593e28-f2ca-4132-ae76-e0773892b922', '2025-09-16 21:51:22', '2025-09-16 21:51:22'),
('2', NULL, 'a692c832-7939-41f9-be3f-784c6cc0f9de', '2025-09-16 22:02:25', '2025-09-16 22:02:25'),
('3', NULL, '2880800e-769e-44de-bd9b-389182936ebe', '2025-09-16 22:08:55', '2025-09-16 22:08:55'),
('4', NULL, 'aa311c45-7856-4031-89e5-de99344ee8fb', '2025-09-17 06:00:49', '2025-09-17 06:00:49'),
('5', NULL, '2718127e-a265-46ce-a708-e0d31689aad3', '2025-09-17 08:14:55', '2025-09-17 08:14:55'),
('6', NULL, '3ca208a0-ad2a-4a67-9152-1e7f03dd91b7', '2025-09-17 11:59:52', '2025-09-17 11:59:52'),
('7', NULL, 'e24aef2e-6cf2-470a-b626-f9d516a02ff6', '2025-09-17 13:54:16', '2025-09-17 13:54:16'),
('8', NULL, '5415f7db-7a31-4627-90e1-7de0dd3ffd9f', '2025-09-17 16:01:10', '2025-09-17 16:01:10'),
('9', '1', NULL, '2025-09-17 16:36:35', '2025-09-17 16:36:35'),
('10', NULL, 'd30325d2-1b77-45d8-a921-d1d58ac168e7', '2025-09-17 16:44:39', '2025-09-17 16:44:39'),
('11', '4', NULL, '2025-09-17 17:44:51', '2025-09-17 17:44:51');

CREATE TABLE `cart_items` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `cart_id` INT NOT NULL, `product_variant_id` INT NOT NULL, `quantity` INT NOT NULL default '1', `created_at` datetime, `updated_at` datetime, foreign key(`cart_id`) references `carts`(`id`) on delete cascade, foreign key(`product_variant_id`) references `product_variants`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `user_id` INT, `session_id` varchar, `order_number` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL default 'pending', `subtotal` numeric not null, `tax` numeric not null, `shipping` numeric not null, `total` numeric not null, `notes` VARCHAR(255), `shipping_name` VARCHAR(255) NOT NULL, `shipping_email` VARCHAR(255) NOT NULL, `shipping_phone` varchar, `shipping_address` VARCHAR(255) NOT NULL, `shipping_city` VARCHAR(255) NOT NULL, `shipping_state` VARCHAR(255) NOT NULL, `shipping_postal_code` VARCHAR(255) NOT NULL, `shipping_country` VARCHAR(255) NOT NULL, `billing_same_as_shipping` tinyint(1) not null default '1', `billing_name` varchar, `billing_email` varchar, `billing_phone` varchar, `billing_address` varchar, `billing_city` varchar, `billing_state` varchar, `billing_postal_code` varchar, `billing_country` varchar, `payment_method` varchar, `payment_status` VARCHAR(255) NOT NULL default 'pending', `payment_transaction_id` varchar, `created_at` datetime, `updated_at` datetime, `tracking_number` varchar, `shipping_service` varchar, foreign key(`user_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `order_id` INT NOT NULL, `product_variant_id` INT, `product_name` VARCHAR(255) NOT NULL, `variant_name` varchar, `price` numeric not null, `quantity` INT NOT NULL, `subtotal` numeric not null, `product_snapshot` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`order_id`) references `orders`(`id`) on delete cascade, foreign key(`product_variant_id`) references `product_variants`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `custom_jacket_cart_items` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `item_id` VARCHAR(255) NOT NULL, `session_id` varchar, `user_id` INT, `name` VARCHAR(255) NOT NULL, `color` VARCHAR(255) NOT NULL, `size` VARCHAR(255) NOT NULL, `quantity` INT NOT NULL, `price` numeric not null, `front_image_url` VARCHAR(255) not null, `back_image_url` VARCHAR(255) not null, `logos` VARCHAR(255), `custom_description` VARCHAR(255), `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addresses` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `user_id` INT NOT NULL, `type` varchar check (`type` in ('home', 'work', 'other')), `label` varchar, `street` VARCHAR(255) NOT NULL, `city` VARCHAR(255) NOT NULL, `state` VARCHAR(255) NOT NULL, `postal_code` VARCHAR(255) NOT NULL, `country` VARCHAR(255) NOT NULL, `is_default` tinyint(1) not null default ('0'), `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references users(`id`) on delete cascade on update no action) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_reviews` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `user_id` INT NOT NULL, `product_id` INT NOT NULL, `rating` INT NOT NULL, `review_VARCHAR(255)` VARCHAR(255), `title` varchar, `is_verified_purchase` tinyint(1) not null default '0', `created_at` datetime, `updated_at` datetime, `media` VARCHAR(255), `status` VARCHAR(255) NOT NULL default 'pending', foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`product_id`) references `products`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blogs` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `title` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL, `excerpt` VARCHAR(255), `content` VARCHAR(255) not null, `featured_image` varchar, `images` VARCHAR(255), `status` varchar check (`status` in ('draft', 'published', 'archived')) not null default 'draft', `author_name` VARCHAR(255) NOT NULL default 'Admin', `meta_title` varchar, `meta_description` VARCHAR(255), `tags` VARCHAR(255), `views_count` INT NOT NULL default '0', `likes_count` INT NOT NULL default '0', `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `blogs`
INSERT INTO `blogs` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `images`, `status`, `author_name`, `meta_title`, `meta_description`, `tags`, `views_count`, `likes_count`, `created_at`, `updated_at`) VALUES
('1', 'Why Nordflex Leather Jackets are the Ultimate Choice for Finland\'s Harsh Winters', 'nordflex-leather-jackets-finland-winters', 'Discover why Nordflex leather jackets are the perfect choice for surviving Finland\'s extreme winter conditions while maintaining style and comfort.', '<div class=\"blog-content\">
                    <p class=\"lead\">Discover why Nordflex leather jackets are the perfect choice for surviving Finland\'s extreme winter conditions while maintaining style and comfort.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Understanding Finland\'s Winter Challenges</h2>
                        <p>Finland\'s winters are notoriously harsh, with temperatures often dropping below -30°C (-22°F) and heavy snowfall. The combination of extreme cold, wind, and moisture requires clothing that can provide both protection and comfort.</p>
                        
                        <h3>Key Winter Factors:</h3>
                        <ul>
                            <li>Extreme cold temperatures</li>
                            <li>Strong winds and wind chill</li>
                            <li>Heavy snowfall and ice</li>
                            <li>Limited daylight hours</li>
                        </ul>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Why Leather Jackets Excel in Cold Weather</h2>
                        <p>Leather jackets, particularly those made from high-quality materials like those used in Nordflex products, offer several advantages in cold weather conditions:</p>
                        
                        <h3>Natural Insulation Properties</h3>
                        <p>Leather naturally provides excellent insulation, helping to retain body heat while allowing the skin to breathe. This makes it ideal for layering and adapting to changing temperatures throughout the day.</p>
                        
                        <h3>Wind Resistance</h3>
                        <p>Quality leather acts as an effective wind barrier, preventing cold air from penetrating through to your body. This is crucial in Finland\'s windy winter conditions.</p>
                        
                        <h3>Durability in Harsh Conditions</h3>
                        <p>Unlike synthetic materials that can become brittle in extreme cold, leather maintains its flexibility and strength even in the harshest winter conditions.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Nordflex Leather Jacket Features for Winter</h2>
                        <p>Nordflex leather jackets are specifically designed with winter conditions in mind, incorporating several features that make them ideal for Finnish winters:</p>
                        
                        <h3>Premium Leather Quality</h3>
                        <p>Our jackets are crafted from the finest leather, ensuring maximum durability and protection against the elements.</p>
                        
                        <h3>Reinforced Construction</h3>
                        <p>Double-stitched seams and reinforced stress points ensure the jacket can withstand the rigors of daily winter use.</p>
                        
                        <h3>Versatile Styling</h3>
                        <p>Our designs work equally well for casual outings, work, or special occasions, making them a versatile addition to any winter wardrobe.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>In conclusion,</strong> Nordflex leather jackets offer the perfect combination of style, durability, and functionality for Finland\'s challenging winter conditions. Their natural insulation properties, wind resistance, and premium construction make them an excellent choice for anyone looking to stay warm and stylish during the long Finnish winter months.</p>
                    </div>
                </div>', '/storage/images/blogs/1.jpeg', NULL, 'published', 'Nordflex Team', 'Nordflex Leather Jackets for Finland Winters - Ultimate Winter Protection', 'Discover why Nordflex leather jackets are the perfect choice for surviving Finland\'s extreme winter conditions. Premium quality, natural insulation, and wind resistance.', '[\"leather jackets\",\"winter fashion\",\"Finland\",\"cold weather\",\"premium quality\"]', '3', '0', '2025-09-16 21:21:49', '2025-09-17 12:10:43'),
('2', 'The Art of Leather Jacket Care: A Complete Guide', 'leather-jacket-care-complete-guide', 'Learn the essential techniques for maintaining your leather jacket\'s beauty and longevity with our comprehensive care guide.', '<div class=\"blog-content\">
                    <p class=\"lead\">Learn the essential techniques for maintaining your leather jacket\'s beauty and longevity with our comprehensive care guide.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Understanding Leather Types</h2>
                        <p>Different types of leather require different care approaches. Understanding your jacket\'s leather type is the first step in proper maintenance.</p>
                        
                        <h3>Common Leather Types:</h3>
                        <ul>
                            <li><strong>Full-grain leather:</strong> Highest quality, requires minimal conditioning</li>
                            <li><strong>Top-grain leather:</strong> Good quality, needs regular conditioning</li>
                            <li><strong>Genuine leather:</strong> Requires more frequent care</li>
                            <li><strong>Suede:</strong> Needs special suede-specific products</li>
                        </ul>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Daily Care Routine</h2>
                        <p>Proper daily care can significantly extend your leather jacket\'s life and maintain its appearance.</p>
                        
                        <h3>Storage Tips</h3>
                        <p>Always hang your leather jacket on a wide, padded hanger to maintain its shape. Avoid plastic bags or covers that can trap moisture.</p>
                        
                        <h3>Cleaning Basics</h3>
                        <p>For regular cleaning, use a soft, dry cloth to remove surface dirt and dust. Avoid water unless absolutely necessary, as it can damage the leather.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Conditioning and Protection</h2>
                        <p>Regular conditioning keeps leather supple and prevents cracking, especially important in dry climates or during winter months.</p>
                        
                        <h3>How Often to Condition</h3>
                        <p>Condition your leather jacket every 3-6 months, or more frequently if you notice the leather becoming dry or stiff.</p>
                        
                        <h3>Choosing the Right Conditioner</h3>
                        <p>Use a high-quality leather conditioner specifically designed for your jacket\'s leather type. Test on a small, inconspicuous area first.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Remember:</strong> Proper care of your leather jacket is an investment in its longevity. With the right techniques and products, your Nordflex leather jacket can last for decades while maintaining its beauty and functionality.</p>
                    </div>
                </div>', '/storage/images/blogs/2.jpeg', NULL, 'published', 'Nordflex Team', 'Leather Jacket Care Guide - Complete Maintenance Tips', 'Complete guide to leather jacket care. Learn proper cleaning, conditioning, and storage techniques to maintain your jacket\'s beauty and longevity.', '[\"leather care\",\"maintenance\",\"jacket care\",\"leather conditioning\",\"storage tips\"]', '1', '0', '2025-09-16 21:21:49', '2025-09-17 12:09:48'),
('3', 'Sustainable Fashion: The Environmental Benefits of Quality Leather', 'sustainable-fashion-leather-environmental-benefits', 'Explore how choosing quality leather products contributes to sustainable fashion and environmental responsibility.', '<div class=\"blog-content\">
                    <p class=\"lead\">Explore how choosing quality leather products contributes to sustainable fashion and environmental responsibility.</p>
                    
                    <section class=\"blog-section\">
                        <h2>The Longevity Factor</h2>
                        <p>Quality leather products, when properly cared for, can last for decades, significantly reducing the need for frequent replacements and the associated environmental impact.</p>
                        
                        <h3>Durability vs. Fast Fashion</h3>
                        <p>Unlike fast fashion items that often need replacement within a year, a well-made leather jacket can serve you for 20+ years with proper care.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Natural Material Benefits</h2>
                        <p>Leather is a natural, biodegradable material that doesn\'t contribute to microplastic pollution like synthetic alternatives.</p>
                        
                        <h3>Biodegradability</h3>
                        <p>When properly treated, leather will eventually biodegrade, returning to the earth without leaving harmful synthetic residues.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Conclusion:</strong> Choosing quality leather products is a step toward more sustainable fashion choices that benefit both you and the environment.</p>
                    </div>
                </div>', '/storage/images/blogs/3.jpeg', NULL, 'published', 'Nordflex Team', 'Sustainable Fashion with Quality Leather - Environmental Benefits', 'Discover how quality leather products contribute to sustainable fashion. Learn about durability, biodegradability, and environmental benefits.', '[\"sustainable fashion\",\"environment\",\"leather benefits\",\"eco-friendly\",\"durability\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('4', 'Leather Jacket Styling Tips for Every Season', 'leather-jacket-styling-tips-every-season', 'Master the art of styling leather jackets throughout the year with our seasonal fashion guide.', '<div class=\"blog-content\">
                    <p class=\"lead\">Master the art of styling leather jackets throughout the year with our seasonal fashion guide.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Spring Styling</h2>
                        <p>Spring is the perfect time to transition your leather jacket from winter layering to lighter, more breathable combinations.</p>
                        
                        <h3>Spring Outfit Ideas</h3>
                        <ul>
                            <li>Layer over lightweight sweaters</li>
                            <li>Pair with denim and sneakers</li>
                            <li>Add colorful scarves for pops of color</li>
                        </ul>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Summer Styling</h2>
                        <p>While leather might seem too warm for summer, there are ways to incorporate it into your warm-weather wardrobe.</p>
                        
                        <h3>Summer Considerations</h3>
                        <p>Choose lighter leather weights and pair with breathable fabrics underneath. Consider cropped styles for better ventilation.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Fall Styling</h2>
                        <p>Fall is leather jacket season at its finest, with perfect weather for layering and showcasing your style.</p>
                        
                        <h3>Fall Favorites</h3>
                        <p>Layer over flannel shirts, pair with boots, and add accessories like hats and scarves for a complete autumn look.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Winter Styling</h2>
                        <p>Winter styling focuses on warmth and functionality while maintaining the leather jacket\'s aesthetic appeal.</p>
                        
                        <h3>Winter Layering</h3>
                        <p>Layer over thick sweaters, add thermal undergarments, and pair with warm accessories for maximum comfort and style.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Pro Tip:</strong> The key to year-round leather jacket styling is understanding how to layer and accessorize appropriately for each season\'s unique challenges and opportunities.</p>
                    </div>
                </div>', '/storage/images/blogs/4.jpeg', NULL, 'published', 'Nordflex Team', 'Leather Jacket Styling Tips - Seasonal Fashion Guide', 'Master leather jacket styling for every season. Spring, summer, fall, and winter outfit ideas and layering techniques.', '[\"styling tips\",\"seasonal fashion\",\"leather jackets\",\"outfit ideas\",\"layering\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('5', 'The History and Evolution of Leather Jackets', 'history-evolution-leather-jackets', 'Take a journey through the fascinating history of leather jackets, from their military origins to modern fashion statements.', '<div class=\"blog-content\">
                    <p class=\"lead\">Take a journey through the fascinating history of leather jackets, from their military origins to modern fashion statements.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Military Origins</h2>
                        <p>Leather jackets first gained popularity during World War I, when military pilots needed protective outerwear for open-cockpit aircraft.</p>
                        
                        <h3>Early Military Use</h3>
                        <p>The first leather flight jackets were designed for functionality, featuring high collars, zippered fronts, and snug fits to protect against wind and cold at high altitudes.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Post-War Popularity</h2>
                        <p>After World War II, surplus military leather jackets became popular among civilians, marking the beginning of leather jackets as fashion items.</p>
                        
                        <h3>Cultural Impact</h3>
                        <p>Leather jackets became associated with rebellion and counterculture, popularized by motorcycle gangs and Hollywood movies.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Modern Evolution</h2>
                        <p>Today\'s leather jackets combine the durability and style of their predecessors with modern design elements and sustainable practices.</p>
                        
                        <h3>Contemporary Features</h3>
                        <p>Modern leather jackets feature improved fit, better materials, and more diverse styling options while maintaining the core elements that made them iconic.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Legacy:</strong> The leather jacket\'s journey from military necessity to fashion icon demonstrates its timeless appeal and enduring functionality.</p>
                    </div>
                </div>', '/storage/images/blogs/5.jpeg', NULL, 'published', 'Nordflex Team', 'History of Leather Jackets - From Military to Fashion Icon', 'Explore the fascinating history of leather jackets from military origins to modern fashion. Learn about their evolution and cultural impact.', '[\"leather jacket history\",\"military origins\",\"fashion evolution\",\"cultural impact\",\"vintage style\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('6', 'Choosing the Right Leather Jacket for Your Body Type', 'choosing-right-leather-jacket-body-type', 'Find the perfect leather jacket that complements your body type and enhances your personal style.', '<div class=\"blog-content\">
                    <p class=\"lead\">Find the perfect leather jacket that complements your body type and enhances your personal style.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Understanding Body Types</h2>
                        <p>Different body types benefit from different jacket styles and cuts. Understanding your body type is the first step in finding your perfect leather jacket.</p>
                        
                        <h3>Common Body Types</h3>
                        <ul>
                            <li><strong>Rectangle:</strong> Straight silhouette, balanced proportions</li>
                            <li><strong>Triangle:</strong> Broader shoulders, narrower hips</li>
                            <li><strong>Inverted Triangle:</strong> Broader shoulders, narrower waist</li>
                            <li><strong>Hourglass:</strong> Balanced shoulders and hips with defined waist</li>
                            <li><strong>Oval:</strong> Rounded midsection, balanced shoulders and hips</li>
                        </ul>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Jacket Styles for Each Body Type</h2>
                        <p>Different jacket styles can help balance proportions and create a more flattering silhouette.</p>
                        
                        <h3>Rectangle Body Type</h3>
                        <p>Choose jackets with defined waists, belts, or cinching details to create the illusion of curves.</p>
                        
                        <h3>Triangle Body Type</h3>
                        <p>Opt for jackets that add volume to the lower body or draw attention upward with interesting collar details.</p>
                        
                        <h3>Inverted Triangle Body Type</h3>
                        <p>Look for jackets that add volume to the lower body and avoid overly structured shoulders.</p>
                        
                        <h3>Hourglass Body Type</h3>
                        <p>Embrace your natural curves with fitted jackets that highlight your waist.</p>
                        
                        <h3>Oval Body Type</h3>
                        <p>Choose jackets with vertical lines and avoid horizontal details that might emphasize width.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Fit Considerations</h2>
                        <p>Beyond body type, proper fit is crucial for both comfort and style.</p>
                        
                        <h3>Key Fit Points</h3>
                        <ul>
                            <li>Shoulders should sit naturally without pulling</li>
                            <li>Arms should allow for comfortable movement</li>
                            <li>Length should complement your proportions</li>
                            <li>Closure should feel comfortable when zipped</li>
                        </ul>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Remember:</strong> The best leather jacket is one that makes you feel confident and comfortable. Don\'t be afraid to try different styles to find what works best for you.</p>
                    </div>
                </div>', '/storage/images/blogs/6.jpeg', NULL, 'published', 'Nordflex Team', 'Choose Right Leather Jacket for Your Body Type - Style Guide', 'Find the perfect leather jacket for your body type. Learn about different body types and which jacket styles work best for each.', '[\"body type\",\"style guide\",\"leather jacket fit\",\"fashion tips\",\"personal style\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('7', 'Leather Jacket Maintenance: Common Problems and Solutions', 'leather-jacket-maintenance-problems-solutions', 'Learn how to identify and solve common leather jacket problems to keep your investment looking its best.', '<div class=\"blog-content\">
                    <p class=\"lead\">Learn how to identify and solve common leather jacket problems to keep your investment looking its best.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Common Leather Problems</h2>
                        <p>Understanding common leather jacket issues helps you address them before they become serious problems.</p>
                        
                        <h3>Drying and Cracking</h3>
                        <p>Leather can dry out over time, especially in dry climates or with insufficient conditioning. This can lead to cracking and loss of flexibility.</p>
                        
                        <h3>Staining and Discoloration</h3>
                        <p>Various substances can stain leather, from food and drinks to cosmetics and environmental factors.</p>
                        
                        <h3>Odor Issues</h3>
                        <p>Leather can absorb odors from smoke, food, or other sources, creating unpleasant smells that are difficult to remove.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Prevention Strategies</h2>
                        <p>Prevention is always better than cure when it comes to leather jacket maintenance.</p>
                        
                        <h3>Regular Conditioning</h3>
                        <p>Apply a quality leather conditioner every 3-6 months to keep the leather supple and prevent drying.</p>
                        
                        <h3>Proper Storage</h3>
                        <p>Store your jacket in a cool, dry place away from direct sunlight and heat sources.</p>
                        
                        <h3>Immediate Care</h3>
                        <p>Address spills and stains immediately to prevent them from setting into the leather.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Problem-Specific Solutions</h2>
                        <p>Different problems require different approaches for effective resolution.</p>
                        
                        <h3>For Drying and Cracking</h3>
                        <p>Use a high-quality leather conditioner and consider professional restoration for severe cases.</p>
                        
                        <h3>For Staining</h3>
                        <p>Blot stains immediately, use appropriate leather cleaners, and avoid harsh chemicals that can damage the leather.</p>
                        
                        <h3>For Odor Issues</h3>
                        <p>Air out the jacket, use leather-safe deodorizers, and consider professional cleaning for persistent odors.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Pro Tip:</strong> When in doubt, consult a professional leather care specialist. They can provide expert advice and services to restore your jacket to its original condition.</p>
                    </div>
                </div>', '/storage/images/blogs/7.jpeg', NULL, 'published', 'Nordflex Team', 'Leather Jacket Maintenance - Common Problems and Solutions', 'Learn how to solve common leather jacket problems. Drying, cracking, staining, and odor issues - prevention and solutions.', '[\"leather maintenance\",\"problem solving\",\"leather care\",\"troubleshooting\",\"jacket repair\"]', '2', '0', '2025-09-16 21:21:49', '2025-09-17 12:09:56'),
('8', 'The Psychology of Leather: Why We Love Leather Jackets', 'psychology-leather-why-love-leather-jackets', 'Explore the psychological and emotional reasons behind our deep connection to leather jackets and what they represent.', '<div class=\"blog-content\">
                    <p class=\"lead\">Explore the psychological and emotional reasons behind our deep connection to leather jackets and what they represent.</p>
                    
                    <section class=\"blog-section\">
                        <h2>The Symbolism of Leather</h2>
                        <p>Leather jackets carry deep symbolic meaning that goes beyond their practical function, representing various aspects of identity and personality.</p>
                        
                        <h3>Symbols of Strength and Durability</h3>
                        <p>Leather\'s natural toughness and durability make it a symbol of strength, resilience, and endurance.</p>
                        
                        <h3>Connection to Nature</h3>
                        <p>As a natural material, leather connects us to the earth and represents authenticity and organic beauty.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Psychological Benefits</h2>
                        <p>Wearing leather jackets can have positive psychological effects on confidence and self-perception.</p>
                        
                        <h3>Confidence Boost</h3>
                        <p>The weight and feel of leather can provide a sense of protection and confidence, making wearers feel more secure and assertive.</p>
                        
                        <h3>Identity Expression</h3>
                        <p>Leather jackets allow for personal expression and can communicate aspects of personality and style preferences.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Cultural and Social Aspects</h2>
                        <p>Leather jackets have become cultural icons that represent various social groups and movements.</p>
                        
                        <h3>Rebellion and Individuality</h3>
                        <p>Historically associated with counterculture and rebellion, leather jackets represent non-conformity and individual expression.</p>
                        
                        <h3>Timeless Appeal</h3>
                        <p>The enduring popularity of leather jackets across generations speaks to their universal appeal and timeless style.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Understanding:</strong> Our love for leather jackets goes beyond fashion - it\'s about the psychological and emotional connections we form with these iconic pieces of clothing.</p>
                    </div>
                </div>', '/storage/images/blogs/8.jpeg', NULL, 'published', 'Nordflex Team', 'Psychology of Leather - Why We Love Leather Jackets', 'Explore the psychological reasons behind our love for leather jackets. Symbolism, confidence, identity, and cultural aspects.', '[\"psychology\",\"leather symbolism\",\"confidence\",\"identity\",\"cultural impact\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('9', 'Leather Jacket Investment: Why Quality Matters', 'leather-jacket-investment-quality-matters', 'Understand why investing in quality leather jackets is a smart financial and style decision that pays off in the long run.', '<div class=\"blog-content\">
                    <p class=\"lead\">Understand why investing in quality leather jackets is a smart financial and style decision that pays off in the long run.</p>
                    
                    <section class=\"blog-section\">
                        <h2>The True Cost of Quality</h2>
                        <p>While quality leather jackets may have a higher upfront cost, their long-term value often makes them more economical than cheaper alternatives.</p>
                        
                        <h3>Cost Per Wear Analysis</h3>
                        <p>When you divide the cost of a quality leather jacket by the number of times you\'ll wear it over its lifetime, the cost per wear is often lower than fast fashion alternatives.</p>
                        
                        <h3>Durability Factor</h3>
                        <p>Quality leather jackets can last 20+ years with proper care, while cheaper alternatives often need replacement within 1-2 years.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Quality Indicators</h2>
                        <p>Understanding what makes a leather jacket high-quality helps you make informed purchasing decisions.</p>
                        
                        <h3>Leather Quality</h3>
                        <p>Look for full-grain or top-grain leather, which offers the best durability and appearance retention.</p>
                        
                        <h3>Construction Quality</h3>
                        <p>Examine stitching, hardware, and overall construction to ensure the jacket is built to last.</p>
                        
                        <h3>Brand Reputation</h3>
                        <p>Choose brands known for quality and customer service, as they\'re more likely to stand behind their products.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Long-term Benefits</h2>
                        <p>Investing in quality leather jackets provides numerous long-term benefits beyond just durability.</p>
                        
                        <h3>Style Longevity</h3>
                        <p>Classic leather jacket styles remain fashionable for decades, making them a timeless investment.</p>
                        
                        <h3>Resale Value</h3>
                        <p>Well-maintained quality leather jackets can retain significant resale value, especially from reputable brands.</p>
                        
                        <h3>Environmental Impact</h3>
                        <p>Buying fewer, higher-quality items reduces environmental impact compared to frequent replacement of cheaper alternatives.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Investment Wisdom:</strong> A quality leather jacket is not just a clothing item - it\'s an investment in your style, comfort, and long-term wardrobe that will serve you well for years to come.</p>
                    </div>
                </div>', '/storage/images/blogs/9.jpeg', NULL, 'published', 'Nordflex Team', 'Leather Jacket Investment - Why Quality Matters for Long-term Value', 'Learn why investing in quality leather jackets is smart. Cost analysis, quality indicators, and long-term benefits of premium leather.', '[\"investment\",\"quality\",\"value\",\"durability\",\"cost analysis\"]', '0', '0', '2025-09-16 21:21:49', '2025-09-16 21:21:49'),
('10', 'Custom Leather Jackets: Personalizing Your Style', 'custom-leather-jackets-personalizing-style', 'Discover the world of custom leather jackets and how personalization can create the perfect piece that reflects your unique style.', '<div class=\"blog-content\">
                    <p class=\"lead\">Discover the world of custom leather jackets and how personalization can create the perfect piece that reflects your unique style.</p>
                    
                    <section class=\"blog-section\">
                        <h2>Benefits of Custom Leather Jackets</h2>
                        <p>Custom leather jackets offer numerous advantages over off-the-rack options, providing a perfect fit and unique style.</p>
                        
                        <h3>Perfect Fit</h3>
                        <p>Custom jackets are made to your exact measurements, ensuring a perfect fit that enhances your silhouette and comfort.</p>
                        
                        <h3>Unique Style</h3>
                        <p>Personalize every aspect of your jacket, from color and leather type to hardware and design details.</p>
                        
                        <h3>Quality Control</h3>
                        <p>Custom jackets often use higher quality materials and construction methods, as they\'re made with more attention to detail.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Customization Options</h2>
                        <p>The possibilities for customizing leather jackets are nearly endless, allowing you to create a truly unique piece.</p>
                        
                        <h3>Leather Selection</h3>
                        <p>Choose from various leather types, colors, and finishes to match your style preferences and needs.</p>
                        
                        <h3>Design Elements</h3>
                        <p>Customize collars, pockets, zippers, and other design elements to create your ideal look.</p>
                        
                        <h3>Personal Touches</h3>
                        <p>Add personal touches like monograms, custom linings, or special hardware to make the jacket uniquely yours.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>The Custom Process</h2>
                        <p>Understanding the custom leather jacket process helps you make informed decisions and set realistic expectations.</p>
                        
                        <h3>Consultation and Design</h3>
                        <p>Work with designers to create your vision, discussing materials, style, and fit requirements.</p>
                        
                        <h3>Measurement and Fitting</h3>
                        <p>Professional measurements ensure the perfect fit, with multiple fitting sessions to refine the design.</p>
                        
                        <h3>Construction and Delivery</h3>
                        <p>Skilled craftspeople bring your design to life, with regular updates on progress and delivery timelines.</p>
                    </section>
                    
                    <section class=\"blog-section\">
                        <h2>Investment Considerations</h2>
                        <p>Custom leather jackets represent a significant investment, but the benefits often justify the cost.</p>
                        
                        <h3>Cost vs. Value</h3>
                        <p>While custom jackets cost more upfront, their perfect fit and unique style often provide better long-term value.</p>
                        
                        <h3>Timeline Expectations</h3>
                        <p>Custom jackets take time to create, so plan accordingly and be patient with the process.</p>
                        
                        <h3>Maintenance and Care</h3>
                        <p>Custom jackets may require special care instructions, so discuss maintenance with your craftsman.</p>
                    </section>
                    
                    <div class=\"blog-conclusion\">
                        <p><strong>Personal Expression:</strong> Custom leather jackets offer the ultimate in personal expression, allowing you to create a piece that perfectly reflects your style, personality, and needs.</p>
                    </div>
                </div>', '/storage/images/blogs/10.jpeg', NULL, 'published', 'Nordflex Team', 'Custom Leather Jackets - Personalizing Your Style Guide', 'Discover custom leather jackets and personalization options. Perfect fit, unique style, customization process, and investment considerations.', '[\"custom jackets\",\"personalization\",\"unique style\",\"perfect fit\",\"customization process\"]', '1', '0', '2025-09-16 21:21:49', '2025-09-17 12:09:12');

CREATE TABLE `personal_access_tokens` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `tokenable_type` VARCHAR(255) NOT NULL, `tokenable_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `token` VARCHAR(255) NOT NULL, `abilities` VARCHAR(255), `last_used_at` datetime, `expires_at` datetime, `created_at` datetime, `updated_at` datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `personal_access_tokens`
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
('2', 'App\\Models\\User', '1', 'admin-session', '406710d6a1d1dd27c8638c9a612c2b11faba0229bc5959285b60847644428107', '[\"*\"]', '2025-09-17 17:33:12', NULL, '2025-09-17 16:58:45', '2025-09-17 17:33:12'),
('3', 'App\\Models\\User', '4', 'customer-token', '07af5f6108b684af98533120bd48963dfd068078ccf34971868463285c015228', '[\"*\"]', NULL, NULL, '2025-09-17 17:44:23', '2025-09-17 17:44:23'),
('4', 'App\\Models\\User', '4', 'customer-session', '5ea10f6e558b0ac6dbb6f7b37a70efe8943c2d844d3b9168d48035e7f1824498', '[\"*\"]', NULL, NULL, '2025-09-17 17:44:48', '2025-09-17 17:44:48');

CREATE TABLE `blog_likes` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `blog_id` INT NOT NULL, `user_id` INT, `session_id` varchar, `ip_address` varchar, `created_at` datetime, `updated_at` datetime, foreign key(`blog_id`) references `blogs`(`id`) on delete cascade, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_views` (`id` INT AUTO_INCREMENT PRIMARY KEY not null, `blog_id` INT NOT NULL, `user_id` INT, `session_id` varchar, `ip_address` varchar, `user_agent` varchar, `viewed_at` datetime not null, `created_at` datetime, `updated_at` datetime, foreign key(`blog_id`) references `blogs`(`id`) on delete cascade, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
