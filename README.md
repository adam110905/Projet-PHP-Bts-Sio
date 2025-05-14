# Projet-PHP-V2

<<<<<<< HEAD
Base de données : 

-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.0.30 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Listage de la structure de la base pour basesitebtp
CREATE DATABASE IF NOT EXISTS `basesitebtp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `basesitebtp`;

-- Listage de la structure de table basesitebtp. cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table basesitebtp.cart : ~4 rows (environ)
REPLACE INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
	(4, 2, 3, 10, '2025-05-05 09:03:15'),
	(5, 2, 1, 10, '2025-05-05 09:03:55'),
	(6, 2, 2, 10, '2025-05-05 09:04:05'),
	(7, 2, 4, 10, '2025-05-05 09:04:09');

-- Listage de la structure de table basesitebtp. orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('en attente','confirmée','expédiée','livrée') DEFAULT 'confirmée',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table basesitebtp.orders : ~7 rows (environ)
REPLACE INTO `orders` (`id`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
	(1, 3, 30.99, '2025-05-07 16:47:38', 'confirmée'),
	(2, 3, 68.49, '2025-05-07 16:51:29', 'confirmée'),
	(3, 3, 68.49, '2025-05-07 16:56:18', 'confirmée'),
	(4, 3, 87.87, '2025-05-07 16:56:25', 'confirmée'),
	(5, 3, 18.49, '2025-05-07 17:20:20', 'confirmée'),
	(6, 3, 2083.80, '2025-05-07 17:22:02', 'confirmée'),
	(7, 3, 53.96, '2025-05-08 13:23:03', 'confirmée');

-- Listage de la structure de table basesitebtp. order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table basesitebtp.order_items : ~11 rows (environ)
REPLACE INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
	(1, 1, 3, 2, 12.50),
	(2, 2, 3, 5, 12.50),
	(3, 3, 3, 5, 12.50),
	(4, 4, 1, 2, 15.99),
	(5, 4, 2, 1, 49.90),
	(6, 5, 3, 1, 12.50),
	(7, 6, 1, 10, 15.99),
	(8, 6, 2, 10, 49.90),
	(9, 6, 3, 10, 12.50),
	(10, 6, 4, 10, 129.99),
	(11, 7, 1, 3, 15.99);

-- Listage de la structure de table basesitebtp. password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table basesitebtp.password_resets : ~0 rows (environ)

-- Listage de la structure de table basesitebtp. products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table basesitebtp.products : ~4 rows (environ)
REPLACE INTO `products` (`id`, `name`, `description`, `price`, `image`, `created_at`, `category`) VALUES
	(1, 'Ciment Ultra Résistant', 'Ciment pour fondations, résiste à l’eau.', 15.99, 'sac-de-ciment.jpg', '2025-05-03 13:40:23', 'Ciment'),
	(2, 'Béton Armé Prémix', 'Béton prêt à l’emploi avec armatures intégrées.', 49.90, 'BetonPremix.jpg', '2025-05-03 13:40:23', 'Béton'),
	(3, 'Casque de chantier jaune', 'Casque de sécurité aux normes CE.', 12.50, 'CasqueChantier.jpg', '2025-05-03 13:40:23', 'Sécurité'),
	(4, 'Perceuse Pro 2200W', 'Perceuse pour murs porteurs, garantie 5 ans.', 129.99, 'PerceusePro.jpg', '2025-05-03 13:40:23', 'Outils');

-- Listage de la structure de table basesitebtp. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table basesitebtp.users : ~5 rows (environ)
REPLACE INTO `users` (`id`, `email`, `pwd`, `username`, `role`, `date`, `modification`, `status`) VALUES
	(2, 'admin@gmail.com', '$2y$10$0lhYizUvN4v9fkZDS/RMOubkik2WmJl05wRf1nQmbJ0CwQO5FiAta', 'Admin', 'admin', '2025-05-03 13:40:33', '2025-05-03 13:40:33', 'active'),
	(3, 'test@gmail.com', '$2y$10$1TM.N22UtgGX5rnKEDoZe.xSLJyFzg1/Dzv3MGonrWnmzBSLrz/oa', 'test@gmail.com', 'admin', '2025-05-03 13:42:28', '2025-05-06 09:54:42', 'active'),
	(4, 'test3@gmail.com', '$2y$10$yQusu1UA.v5beFgUxCAIkO8rsZdL2I25xVg5K/VVPo1MjgegshB9W', 'test3', 'client', '2025-05-06 11:39:42', '2025-05-06 11:39:42', 'active'),
	(5, 'adam0@gmail.com', '$2y$10$n4/xdhDowbIhXel/USu0EekXeEklF9aSZi5NMZcXD5e98ZiaueFnu', 'adam0', 'client', '2025-05-07 17:29:18', '2025-05-07 17:29:18', 'active'),
	(6, 'adam2005bouali@gmail.com', '$2y$10$HH6n0hz4ArKa4GGzuibRwOgItCZJKsyP2ZmUaDpTs5/xe4blsfCuC', 'adam', 'client', '2025-05-08 13:08:33', '2025-05-08 13:08:33', 'active');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;


=======
--- BASE DE DONNÉE DU SITE ALLOPRO ---

-> projet php v2 -> BDD -> base de donnée pour le site allopro.sql
>>>>>>> 1701091aa6e693c70ef4994734e33cf31695539b

--- CHEMIN POUR ACCÉDER AU DÉBUT ---

-> projet php v2 -> Code -> php -> offline -> lancer 'index.html'

<<<<<<< HEAD
ACCÉS A UN COMPTE ADMIN => mail : admin@gmail.com | mdp : G7u$kP-m2NYvb!

Bonne Aventure Sur Notre Site Web ! Adam et Yanis
=======
--- ACCES AU LOG D'UN COMPTE ADMIN ---

mail : admin@gmail.com | mdp : G7u$kP-m2NYvb!

----- CRÉDIT DU PROJET -----

| Yanis PERRRIN et Adam BOUALI |
>>>>>>> 1701091aa6e693c70ef4994734e33cf31695539b
