-- Database Schema for Sofa Repair Project (Full Stack PHP)

CREATE TABLE `tenants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `subdomain` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(255) DEFAULT '/frontend/public/logo-dark.png',
  `theme_color` VARCHAR(50) DEFAULT '#ea580c',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain_unique` (`subdomain`)
);

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'provider', 'admin', 'superadmin') DEFAULT 'user',
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `email_verified` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
);

CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT 'default-category.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
);

CREATE TABLE `services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `category_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `base_price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT 'default-service.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);

CREATE TABLE `bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `provider_id` INT(11) DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
  `scheduled_date` DATETIME NOT NULL,
  `address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`provider_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `booking_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `rating` INT(1) NOT NULL,
  `quality_rating` INT(1) DEFAULT 5,
  `communication_rating` INT(1) DEFAULT 5,
  `value_rating` INT(1) DEFAULT 5,
  `comment` TEXT,
  `images` JSON DEFAULT NULL,
  `helpfulness_votes` INT(11) DEFAULT 0,
  `admin_reply` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
);

CREATE TABLE `contact_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
);

CREATE TABLE `blogs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `author_id` INT(11) NOT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `seo_score` INT(3) DEFAULT 0,
  `meta_description` TEXT DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `views` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_unique` (`slug`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('system', 'booking', 'alert', 'message') DEFAULT 'system',
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'low',
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `app_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `setting_key` VARCHAR(255) NOT NULL,
  `setting_value` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_setting_unique` (`tenant_id`, `setting_key`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
);

CREATE TABLE `otp_verifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `is_used` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `chatbot_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `response` TEXT NOT NULL,
  `intent` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Insert a default tenant
INSERT INTO `tenants` (`name`, `subdomain`) VALUES ('Default Shop', 'www');

-- Insert a default admin
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `email_verified`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', TRUE); -- password is 'password'

-- Insert default app settings
INSERT INTO `app_settings` (`tenant_id`, `setting_key`, `setting_value`) VALUES 
(1, 'show_service_prices', 'true'),
(1, 'enable_notifications', 'true'),
(1, 'seo_festival_mode', 'false');
