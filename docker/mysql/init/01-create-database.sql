-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS u672825292_nordflex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the user
GRANT ALL PRIVILEGES ON u672825292_nordflex.* TO 'u672825292_admin'@'%';
FLUSH PRIVILEGES;