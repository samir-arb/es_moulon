-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 28 oct. 2025 à 18:37
-- Version du serveur : 8.4.3
-- Version de PHP : 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `es_moulon`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id_category` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_category`, `name`, `description`) VALUES
(1, 'Séniors', NULL),
(2, 'Vétérans', NULL),
(3, 'U18', NULL),
(4, 'U17', NULL),
(5, 'U15', NULL),
(6, 'U14', NULL),
(7, 'U13', NULL),
(8, 'U12', NULL),
(9, 'U11', NULL),
(10, 'U9', NULL),
(11, 'U7', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `categories_medias`
--

CREATE TABLE `categories_medias` (
  `id_category_media` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `media_id` int DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 0xF09F9381,
  `color` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories_medias`
--

INSERT INTO `categories_medias` (`id_category_media`, `name`, `slug`, `description`, `media_id`, `icon`, `color`, `display_order`, `is_active`, `uploaded_at`, `created_at`) VALUES
(1, 'Photos d\'équipes', 'equipes', 'Photos des différentes équipes du club', NULL, '⚽', '#10b981', 1, 1, NULL, '2025-10-21 10:25:13'),
(2, 'Stade & Infrastructures', 'stade', 'Photos du stade et des installations', NULL, '🏟️', '#3b82f6', 3, 1, NULL, '2025-10-21 10:25:13'),
(3, 'Événements', 'evenements', 'Photos des événements et cérémonies', NULL, '🎉', '#f59e0b', 4, 1, NULL, '2025-10-21 10:25:13'),
(4, 'Logos & Graphismes', 'logos', 'Logos, bannières et éléments graphiques', NULL, '🎨', '#A1f723', 5, 1, NULL, '2025-10-21 10:25:13'),
(5, 'Documents officiels', 'documents', 'PDF, règlements, formulaires', NULL, '📄', '#ef4444', 6, 1, NULL, '2025-10-21 10:25:13'),
(6, 'Partenaires', 'partenaires', 'Logos et photos des partenaires', NULL, '🤝', '#06b6d4', 7, 1, NULL, '2025-10-21 10:25:13'),
(7, 'Actualités', 'actualites', 'Images pour les articles et actualités', NULL, '📰', '#ec4899', 8, 1, NULL, '2025-10-21 10:25:13'),
(8, 'Non classé', 'non-classe', 'Médias sans catégorie', NULL, '📦', '#6b7280', 99, 1, NULL, '2025-10-21 10:25:13'),
(9, 'Photos joueurs & staff', 'joueurs-staff', 'Photos officielles des joueurs et du staff technique', NULL, '👥', '#1e40af', 2, 1, NULL, '2025-10-21 11:31:17');

-- --------------------------------------------------------

--
-- Structure de la table `club_functions`
--

CREATE TABLE `club_functions` (
  `id_club_function` int NOT NULL,
  `function_name` varchar(100) NOT NULL,
  `function_type` enum('sportif','administratif') NOT NULL,
  `ordre_affichage` int NOT NULL DEFAULT '99'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `club_functions`
--

INSERT INTO `club_functions` (`id_club_function`, `function_name`, `function_type`, `ordre_affichage`) VALUES
(1, 'Joueur', 'sportif', 99),
(2, 'Responsable sportif', 'sportif', 1),
(3, 'Responsable école de foot', 'sportif', 2),
(4, 'Entraîneur', 'sportif', 3),
(5, 'Entraîneur adjoint', 'sportif', 4),
(6, 'Préparateur physique', 'sportif', 5),
(7, 'Préparateur gardiens', 'sportif', 6),
(8, 'Arbitre', 'sportif', 7),
(9, 'Président', 'administratif', 1),
(10, 'Président adjoint', 'administratif', 2),
(11, 'Trésorier', 'administratif', 3),
(12, 'Trésorier adjoint', 'administratif', 4),
(13, 'Secrétaire', 'administratif', 5),
(14, 'Responsable communication', 'administratif', 6),
(15, 'Responsable partenariat', 'administratif', 7),
(16, 'Responsable logistique', 'administratif', 8),
(17, 'Dirigeant', 'administratif', 9),
(18, 'Bénévole', 'administratif', 10);

-- --------------------------------------------------------

--
-- Structure de la table `club_info`
--

CREATE TABLE `club_info` (
  `id_club_info` int NOT NULL,
  `stadium_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `google_maps_url` text,
  `id_media` int DEFAULT NULL,
  `monday_open` time DEFAULT NULL,
  `monday_close` time DEFAULT NULL,
  `tuesday_open` time DEFAULT NULL,
  `tuesday_close` time DEFAULT NULL,
  `wednesday_open` time DEFAULT NULL,
  `wednesday_close` time DEFAULT NULL,
  `thursday_open` time DEFAULT NULL,
  `thursday_close` time DEFAULT NULL,
  `friday_open` time DEFAULT NULL,
  `friday_close` time DEFAULT NULL,
  `saturday_open` time DEFAULT NULL,
  `saturday_close` time DEFAULT NULL,
  `sunday_open` time DEFAULT NULL,
  `sunday_close` time DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `club_info`
--

INSERT INTO `club_info` (`id_club_info`, `stadium_name`, `address`, `google_maps_url`, `id_media`, `monday_open`, `monday_close`, `tuesday_open`, `tuesday_close`, `wednesday_open`, `wednesday_close`, `thursday_open`, `thursday_close`, `friday_open`, `friday_close`, `saturday_open`, `saturday_close`, `sunday_open`, `sunday_close`, `updated_at`) VALUES
(1, 'Stade Jacques Loubier', 'Rue de la Sente aux Loups, 18000 Bourges', 'https://www.google.com/maps/place/Esp%C3%A9rance+Sportive+du+Moulon/@47.1027486,2.3995011,566m/data=!3m1!1e3!4m15!1m8!3m7!1s0x47fa9605beea563b:0x3f5dd5694273243a!2sRue+de+la+Sente+aux+Loups,+18000+Bourges!3b1!8m2!3d47.102745!4d2.402076!16s%2Fg%2F1xcbn_1k!3m5!1s0x47fa9605bc2e8953:0xc0a24c49a3748a44!8m2!3d47.1025275!4d2.401022!16s%2Fg%2F1v_z87xn?entry=ttu&g_ep=EgoyMDI1MTAxOS4wIKXMDSoASAFQAw%3D%3D', 62, '10:00:00', '17:00:00', '10:00:00', '17:00:00', '14:00:00', '19:00:00', '10:00:00', '17:00:00', '10:00:00', '20:00:00', NULL, NULL, NULL, NULL, '2025-10-21 08:39:31');

-- --------------------------------------------------------

--
-- Structure de la table `club_structure`
--

CREATE TABLE `club_structure` (
  `id_structure` int NOT NULL,
  `type_structure` enum('administratif','sportif') NOT NULL,
  `id_club_function` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `position_number` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `club_structure`
--

INSERT INTO `club_structure` (`id_structure`, `type_structure`, `id_club_function`, `id_user`, `parent_id`, `position_number`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(15, 'sportif', 9, 10, NULL, 0, 1, 10, NULL, '2025-10-10 14:26:00', '2025-10-10 14:26:00'),
(16, 'administratif', 9, 10, NULL, 0, 1, 10, 10, '2025-10-10 14:26:20', '2025-10-10 14:26:32'),
(17, 'administratif', 10, 9, 16, 1, 1, 10, NULL, '2025-10-10 14:27:14', '2025-10-10 14:27:14'),
(18, 'sportif', 10, 9, 15, 1, 1, 10, 10, '2025-10-10 14:27:45', '2025-10-10 15:55:28'),
(19, 'administratif', 14, 25, 17, 2, 1, 10, 10, '2025-10-10 14:29:05', '2025-10-10 14:30:34'),
(20, 'sportif', 2, 31, 18, 2, 1, 10, 10, '2025-10-10 15:54:45', '2025-10-10 15:56:22'),
(21, 'sportif', 3, 27, 20, 3, 1, 10, NULL, '2025-10-10 15:57:51', '2025-10-10 15:57:51'),
(22, 'sportif', 3, 28, 20, 4, 1, 10, NULL, '2025-10-10 15:59:43', '2025-10-10 15:59:43'),
(23, 'administratif', 18, 30, NULL, 7, 1, 10, NULL, '2025-10-10 17:33:26', '2025-10-10 17:33:26');

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `id_contact` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text,
  `contact_type` enum('arbitre','benevole','partenaire','contact') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `sent_at` date NOT NULL,
  `status` enum('en attente','accepte','rejete') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `response_date` datetime DEFAULT NULL,
  `response` text,
  `id_user` int DEFAULT NULL,
  `id_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `convocations`
--

CREATE TABLE `convocations` (
  `id_convocation` int NOT NULL,
  `id_team` int NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `opponent` varchar(255) NOT NULL,
  `home_away` enum('domicile','extérieur') NOT NULL,
  `location` varchar(255) NOT NULL,
  `meeting_time` time DEFAULT NULL,
  `meeting_place` varchar(255) DEFAULT NULL,
  `message` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `convocations`
--

INSERT INTO `convocations` (`id_convocation`, `id_team`, `match_date`, `match_time`, `opponent`, `home_away`, `location`, `meeting_time`, `meeting_place`, `message`, `created_by`, `created_at`) VALUES
(15, 4, '2025-10-25', '17:00:00', 'mehun', 'domicile', '', '14:30:00', 'stade du moulon', '', 10, '2025-10-21 21:04:32'),
(16, 4, '2025-10-25', '18:00:00', 'chateauroux', 'domicile', '', '16:00:00', '', '', 10, '2025-10-21 21:22:01'),
(17, 5, '2025-10-25', '21:00:00', 'FC Blois', 'domicile', '', '00:00:00', '', '', 10, '2025-10-21 21:23:27');

-- --------------------------------------------------------

--
-- Structure de la table `convocation_players`
--

CREATE TABLE `convocation_players` (
  `id_convocation_player` int NOT NULL,
  `id_convocation` int NOT NULL,
  `id_player` int NOT NULL,
  `status` enum('convoqué','présent','absent','non répondu') DEFAULT 'convoqué'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `convocation_players`
--

INSERT INTO `convocation_players` (`id_convocation_player`, `id_convocation`, `id_player`, `status`) VALUES
(54, 15, 61, 'convoqué'),
(55, 15, 40, 'convoqué'),
(56, 15, 35, 'convoqué'),
(57, 16, 37, 'convoqué'),
(58, 16, 62, 'convoqué'),
(59, 16, 40, 'convoqué'),
(60, 16, 35, 'convoqué'),
(61, 16, 63, 'convoqué'),
(62, 16, 36, 'convoqué'),
(63, 17, 37, 'convoqué'),
(64, 17, 62, 'convoqué'),
(65, 17, 61, 'convoqué'),
(66, 17, 40, 'convoqué'),
(67, 17, 35, 'convoqué'),
(68, 17, 63, 'convoqué'),
(69, 17, 60, 'convoqué'),
(70, 17, 36, 'convoqué');

-- --------------------------------------------------------

--
-- Structure de la table `home_blocks`
--

CREATE TABLE `home_blocks` (
  `id_home_block` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `button_link` varchar(255) DEFAULT NULL,
  `block_type` enum('resultats','actualites','club','partner') NOT NULL,
  `display_order` int NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `id_user` int NOT NULL,
  `id_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `matches`
--

CREATE TABLE `matches` (
  `id_match` int NOT NULL,
  `match_type` enum('championnat','coupe','amical') NOT NULL DEFAULT 'championnat',
  `match_date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `competition_level` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `phase` enum('Aller','Retour') DEFAULT NULL,
  `home_score` int DEFAULT NULL,
  `away_score` int DEFAULT NULL,
  `id_home_team` int DEFAULT NULL,
  `id_away_team` int DEFAULT NULL,
  `id_season` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `matches`
--

INSERT INTO `matches` (`id_match`, `match_type`, `match_date`, `location`, `competition_level`, `phase`, `home_score`, `away_score`, `id_home_team`, `id_away_team`, `id_season`, `id_user`, `id_media`) VALUES
(40, 'championnat', '2025-08-23 20:00:00', 'Stade Yves Du Manoir', 'R1', 'Aller', 2, 2, 22, 21, 1, NULL, NULL),
(44, 'championnat', '2025-10-04 20:00:00', 'Stade Yves Du Manoir 18000 Bourges', 'R1', 'Aller', 1, 1, 2, 24, 1, NULL, NULL),
(46, 'championnat', '2025-11-01 20:00:00', 'Stade Yves Du Manoir 18000 Bourges', 'R1', 'Aller', NULL, NULL, 2, 27, 1, NULL, NULL),
(47, 'championnat', '2025-09-06 20:00:00', 'Stade Yves Du Manoir 18000 Bourges', 'R1', 'Aller', 0, 1, 2, 23, 1, NULL, NULL),
(48, 'championnat', '2026-01-10 20:00:00', 'Stade Yves Du Manoir', 'R1', 'Aller', NULL, NULL, 2, 20, 1, NULL, NULL),
(50, 'championnat', '2025-10-11 18:00:00', 'Complexe Sportif Guy Drut 1', 'R1', 'Aller', 3, 0, 25, 22, 1, NULL, NULL),
(67, 'championnat', '2025-09-21 15:00:00', 'Stade Des Allées Jean Leroi 1,  41000 Blois', NULL, 'Aller', 1, 2, 26, 2, 1, NULL, NULL),
(68, 'championnat', '2025-12-13 20:00:00', 'Stade Yves Du Manoir', NULL, 'Aller', NULL, NULL, 2, 28, 1, NULL, NULL),
(69, 'championnat', '2025-11-22 20:00:00', 'Stade Yves Du Manoir 18000 Bourges', NULL, 'Aller', NULL, NULL, 2, 22, 1, NULL, NULL),
(70, 'championnat', '2025-12-07 15:00:00', 'Stade Lionel Charbonnier', NULL, 'Aller', NULL, NULL, 29, 2, 1, NULL, NULL),
(71, 'championnat', '2026-01-18 15:00:00', 'Stade Bernard Maroquin', NULL, 'Aller', NULL, NULL, 30, 2, 1, NULL, NULL),
(72, 'championnat', '2026-01-24 20:00:00', 'Stade Yves Du Manoir', NULL, 'Retour', NULL, NULL, 2, 25, 1, NULL, NULL),
(73, 'championnat', '2026-02-07 18:30:00', 'Stade Marce Vignaud', NULL, 'Retour', NULL, NULL, 23, 2, 1, NULL, NULL),
(74, 'championnat', '2026-02-14 20:00:00', 'Stade Yves Du Manoir', NULL, 'Retour', NULL, NULL, 2, 26, 1, NULL, NULL),
(75, 'championnat', '2026-02-21 19:30:00', 'Stade Lièvre D\'or', NULL, 'Retour', NULL, NULL, 24, 2, 1, NULL, NULL),
(76, 'championnat', '2026-03-07 20:00:00', 'Stade Yves Du Manoir', NULL, 'Retour', NULL, NULL, 2, 31, 1, NULL, NULL),
(77, 'championnat', '2025-10-18 18:00:00', 'Stade Emile Leger 37380 Monnaie', NULL, 'Aller', 1, 3, 31, 2, 1, NULL, NULL),
(78, 'championnat', '2026-03-15 15:00:00', 'Complexe Sportif De La Haye', NULL, 'Retour', NULL, NULL, 27, 2, 1, NULL, NULL),
(79, 'championnat', '2026-03-21 20:00:00', 'Stade Yves Du Manoir', NULL, 'Retour', NULL, NULL, 2, 32, 1, NULL, NULL),
(80, 'championnat', '2026-03-28 18:00:00', 'Stade Municipal', NULL, 'Retour', NULL, NULL, 22, 2, 1, NULL, NULL),
(81, 'championnat', '2026-04-11 20:00:00', 'Stade Yves Du Manoir', NULL, 'Retour', NULL, NULL, 2, 29, 1, NULL, NULL),
(82, 'championnat', '2026-04-19 15:00:00', 'Stade Fernand Sastre', NULL, 'Retour', NULL, NULL, 28, 2, 1, NULL, NULL),
(83, 'championnat', '2026-04-25 18:30:00', 'Stade Jean Bruck', NULL, 'Retour', NULL, NULL, 20, 2, 1, NULL, NULL),
(85, 'championnat', '2026-05-17 15:00:00', 'Stade De La Loge', NULL, 'Retour', NULL, NULL, 21, 2, 1, NULL, NULL),
(86, 'coupe', '2025-08-30 19:00:00', 'Stade Jean Delaveau 36330 Le Poinconnet', NULL, 'Aller', 5, 1, 33, 2, 1, NULL, NULL),
(87, 'championnat', '2025-08-23 20:00:00', 'Stade Yves Du Manoir 18000 Bourges', NULL, 'Aller', 2, 2, 2, 21, 1, NULL, NULL),
(88, 'championnat', '2025-10-11 18:00:00', 'Complexe Sportif Guy Drut 1, 37540 St CYR Sur Loire', NULL, 'Aller', 3, 0, 25, 2, 1, NULL, NULL),
(89, 'coupe', '2025-10-26 14:30:00', 'Stade de Brouhot , Rue Henri Barbusse 18110 Vierzon', NULL, 'Aller', 1, 3, 43, 2, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `medias`
--

CREATE TABLE `medias` (
  `id_media` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `usage_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `description` text,
  `id_user` int DEFAULT NULL,
  `id_category_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `medias`
--

INSERT INTO `medias` (`id_media`, `file_name`, `file_type`, `usage_type`, `file_path`, `uploaded_at`, `description`, `id_user`, `id_category_media`) VALUES
(5, 'media_68e563fbeb202.webp', 'image/webp', NULL, 'uploads/media_68e563fbeb202.webp', '2025-10-07 21:03:23', 'MONIN logo', 10, 6),
(6, 'loups-moulon.webp', 'image/webp', NULL, 'uploads/loups-moulon.webp', '2025-10-07 21:34:28', 'fresque de l\'es moulon', 10, 4),
(7, 'logo_intersport.webp', 'image/webp', NULL, 'uploads/logo_intersport.webp', '2025-10-07 21:34:59', 'INTERSPORT logo', 10, 6),
(8, 'logo_moulon.webp', 'image/webp', NULL, 'uploads/logo_moulon.webp', '2025-10-07 21:35:57', 'logo de l\'ES MOULON', 10, 4),
(9, 'media_68e56bc4a6d7c.webp', 'image/webp', NULL, 'uploads/media_68e56bc4a6d7c.webp', '2025-10-07 21:36:36', 'match des U17', 10, 8),
(10, 'VSAJ.webp', 'image/webp', NULL, 'uploads/VSAJ.webp', '2025-10-07 21:37:09', 'logo VSAJ', 10, 8),
(11, 'media_68e56c009ce5c.webp', 'image/webp', NULL, 'uploads/media_68e56c009ce5c.webp', '2025-10-07 21:37:36', 'LOGO US ORLEANS', 10, 4),
(15, '347584085_659327452891754_6567307010548331243_n_1759924309.webp', 'image/webp', NULL, 'uploads/347584085_659327452891754_6567307010548331243_n_1759924309.webp', '2025-10-08 13:51:49', '', 10, 6),
(16, '469691241_708344678191028_4893659730431193954_n_1759924317.webp', 'image/webp', NULL, 'uploads/469691241_708344678191028_4893659730431193954_n_1759924317.webp', '2025-10-08 13:51:57', '', 10, 8),
(17, 'match_days_1759924460.webp', 'image/webp', NULL, 'uploads/match_days_1759924460.webp', '2025-10-08 13:54:20', '', 10, 8),
(18, '485160916_963188052693916_3946446804460103170_n_1759924600.webp', 'image/webp', NULL, 'uploads/485160916_963188052693916_3946446804460103170_n_1759924600.webp', '2025-10-08 13:56:40', '', 10, 8),
(19, 'match_contre_deols.webp', 'image/webp', NULL, 'uploads/match_contre_deols.webp', '2025-10-08 13:58:48', '', 10, 7),
(20, 'happy_days.webp', 'image/webp', NULL, 'uploads/happy_days.webp', '2025-10-08 14:15:18', '', 10, 6),
(21, 'sponsors.webp', 'image/webp', 'partner_footer', 'uploads/sponsors.webp', '2025-10-08 14:15:30', '', 10, 6),
(22, '488885430_976857111327010_1554036884513363493_n_1759925744.webp', 'image/webp', NULL, 'uploads/488885430_976857111327010_1554036884513363493_n_1759925744.webp', '2025-10-08 14:15:44', '', 10, 7),
(23, '501127979_1017440497268671_8705885293043676621_n__1__1759925761.webp', 'image/webp', NULL, 'uploads/501127979_1017440497268671_8705885293043676621_n__1__1759925761.webp', '2025-10-08 14:16:01', '', 10, 8),
(24, '503427404_1017440590601995_2778607143835076775_n_1759925779.webp', 'image/webp', NULL, 'uploads/503427404_1017440590601995_2778607143835076775_n_1759925779.webp', '2025-10-08 14:16:19', '', 10, 8),
(25, 'VETERANT.webp', 'image/webp', NULL, 'uploads/VETERANT.webp', '2025-10-08 14:16:38', '', 10, 1),
(26, '483105471_958448456501209_119159498194121039_n_1759925813.webp', 'image/webp', NULL, 'uploads/483105471_958448456501209_119159498194121039_n_1759925813.webp', '2025-10-08 14:16:53', '', 10, 8),
(27, 'Capture_le_club-removebg-preview_1759925837.webp', 'image/webp', NULL, 'uploads/Capture_le_club-removebg-preview_1759925837.webp', '2025-10-08 14:17:17', '', 10, 8),
(28, 'Fond_d_ecran.webp', 'image/webp', NULL, 'uploads/Fond_d_ecran.webp', '2025-10-08 14:17:49', '', 10, 8),
(29, 'match_23082025_1759927196.webp', 'image/webp', NULL, 'uploads/match_23082025_1759927196.webp', '2025-10-08 14:39:56', '', 10, 8),
(30, 'preparation-physique-integree-au-football-min_jpg_1759927873.webp', 'image/webp', NULL, 'uploads/preparation-physique-integree-au-football-min_jpg_1759927873.webp', '2025-10-08 14:51:13', '', 10, 8),
(31, 'U13.webp', 'image/webp', NULL, 'uploads/U13.webp', '2025-10-08 14:54:02', '', 10, 8),
(32, '493746273_992402986439089_1476673470310472777_n_1759929658.webp', 'image/webp', NULL, 'uploads/493746273_992402986439089_1476673470310472777_n_1759929658.webp', '2025-10-08 15:20:58', '', 10, 8),
(33, '347584085_659327452891754_6567307010548331243_n_1759941065.webp', 'image/webp', NULL, 'uploads/347584085_659327452891754_6567307010548331243_n_1759941065.webp', '2025-10-08 18:31:05', '', 10, 6),
(34, '23_PIZZA_STREET.webp', 'image/webp', NULL, 'uploads/23_PIZZA_STREET.webp', '2025-10-09 00:05:53', '', 10, 6),
(35, 'AB-CLIM.webp', 'image/webp', NULL, 'uploads/AB-CLIM.webp', '2025-10-09 00:06:26', '', 10, 6),
(36, 'CAZ_APIZZ.webp', 'image/webp', NULL, 'uploads/CAZ_APIZZ.webp', '2025-10-09 00:12:22', '', 10, 6),
(37, 'VILLE_DE_BOURGES.webp', 'image/webp', NULL, 'uploads/VILLE_DE_BOURGES.webp', '2025-10-09 00:12:54', '', 10, 6),
(39, 'didier_deschamp_1760176338.webp', 'image/webp', NULL, 'uploads/didier_deschamp_1760176338.webp', '2025-10-11 11:52:18', '', 10, 1),
(40, 'intendant.webp', 'image/webp', NULL, 'uploads/intendant.webp', '2025-10-11 11:52:43', '', 10, 1),
(41, 'GUY_STEPHAN.webp', 'image/webp', NULL, 'uploads/GUY_STEPHAN.webp', '2025-10-11 11:55:28', '', 10, 1),
(42, 'Cyril_moine.webp', 'image/webp', NULL, 'uploads/Cyril_moine.webp', '2025-10-11 12:46:34', '', 10, 9),
(43, 'logo_vineuil_sp.webp', 'image/webp', NULL, 'uploads/logo_vineuil_sp.webp', '2025-10-15 14:18:00', '', 10, 4),
(44, 'logo_Azay_Cheille.webp', 'image/webp', NULL, 'uploads/logo_Azay_Cheille.webp', '2025-10-15 14:30:37', '', 10, 4),
(45, 'logo_blois_foot.webp', 'image/webp', NULL, 'uploads/logo_blois_foot.webp', '2025-10-15 14:31:40', '', 10, 4),
(46, 'logo_Bourges_Fc.webp', 'image/webp', NULL, 'uploads/logo_Bourges_Fc.webp', '2025-10-15 14:32:05', '', 10, 4),
(47, 'logo_CJF_Fleury_les_aub.webp', 'image/webp', NULL, 'uploads/logo_CJF_Fleury_les_aub.webp', '2025-10-15 14:32:26', '', 10, 4),
(48, 'logo_cs_mainvilliers.webp', 'image/webp', NULL, 'uploads/logo_cs_mainvilliers.webp', '2025-10-15 14:32:44', '', 10, 4),
(49, 'logo_fc_drouais.webp', 'image/webp', NULL, 'uploads/logo_fc_drouais.webp', '2025-10-15 14:33:07', '', 10, 4),
(50, 'logo_FC_ST_Jean_le_blanc.webp', 'image/webp', NULL, 'uploads/logo_FC_ST_Jean_le_blanc.webp', '2025-10-15 14:33:24', '', 10, 4),
(51, 'logo_monnaie_us.webp', 'image/webp', NULL, 'uploads/logo_monnaie_us.webp', '2025-10-15 14:33:40', '', 10, 4),
(52, 'logo_ST__CYR_SUR_LOIRE.webp', 'image/webp', NULL, 'uploads/logo_ST__CYR_SUR_LOIRE.webp', '2025-10-15 14:34:02', '', 10, 4),
(53, 'logo_union_foot_touraine.webp', 'image/webp', NULL, 'uploads/logo_union_foot_touraine.webp', '2025-10-15 14:34:19', '', 10, 4),
(54, 'logo_US_Chateauneuf_sl.webp', 'image/webp', NULL, 'uploads/logo_US_Chateauneuf_sl.webp', '2025-10-15 14:34:48', '', 10, 4),
(55, 'logo_Avoine_chinon.webp', 'image/webp', NULL, 'uploads/logo_Avoine_chinon.webp', '2025-10-15 14:36:44', '', 10, 4),
(56, 'logo_us_poinconet.webp', 'image/webp', NULL, 'uploads/logo_us_poinconet.webp', '2025-10-15 14:47:10', '', 10, 4),
(58, 'VIERZON_FOOT_1760959502.webp', 'image/webp', NULL, 'uploads/VIERZON_FOOT_1760959502.webp', '2025-10-20 13:25:02', '', 10, 4),
(59, 'resultats_contre_monnaie_1760973804.webp', 'image/webp', NULL, 'uploads/resultats_contre_monnaie_1760973804.webp', '2025-10-20 17:23:24', '', 10, 8),
(62, 'terrain_esm_1761034672.webp', 'image/webp', NULL, 'uploads/terrain_esm_1761034672.webp', '2025-10-21 10:17:52', '', 10, 2);

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `id_new` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `published_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `id_media` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`id_new`, `title`, `content`, `published_at`, `updated_at`, `id_user`, `id_media`, `status`) VALUES
(2, 'Toutes les actualités', 'test', '2025-10-06 17:36:00', NULL, NULL, 9, 1),
(5, 'Victoire de l’équipe première', NULL, '2025-10-05 17:49:12', NULL, NULL, NULL, 1),
(7, 'OK OK OK', 'Une fin de saison GRANDIOSE!!!!!', '2025-10-07 17:11:00', '2025-10-08 09:37:16', 10, NULL, 1),
(8, 'ah le casse tete', 'jfbhJDBFDJN', '2025-10-07 18:30:00', '2025-10-20 18:38:52', 10, NULL, 1),
(9, 'oulalalalalalal', 'sa recommence', '2025-10-07 16:16:58', NULL, 10, NULL, 1),
(10, 'BONJOUR', 'Hello the World', '2025-10-07 16:43:00', '2025-10-20 18:39:28', 10, NULL, 1),
(11, 'Enfin ma page fonctionne', 'Apres des heureshttps://localhost/es_moulon/uploads/medias/media_68e56c009ce5c.png et des jours de bug j\'ai enfin réussi ...', '2025-10-07 18:52:00', '2025-10-08 09:37:59', 10, 6, 1),
(12, 'IMAGE TESTE', '', '2025-10-07 23:39:00', '2025-10-08 10:40:16', 10, 7, 1),
(13, 'Mercredi', 'un nouveau test', '2025-10-08 08:27:02', NULL, 10, 10, 1),
(14, 'VICTOIRE', 'La R1 s\'impose sur le terrain de DEOLS par une victoir de 3 à 1. les buteurs sont tintin 36ème, kadir 63ème et mourad 82ème, les locaux ont marqués a la toutes fin de match 92éme.', '2025-10-08 13:10:00', '2025-10-08 13:59:07', 10, 19, 1),
(17, '𝐋𝐞𝐬 𝟑 𝐩𝐨𝐢𝐧𝐭𝐬 𝐬𝐨𝐧𝐭 𝐝𝐚𝐧𝐬 𝐥𝐚 𝐩𝐨𝐜𝐡𝐞 ! 🏆⚽️', 'Belle victoire de nos seniors R1 sur la pelouse de Monnaie 🚌\r\nUne équipe soudée, du jeu, de l’envie : tout y était ! 🟢⚪️\r\nPlace à la suite 💪', '2025-10-20 17:24:08', NULL, 10, 59, 1);

-- --------------------------------------------------------

--
-- Structure de la table `partners`
--

CREATE TABLE `partners` (
  `id_partner` int NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `partnership_type` varchar(100) DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `star_date` date DEFAULT NULL,
  `display_order` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `id_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `partners`
--

INSERT INTO `partners` (`id_partner`, `company_name`, `logo`, `partnership_type`, `redirect_url`, `description`, `is_active`, `star_date`, `display_order`, `id_user`, `id_media`) VALUES
(1, 'HAPPY DAYS', NULL, 'Sponsor', '', 'Partenaire officiel depuis 2020', 1, '2020-01-01', 3, NULL, 20),
(2, 'MONIN', NULL, 'Sponsor principal', '', 'Sponsor maillot', 1, '2019-06-15', 4, NULL, 5),
(3, 'FOOD MARKET', NULL, 'Fournisseur', '', 'Partenaire alimentaire', 1, '2021-03-10', 2, NULL, 33),
(4, 'VSAJ', NULL, NULL, 'https://www.facebook.com/guinguettemoderne/', '', 1, NULL, 1, NULL, 10),
(5, 'INTERSPORT', NULL, NULL, '', '', 1, NULL, 5, NULL, 7),
(6, 'AB-CLIM', NULL, NULL, '', '', 1, NULL, 6, NULL, 35),
(7, '23 PIZZA STREET', NULL, NULL, '', '', 1, NULL, 7, NULL, 34),
(8, 'CAZ A PIZZ\'', NULL, NULL, '', '', 1, NULL, 8, NULL, 36),
(9, 'VILLE DE BOURGES', NULL, NULL, '', '', 1, NULL, 9, NULL, 37);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id_role` int NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id_role`, `role_name`, `description`) VALUES
(1, 'ROLE_LICENSED', 'Licencié : aucun accès back-office'),
(2, 'ROLE_ADMIN', 'Accès complet au back-office'),
(3, 'ROLE_EDITOR', 'Peut gérer articles/actualités/médias'),
(4, 'ROLE_SPORT_MANAGER', 'Gestion sportive (équipes/joueurs/matchs)'),
(5, 'ROLE_MODERATOR', 'Modération des contenus');

-- --------------------------------------------------------

--
-- Structure de la table `seasons`
--

CREATE TABLE `seasons` (
  `id_season` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `seasons`
--

INSERT INTO `seasons` (`id_season`, `name`, `start_date`, `end_date`, `is_active`) VALUES
(1, '2025/2026', '2025-07-01', '2026-06-30', 1);

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

CREATE TABLE `site_settings` (
  `id_site_setting` int NOT NULL COMMENT 'Clé primaire (toujours = 1)',
  `hero_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Bienvenue à l''ES Moulon',
  `hero_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Passion, Excellence, Fraternité',
  `hero_lead` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_hero_media` int DEFAULT NULL COMMENT 'FK vers medias.id_media - img de fond Hero ',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paramètres globaux du site (1 seule ligne)';

--
-- Déchargement des données de la table `site_settings`
--

INSERT INTO `site_settings` (`id_site_setting`, `hero_title`, `hero_subtitle`, `hero_lead`, `id_hero_media`, `updated_at`) VALUES
(1, 'Bienvenue sur le site de', 'L\'ES MOULON', 'Depuis 1940, notre club s\'engage à développer le football local et à promouvoir les valeurs du sport.', 6, '2025-10-28 17:07:20');

-- --------------------------------------------------------

--
-- Structure de la table `teams`
--

CREATE TABLE `teams` (
  `id_team` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `id_club_team` tinyint(1) NOT NULL DEFAULT '1',
  `id_category` int DEFAULT NULL,
  `id_media` int DEFAULT NULL,
  `id_team_logo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `teams`
--

INSERT INTO `teams` (`id_team`, `name`, `level`, `id_club_team`, `id_category`, `id_media`, `id_team_logo`) VALUES
(2, 'E.S.MOULON 1', 'Régionale 1', 1, 1, 22, 8),
(3, 'E.S.MOULON 2', 'Régionale 3', 1, 1, 29, 8),
(4, 'E.S.MOULON 3', 'Départemental 1', 1, 1, 23, 8),
(5, 'VÉTÉRANS', 'Loisir', 1, 2, 25, NULL),
(6, 'E.S.MOULON U14', 'Régionale 1', 1, 6, 31, NULL),
(8, 'E.S.MOULON U7', 'Départemental', 1, 11, 31, NULL),
(9, 'E.S.MOULON U9', 'Départemental', 1, 10, NULL, NULL),
(20, 'DREUX FC DROU.', 'R1', 0, 1, 49, 49),
(21, 'AZAY CHEILLE', 'R1', 0, 1, 44, 44),
(22, 'VINEUIL SP.', 'R1', 0, 1, 43, 43),
(23, 'AVOINE O. CHINON C.', 'R1', 0, 1, 55, 55),
(24, 'U.S. CHATEAUNEUF S/L', 'R1', 0, 1, 54, 54),
(25, 'EB ST CYR S/L', 'R1', 0, 1, 52, 52),
(26, 'BLOIS F. 41  2', 'R1', 0, 1, 45, 45),
(27, 'UNION FOOT TOURAINE 2', 'R1', 0, 1, 53, 53),
(28, 'CJF FLEURY LES AUB.', 'R1', 0, 1, 47, 47),
(29, 'FC ST JEAN LE BLANC', 'R1', 0, 1, 50, 50),
(30, 'C.S MAINVILLIERS FO', 'R1', 0, 1, 48, 48),
(31, 'MONNAIE US', 'R1', 0, 1, 51, 51),
(32, 'BOURGES FC 2', 'R1', 0, 1, 46, 46),
(33, 'POINCONNET US', 'R3', 0, 1, 56, 56),
(34, 'E.S.MOULON U17', 'Départemental 1', 1, 4, 7, NULL),
(35, 'OM', 'Ligue 1', 0, 1, 10, NULL),
(36, 'E.S.MOULON U18', 'Régional 2', 1, 3, NULL, NULL),
(37, 'E.S.MOULON U15', 'Départemental 1', 1, 5, NULL, NULL),
(38, 'E.S.MOULON U13 - 1', 'Départemental 1', 1, 7, NULL, NULL),
(39, 'E.S.MOULON U13 - 2', 'Départemental 3', 1, 7, NULL, NULL),
(40, 'E.S.MOULON U12', 'Départemental 1', 1, 8, NULL, NULL),
(41, 'E.S.MOULON U11 - 1', 'Départemental 2', 1, 9, NULL, NULL),
(42, 'E.S.MOULON U11 - 2', 'Départemental 3', 1, 9, NULL, NULL),
(43, 'VIERZON FC 2', 'Régional 2', 0, 1, NULL, 58);

-- --------------------------------------------------------

--
-- Structure de la table `teams_seasons`
--

CREATE TABLE `teams_seasons` (
  `id_team_season` int NOT NULL,
  `id_season` int NOT NULL,
  `id_team` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `teams_seasons`
--

INSERT INTO `teams_seasons` (`id_team_season`, `id_season`, `id_team`) VALUES
(1, 1, 6),
(3, 1, 8),
(4, 1, 9),
(5, 1, 34),
(6, 1, 36),
(7, 1, 37),
(8, 1, 38),
(9, 1, 39),
(10, 1, 40),
(11, 1, 41),
(12, 1, 42);

-- --------------------------------------------------------

--
-- Structure de la table `trainings`
--

CREATE TABLE `trainings` (
  `id_training` int NOT NULL,
  `id_team` int NOT NULL,
  `day_of_week` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `notes` text,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `id_role` int NOT NULL,
  `has_backoffice_access` tinyint(1) DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `is_password_set` tinyint(1) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_media` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `id_role`, `has_backoffice_access`, `name`, `first_name`, `birth_date`, `phone`, `address`, `email`, `password`, `reset_token`, `reset_token_expiry`, `is_password_set`, `status`, `created_at`, `updated`, `id_media`) VALUES
(9, 4, 1, 'Admin', 'Test', NULL, NULL, NULL, 'admin@test.com', '$2y$10$pTbTPuHc1GxdHbUFszq4aOUk6XVyIQLxJZsjWdR4eSZvRt0oaSI.W', NULL, NULL, NULL, 1, '2025-10-01 12:04:51', '2025-10-05 19:15:04', NULL),
(10, 2, 1, 'Arb', 'samir', NULL, NULL, NULL, 'samir.dwwm@gmail.com', '$2y$10$pTbTPuHc1GxdHbUFszq4aOUk6XVyIQLxJZsjWdR4eSZvRt0oaSI.W', NULL, NULL, NULL, 1, '2025-10-01 18:39:37', '2025-10-05 16:59:29', NULL),
(23, 4, 0, 'qqqq', 'pierre', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 20:27:39', '2025-10-05 20:27:39', NULL),
(24, 4, 0, 'swat', 'momo', '2002-04-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 20:28:23', '2025-10-05 20:49:30', NULL),
(25, 4, 0, 'derwan', 'sam', '1987-08-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 20:35:03', '2025-10-05 20:35:03', NULL),
(26, 4, 0, 'dsza', 'oscar', '2012-12-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:35:43', '2025-10-05 23:35:43', NULL),
(27, 4, 0, 'swat', 'sam', '2000-04-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:54:33', '2025-10-11 11:28:36', 36),
(28, 4, 0, 'DESCHAMPS', 'Didier', '1982-06-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:55:14', '2025-10-11 11:53:56', 39),
(29, 4, 0, 'STEPHAN', 'Guy', '1960-02-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:55:55', '2025-10-11 11:57:18', 41),
(30, 4, 0, 'poulin', 'oscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:56:23', '2025-10-05 23:56:23', NULL),
(31, 4, 0, 'trico', 'seb', '2014-05-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-05 23:56:59', '2025-10-05 23:56:59', NULL),
(33, 4, 0, 'arb', 'sam', NULL, NULL, NULL, 'aubondeal18@gmail.com', NULL, '457aa11864a5bb823bc0528f4c02d81683dcd41d54819018a98e27a796ea11fa', '2025-10-07 22:34:13', 0, 1, '2025-10-06 00:34:13', '2025-10-06 00:34:13', NULL),
(34, 1, 0, 'qqqq', 'pierre', NULL, NULL, NULL, 'azert18@gmail.com', NULL, '29a26a23018c569cf945c08aa351374b90532f5bdc9565943dba8c08bed00ce5', '2025-10-07 22:48:38', 0, 1, '2025-10-06 00:48:38', '2025-10-06 00:48:38', NULL),
(35, 5, NULL, 'nuts', 'choco', '2000-10-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-07 12:23:24', '2025-10-07 12:23:24', NULL),
(36, 5, NULL, 'test', 'sam', '1978-08-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-10 19:02:13', '2025-10-10 19:02:13', NULL),
(37, 5, NULL, 'dresa', 'oscar', '1996-12-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-10 19:02:50', '2025-10-10 19:02:50', NULL),
(38, 4, 0, 'Moine', 'cyril', '1985-12-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-10 21:07:14', '2025-10-11 12:47:10', 42),
(39, 4, 0, 'OLMETA', 'Pascal', '1978-08-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-10 21:07:51', '2025-10-10 21:07:51', NULL),
(40, 5, NULL, 'Maignan', 'Mike', '2000-03-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-10 21:09:26', '2025-10-10 21:09:26', NULL),
(42, 1, 0, 'alo', 'samuel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 15:40:20', '2025-10-13 15:40:20', 35),
(43, 1, 0, 'alo', 'samuel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 15:45:26', '2025-10-13 15:45:26', 35),
(47, 1, 0, 'ROUMADNI', 'Khalil', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 18:45:47', '2025-10-13 18:45:47', 41),
(48, 1, 0, 'ROUMADNI', 'Khalil', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 18:50:31', '2025-10-13 18:50:31', NULL),
(49, 1, 0, 'ROUMADNI', 'Khalil', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:23:09', '2025-10-13 19:23:09', 31),
(50, 1, 0, 'GRIMOULT', 'Patrice', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:35:32', '2025-10-13 19:59:25', 8),
(51, 1, 0, 'BERRIA', 'Kevin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:44:17', '2025-10-13 19:59:04', 8),
(52, 1, 0, 'BOUCHOU', 'Amine', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:44:40', '2025-10-13 19:59:16', 8),
(53, 1, 0, 'BARBOSA', 'David', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:44:59', '2025-10-13 19:48:34', 8),
(54, 1, 0, 'YOUSSFI', 'Mohcine', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-13 19:45:44', '2025-10-13 20:34:00', 42),
(55, 4, 0, 'toto', 'david', '2000-12-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-14 20:57:26', '2025-10-14 20:58:56', 41),
(56, 4, 0, 'tamtam', 'ali', '1998-08-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-14 21:41:56', '2025-10-14 21:42:21', 42),
(57, 4, 0, 'zerff', 'ze', '2010-10-10', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-14 21:59:39', '2025-10-14 21:59:39', NULL),
(58, 4, 0, 'Melloula', 'Walid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-14 22:58:12', '2025-10-14 22:58:12', 25),
(59, 2, 0, 'Barbosa', 'david', NULL, NULL, NULL, 'dbarbosa@monin.com', NULL, '7044775539d0309b03713ce2a535deb46fd66e56698235d6662a77a0d61ea887', '2025-10-20 16:18:20', 0, 1, '2025-10-18 16:18:20', '2025-10-18 16:18:20', NULL),
(60, 5, NULL, 'test', 'pep', '2000-08-15', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-21 19:59:30', '2025-10-21 19:59:30', NULL),
(61, 5, NULL, 'lala', 'momo', '2012-12-12', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-21 19:59:56', '2025-10-21 19:59:56', NULL),
(62, 5, NULL, 'dro', 'oscar', '2010-02-14', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-21 20:00:24', '2025-10-21 20:00:24', NULL),
(63, 5, NULL, 'nuts', 'pierre', '2008-03-28', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-10-21 20:00:59', '2025-10-21 20:00:59', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users_club_functions`
--

CREATE TABLE `users_club_functions` (
  `id_user_club_function` int NOT NULL,
  `id_club_function` int NOT NULL,
  `id_user` int NOT NULL,
  `id_season` int NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `jersey_number` int DEFAULT NULL,
  `id_team` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users_club_functions`
--

INSERT INTO `users_club_functions` (`id_user_club_function`, `id_club_function`, `id_user`, `id_season`, `position`, `jersey_number`, `id_team`, `start_date`, `end_date`) VALUES
(17, 4, 28, 1, NULL, NULL, 2, '2025-10-05', NULL),
(18, 5, 29, 1, NULL, NULL, 2, '2025-10-05', NULL),
(19, 17, 30, 1, NULL, NULL, NULL, '2025-10-05', NULL),
(20, 16, 31, 1, NULL, NULL, NULL, '2025-10-05', NULL),
(21, 1, 35, 1, 'Défenseur', NULL, 4, '2025-10-07', NULL),
(22, 1, 36, 1, 'Attaquant', 9, 2, '2025-10-10', NULL),
(23, 1, 37, 1, 'Milieu', 6, 2, '2025-10-10', NULL),
(24, 6, 38, 1, NULL, NULL, 2, '2025-10-10', NULL),
(25, 7, 39, 1, NULL, NULL, 2, '2025-10-10', NULL),
(26, 1, 40, 1, 'Gardien', 1, 2, '2025-10-10', NULL),
(31, 8, 49, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(32, 8, 50, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(33, 8, 51, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(34, 8, 52, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(35, 8, 53, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(36, 8, 54, 1, NULL, NULL, NULL, '2025-10-13', NULL),
(37, 4, 55, 1, NULL, NULL, 8, '2025-10-14', NULL),
(38, 3, 56, 1, NULL, NULL, NULL, '2025-10-14', NULL),
(39, 5, 57, 1, NULL, NULL, 8, '2025-10-14', NULL),
(40, 4, 58, 1, NULL, NULL, 5, '2025-10-14', NULL),
(41, 1, 60, 1, '', NULL, 6, '2025-10-21', NULL),
(42, 1, 61, 1, '', NULL, 39, '2025-10-21', NULL),
(43, 1, 62, 1, 'Défenseur', NULL, 37, '2025-10-21', NULL),
(44, 1, 63, 1, 'Milieu', NULL, 34, '2025-10-21', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `visites`
--

CREATE TABLE `visites` (
  `id_visite` int NOT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_agent` text,
  `page_url` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `date_visite` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `visites`
--

INSERT INTO `visites` (`id_visite`, `ip_address`, `user_agent`, `page_url`, `referer`, `browser`, `os`, `date_visite`) VALUES
(8, '192.168.1.100', NULL, '/index.php', NULL, 'Chrome', 'Windows', '2025-10-01 10:30:00'),
(9, '192.168.1.101', NULL, '/actualites.php', NULL, 'Safari', 'MacOS', '2025-10-05 14:20:00'),
(10, '192.168.1.102', NULL, '/equipe.php', NULL, 'Firefox', 'Linux', '2025-10-06 09:15:00'),
(11, '192.168.1.103', NULL, '/contact.php', NULL, 'Chrome', 'Android', '2025-09-15 11:00:00'),
(12, '192.168.1.104', NULL, '/matchs.php', NULL, 'Edge', 'Windows', '2025-09-20 16:45:00'),
(13, '192.168.1.105', NULL, '/index.php', NULL, 'Safari', 'iOS', '2025-08-10 08:30:00'),
(14, '192.168.1.106', NULL, '/galerie.php', NULL, 'Chrome', 'Windows', '2025-07-25 19:00:00'),
(15, '192.168.1.107', NULL, '/index.php', NULL, 'Firefox', 'Windows', '2025-06-12 12:30:00'),
(16, '192.168.1.108', NULL, '/actualites.php', NULL, 'Chrome', 'MacOS', '2025-05-05 15:20:00'),
(17, '192.168.1.100', NULL, '/index.php', NULL, 'Chrome', 'Windows', '2025-10-01 10:30:00'),
(18, '192.168.1.101', NULL, '/actualites.php', NULL, 'Safari', 'MacOS', '2025-10-05 14:20:00'),
(19, '192.168.1.102', NULL, '/equipe.php', NULL, 'Firefox', 'Linux', '2025-10-06 09:15:00'),
(20, '192.168.1.103', NULL, '/contact.php', NULL, 'Edge', 'Windows', '2025-10-06 11:45:00'),
(21, '192.168.1.104', NULL, '/matchs.php', NULL, 'Chrome', 'Android', '2025-10-06 16:00:00'),
(22, '192.168.1.105', NULL, '/index.php', NULL, 'Chrome', 'Windows', '2025-09-15 11:00:00'),
(23, '192.168.1.106', NULL, '/actualites.php', NULL, 'Safari', 'iOS', '2025-09-18 14:30:00'),
(24, '192.168.1.107', NULL, '/equipe.php', NULL, 'Firefox', 'Linux', '2025-09-20 16:45:00'),
(25, '192.168.1.108', NULL, '/galerie.php', NULL, 'Edge', 'Windows', '2025-09-25 10:15:00'),
(26, '192.168.1.109', NULL, '/index.php', NULL, 'Chrome', 'MacOS', '2025-08-10 08:30:00'),
(27, '192.168.1.110', NULL, '/contact.php', NULL, 'Safari', 'iOS', '2025-08-12 12:00:00'),
(28, '192.168.1.111', NULL, '/matchs.php', NULL, 'Firefox', 'Windows', '2025-08-20 19:30:00'),
(29, '192.168.1.112', NULL, '/index.php', NULL, 'Chrome', 'Windows', '2025-07-05 09:00:00'),
(30, '192.168.1.113', NULL, '/actualites.php', NULL, 'Edge', 'Windows', '2025-07-15 14:00:00'),
(31, '192.168.1.114', NULL, '/equipe.php', NULL, 'Safari', 'MacOS', '2025-07-25 17:30:00'),
(32, '192.168.1.115', NULL, '/index.php', NULL, 'Chrome', 'Android', '2025-06-08 10:45:00'),
(33, '192.168.1.116', NULL, '/contact.php', NULL, 'Firefox', 'Linux', '2025-06-18 13:20:00'),
(34, '192.168.1.117', NULL, '/index.php', NULL, 'Safari', 'iOS', '2025-05-05 15:20:00'),
(35, '192.168.1.118', NULL, '/matchs.php', NULL, 'Chrome', 'Windows', '2025-05-12 11:30:00'),
(36, '192.168.1.119', NULL, '/galerie.php', NULL, 'Edge', 'Windows', '2025-05-20 16:00:00'),
(37, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/partenaires', NULL, 'Chrome', 'Windows', '2025-10-08 22:32:25'),
(38, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/pages/accueil.php', 'https://localhost/es_moulon/pages/', 'Chrome', 'Windows', '2025-10-09 13:11:10'),
(39, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/', 'https://localhost/es_moulon/', 'Chrome', 'Windows', '2025-10-09 13:14:42'),
(40, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/', 'https://localhost/es_moulon/', 'Chrome', 'Windows', '2025-10-09 13:15:18'),
(41, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/Le_club/infos_pratiques', 'https://localhost/es_moulon/public/', 'Chrome', 'Windows', '2025-10-15 09:43:19'),
(42, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/', NULL, 'Chrome', 'Windows', '2025-10-16 14:38:33'),
(43, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/Le_club/nos_arbitres', 'https://localhost/es_moulon/public/Le_club/nos_benevols', 'Chrome', 'Windows', '2025-10-17 09:20:35'),
(44, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/Le_club/infos_pratiques', NULL, 'Chrome', 'Windows', '2025-10-21 10:11:35'),
(45, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/Nos_equipes/pole_pre_formation', 'https://localhost/es_moulon/public/Nos_equipes/ecole_de_foot', 'Chrome', 'Windows', '2025-10-24 13:18:51'),
(46, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '/es_moulon/public/', 'https://localhost/es_moulon/', 'Chrome', 'Windows', '2025-10-27 21:54:02');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_category`);

--
-- Index pour la table `categories_medias`
--
ALTER TABLE `categories_medias`
  ADD PRIMARY KEY (`id_category_media`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `club_functions`
--
ALTER TABLE `club_functions`
  ADD PRIMARY KEY (`id_club_function`);

--
-- Index pour la table `club_info`
--
ALTER TABLE `club_info`
  ADD PRIMARY KEY (`id_club_info`);

--
-- Index pour la table `club_structure`
--
ALTER TABLE `club_structure`
  ADD PRIMARY KEY (`id_structure`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_club_function` (`id_club_function`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `fk_contacs_users` (`id_user`),
  ADD KEY `id_media` (`id_media`);

--
-- Index pour la table `convocations`
--
ALTER TABLE `convocations`
  ADD PRIMARY KEY (`id_convocation`),
  ADD KEY `id_team` (`id_team`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `convocation_players`
--
ALTER TABLE `convocation_players`
  ADD PRIMARY KEY (`id_convocation_player`),
  ADD KEY `fk_convocation_players_convocation` (`id_convocation`),
  ADD KEY `fk_convocation_players_user` (`id_player`);

--
-- Index pour la table `home_blocks`
--
ALTER TABLE `home_blocks`
  ADD PRIMARY KEY (`id_home_block`),
  ADD KEY `fk_home_blocks_medias` (`id_media`),
  ADD KEY `fk_home_blocks_users` (`id_user`);

--
-- Index pour la table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id_match`),
  ADD KEY `fk_matches_teamshome` (`id_home_team`),
  ADD KEY `fk_matches_teamsaway` (`id_away_team`),
  ADD KEY `fk_matches_medias` (`id_media`),
  ADD KEY `fk_matches_users` (`id_user`),
  ADD KEY `fk_matches_seasons` (`id_season`);

--
-- Index pour la table `medias`
--
ALTER TABLE `medias`
  ADD PRIMARY KEY (`id_media`),
  ADD KEY `fk_medias_users` (`id_user`),
  ADD KEY `idx_category_media` (`id_category_media`);

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id_new`),
  ADD KEY `fk_news_users` (`id_user`),
  ADD KEY `fk_news_medias` (`id_media`);

--
-- Index pour la table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id_partner`),
  ADD KEY `fk_partners_users` (`id_user`),
  ADD KEY `fk_partners_medias` (`id_media`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id_season`);

--
-- Index pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id_site_setting`),
  ADD KEY `fk_site_settings_medias` (`id_hero_media`);

--
-- Index pour la table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id_team`),
  ADD KEY `fk_teams_medias` (`id_media`),
  ADD KEY `fk_teams_categories` (`id_category`),
  ADD KEY `fk_team_logo` (`id_team_logo`);

--
-- Index pour la table `teams_seasons`
--
ALTER TABLE `teams_seasons`
  ADD PRIMARY KEY (`id_team_season`),
  ADD KEY `fk_teamsseasons_teams` (`id_team`),
  ADD KEY `fk_teamsseasons_seasons` (`id_season`);

--
-- Index pour la table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id_training`),
  ADD KEY `id_team` (`id_team`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_medias` (`id_media`),
  ADD KEY `fk_users_roles` (`id_role`);

--
-- Index pour la table `users_club_functions`
--
ALTER TABLE `users_club_functions`
  ADD PRIMARY KEY (`id_user_club_function`),
  ADD KEY `fk_usersclubfunctions_users` (`id_user`),
  ADD KEY `fk_usersclubfunctions_clubfunctions` (`id_club_function`),
  ADD KEY `fk_userclubfunctions_seasons` (`id_season`),
  ADD KEY `fk_userclubfunctions_teams` (`id_team`);

--
-- Index pour la table `visites`
--
ALTER TABLE `visites`
  ADD PRIMARY KEY (`id_visite`),
  ADD KEY `idx_date` (`date_visite`),
  ADD KEY `idx_ip` (`ip_address`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_category` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `categories_medias`
--
ALTER TABLE `categories_medias`
  MODIFY `id_category_media` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `club_functions`
--
ALTER TABLE `club_functions`
  MODIFY `id_club_function` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `club_info`
--
ALTER TABLE `club_info`
  MODIFY `id_club_info` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `club_structure`
--
ALTER TABLE `club_structure`
  MODIFY `id_structure` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id_contact` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `convocations`
--
ALTER TABLE `convocations`
  MODIFY `id_convocation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `convocation_players`
--
ALTER TABLE `convocation_players`
  MODIFY `id_convocation_player` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `home_blocks`
--
ALTER TABLE `home_blocks`
  MODIFY `id_home_block` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `matches`
--
ALTER TABLE `matches`
  MODIFY `id_match` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT pour la table `medias`
--
ALTER TABLE `medias`
  MODIFY `id_media` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id_new` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `partners`
--
ALTER TABLE `partners`
  MODIFY `id_partner` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id_season` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id_site_setting` int NOT NULL AUTO_INCREMENT COMMENT 'Clé primaire (toujours = 1)', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `teams`
--
ALTER TABLE `teams`
  MODIFY `id_team` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `teams_seasons`
--
ALTER TABLE `teams_seasons`
  MODIFY `id_team_season` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id_training` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT pour la table `users_club_functions`
--
ALTER TABLE `users_club_functions`
  MODIFY `id_user_club_function` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `visites`
--
ALTER TABLE `visites`
  MODIFY `id_visite` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `club_structure`
--
ALTER TABLE `club_structure`
  ADD CONSTRAINT `club_structure_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `club_structure_ibfk_2` FOREIGN KEY (`id_club_function`) REFERENCES `club_functions` (`id_club_function`) ON DELETE SET NULL,
  ADD CONSTRAINT `club_structure_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `club_structure` (`id_structure`) ON DELETE SET NULL,
  ADD CONSTRAINT `club_structure_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `club_structure_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `fk_contacs_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `convocations`
--
ALTER TABLE `convocations`
  ADD CONSTRAINT `convocations_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`),
  ADD CONSTRAINT `convocations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `convocation_players`
--
ALTER TABLE `convocation_players`
  ADD CONSTRAINT `fk_convocation_players_convocation` FOREIGN KEY (`id_convocation`) REFERENCES `convocations` (`id_convocation`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_convocation_players_user` FOREIGN KEY (`id_player`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `home_blocks`
--
ALTER TABLE `home_blocks`
  ADD CONSTRAINT `fk_home_blocks_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_home_blocks_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_matches_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_matches_seasons` FOREIGN KEY (`id_season`) REFERENCES `seasons` (`id_season`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_matches_teamsaway` FOREIGN KEY (`id_away_team`) REFERENCES `teams` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_matches_teamshome` FOREIGN KEY (`id_home_team`) REFERENCES `teams` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_matches_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `medias`
--
ALTER TABLE `medias`
  ADD CONSTRAINT `fk_medias_category_media` FOREIGN KEY (`id_category_media`) REFERENCES `categories_medias` (`id_category_media`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medias_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_news_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_news_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `partners`
--
ALTER TABLE `partners`
  ADD CONSTRAINT `fk_partners_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partners_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `fk_site_settings_medias` FOREIGN KEY (`id_hero_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_team_logo` FOREIGN KEY (`id_team_logo`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teams_categories` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id_category`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teams_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `teams_seasons`
--
ALTER TABLE `teams_seasons`
  ADD CONSTRAINT `fk_teamsseasons_seasons` FOREIGN KEY (`id_season`) REFERENCES `seasons` (`id_season`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teamsseasons_teams` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `trainings_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`),
  ADD CONSTRAINT `trainings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_medias` FOREIGN KEY (`id_media`) REFERENCES `medias` (`id_media`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `users_club_functions`
--
ALTER TABLE `users_club_functions`
  ADD CONSTRAINT `fk_userclubfunctions_seasons` FOREIGN KEY (`id_season`) REFERENCES `seasons` (`id_season`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_userclubfunctions_teams` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usersclubfunctions_clubfunctions` FOREIGN KEY (`id_club_function`) REFERENCES `club_functions` (`id_club_function`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usersclubfunctions_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
