-- Database Schema for Sofa Repair Project (InfinityFree)

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'provider', 'admin', 'superadmin') DEFAULT 'user',
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
);

CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT 'default-category.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `base_price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT 'default-service.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);

CREATE TABLE `bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
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
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`provider_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `booking_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `rating` INT(1) NOT NULL,
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
);

CREATE TABLE `contact_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `blogs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `author_id` INT(11) NOT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_unique` (`slug`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Insert a default admin
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin'); -- password is 'password'

-- Default site settings could be added later or loaded from config.
