CREATE TABLE `cart_item_addons` (
     `id` int NOT NULL AUTO_INCREMENT,
     `cart_item_id` int DEFAULT NULL,
     `addon_id` int DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `cart_item_id` (`cart_item_id`),
      KEY `addon_id` (`addon_id`),
      CONSTRAINT `cart_item_addons_ibfk_1` FOREIGN KEY (`cart_item_id`) REFERENCES `cart_items` (`id`) ON DELETE CASCADE,
      CONSTRAINT `cart_item_addons_ibfk_2` FOREIGN KEY (`addon_id`) REFERENCES `menu_add_ons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `cart_items` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `item_id` int DEFAULT NULL,
                              `name` varchar(255) DEFAULT NULL,
                              `price` decimal(10,2) DEFAULT NULL,
                              `quantity` int DEFAULT NULL,
                              `variant` varchar(100) DEFAULT NULL,
                              `addons` text,
                              `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                              `user_id` int NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `categories` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `name` varchar(100) NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `menu_add_ons` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `item_id` int NOT NULL,
                                `addon_name` varchar(100) NOT NULL,
                                `addon_price` decimal(10,2) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `item_id` (`item_id`),
                                CONSTRAINT `menu_add_ons_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `menu_items` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) NOT NULL,
                              `description` text,
                              `price` decimal(10,2) NOT NULL,
                              `image_url` varchar(255) DEFAULT NULL,
                              `category_id` int DEFAULT NULL,
                              `available` tinyint(1) DEFAULT '1',
                              PRIMARY KEY (`id`),
                              KEY `category_id` (`category_id`),
                              CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `menu_variants` (
                                 `id` int NOT NULL AUTO_INCREMENT,
                                 `item_id` int NOT NULL,
                                 `variant_name` varchar(100) NOT NULL,
                                 `price` decimal(10,2) NOT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `item_id` (`item_id`),
                                 CONSTRAINT `menu_variants_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `payments` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `order_item_id` int NOT NULL,
                            `paid_amount` decimal(10,2) NOT NULL,
                            `paid_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`),
                            KEY `order_item_id` (`order_item_id`),
                            CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `processed_order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `processed_order` (
                                   `id` int NOT NULL AUTO_INCREMENT,
                                   `table_number` varchar(50) DEFAULT NULL,
                                   `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                   `user_id` int DEFAULT NULL,
                                   `status` varchar(20) DEFAULT 'Pending',
                                   `payment_status` varchar(20) DEFAULT 'Unpaid',
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `processed_order_items` (
                                         `id` int NOT NULL AUTO_INCREMENT,
                                         `order_id` int DEFAULT NULL,
                                         `cart_item_id` int DEFAULT NULL,
                                         `quantity` int DEFAULT NULL,
                                         `total_price` decimal(10,2) DEFAULT NULL,
                                         `status` enum('Pending','Prepared','Served','Canceled','Paid') NOT NULL DEFAULT 'Pending',
                                         PRIMARY KEY (`id`),
                                         KEY `order_id` (`order_id`),
                                         CONSTRAINT `processed_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `processed_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `table_bookings` (
                                  `booking_id` int NOT NULL AUTO_INCREMENT,
                                  `user_id` int NOT NULL,
                                  `name` varchar(100) NOT NULL,
                                  `phone` varchar(15) NOT NULL,
                                  `email` varchar(100) NOT NULL,
                                  `number_of_people` int NOT NULL,
                                  `booking_date` date NOT NULL,
                                  `booking_time` time NOT NULL,
                                  `duration` int NOT NULL,
                                  `table_number` int NOT NULL,
                                  `special_request` text,
                                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                  `status` varchar(20) DEFAULT 'active',
                                  PRIMARY KEY (`booking_id`),
                                  KEY `user_id` (`user_id`),
                                  CONSTRAINT `table_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `tables` (
                          `table_number` int NOT NULL,
                          `seats` int NOT NULL,
                          PRIMARY KEY (`table_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci


CREATE TABLE `user_feedback` (
                                 `id` int NOT NULL AUTO_INCREMENT,
                                 `message` text NOT NULL,
                                 `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                 `user_email` varchar(255) DEFAULT NULL,
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `users` (
                         `id` int NOT NULL AUTO_INCREMENT,
                         `name` varchar(255) NOT NULL,
                         `email` varchar(255) NOT NULL,
                         `password_hash` varchar(255) NOT NULL,
                         `role` varchar(50) NOT NULL,
                         `status` tinyint(1) DEFAULT '1',
                         `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `email` (`email`),
                         CONSTRAINT `users_chk_1` CHECK ((`role` in (_utf8mb4'user',_utf8mb4'admin',_utf8mb4'kitchen',_utf8mb4'cashier')))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci



CREATE TABLE `daily_summary_logs` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `summary_date` date NOT NULL,
                                      `total_orders` int NOT NULL,
                                      `total_revenue` decimal(10,2) NOT NULL,
                                      `total_bookings` int NOT NULL,
                                      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                      PRIMARY KEY (`id`),
                                      UNIQUE KEY `summary_date` (`summary_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci