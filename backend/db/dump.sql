-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 11, 2025 at 05:45 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elprimerofootballer`
--

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`country_id`, `name`, `flag`) VALUES
(1, 'Italy', 'https://flagpedia.net/data/flags/w1160/it.webp'),
(2, 'Argentina', 'https://flagpedia.net/data/flags/w1160/ar.webp'),
(3, 'Morocco', 'https://flagpedia.net/data/flags/w1160/ma.webp'),
(4, 'France', 'https://flagpedia.net/data/flags/w1160/fr.webp'),
(5, 'Spain', 'https://flagpedia.net/data/flags/w1160/es.webp'),
(6, 'Netherlands', 'https://flagpedia.net/data/flags/w1160/nl.webp'),
(7, 'Germany', 'https://flagpedia.net/data/flags/w1160/de.webp'),
(8, 'Saudi Arabia', 'https://flagpedia.net/data/flags/w1160/sa.webp'),
(9, 'Egypt', 'https://flagpedia.net/data/flags/w1160/eg.webp'),
(10, 'USA', 'https://flagpedia.net/data/flags/w1160/us.webp'),
(11, 'Mexico', 'https://flagpedia.net/data/flags/w1160/mx.webp'),
(12, 'Brazil', 'https://flagpedia.net/data/flags/w1160/br.webp'),
(13, 'Portugal', 'https://flagpedia.net/data/flags/w1160/pt.webp'),
(14, 'Uruguay', 'https://flagpedia.net/data/flags/w1160/uy.webp'),
(15, 'Belgium', 'https://flagpedia.net/data/flags/w1160/be.webp'),
(16, 'Poland', 'https://flagpedia.net/data/flags/w1160/pl.webp'),
(17, 'Japan', 'https://flagpedia.net/data/flags/w1160/jp.webp'),
(18, 'South Korea', 'https://flagpedia.net/data/flags/w1160/kr.webp'),
(19, 'Greece', 'https://flagpedia.net/data/flags/w1160/gr.webp'),
(20, 'Albania', 'https://flagpedia.net/data/flags/w1160/al.webp'),
(21, 'Croatia', 'https://flagpedia.net/data/flags/w1160/hr.webp'),
(22, 'Ukraine', 'https://flagpedia.net/data/flags/w1160/ua.webp'),
(23, 'Senegal', 'https://flagpedia.net/data/flags/w1160/sn.webp'),
(24, 'Nigeria', 'https://flagpedia.net/data/flags/w1160/ng.webp'),
(25, 'Gabon', 'https://flagpedia.net/data/flags/w1160/ga.webp'),
(26, 'Algeria', 'https://flagpedia.net/data/flags/w1160/dz.webp'),
(27, 'South Africa', 'https://flagpedia.net/data/flags/w1160/za.webp'),
(28, 'Tunisia', 'https://flagpedia.net/data/flags/w1160/tn.webp'),
(29, 'England', 'https://flagpedia.net/data/flags/w1160/gb-eng.webp'),
(30, 'Wales', 'https://flagpedia.net/data/flags/w1160/gb-wls.webp'),
(31, 'Scotland', 'https://flagpedia.net/data/flags/w1160/gb-sct.webp'),
(32, 'Ireland', 'https://flagpedia.net/data/flags/w1160/ie.webp'),
(33, 'Denmark', 'https://flagpedia.net/data/flags/w1160/ie.webp'),
(34, 'Switzerland', 'https://flagpedia.net/data/flags/w1160/ch.webp'),
(35, 'Sweden', 'https://flagpedia.net/data/flags/w1160/se.webp'),
(36, 'Iceland', 'https://flagpedia.net/data/flags/w1160/is.webp'),
(37, 'Canada', 'https://flagpedia.net/data/flags/w1160/ca.webp'),
(38, 'Colombia', 'https://flagpedia.net/data/flags/w1160/co.webp');

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `email`, `password`, `full_name`, `phone`, `admin`, `address`, `city`, `province`, `zip`, `country`) VALUES
(1, 'john.doe@example.com', '$2y$10$NGZjdsr8XtTs5MDxuh5/VuKySzof6IQ2e8YLZUMrcNJ1rIc7N7oua', 'John', '+1234567890', 0, '', '', '', '', ''),
(2, 'jane.smith@example.com', '$2y$10$NGZjdsr8XtTs5MDxuh5/VuKySzof6IQ2e8YLZUMrcNJ1rIc7N7oua', 'Jane', '+0987654321', 0, '', '', '', '', '');

--
-- Dumping data for table `editions`
--

INSERT INTO `editions` (`edition_id`, `name`, `year`, `description`) VALUES
(1, 'Home 2024/25', 2024, 'Home kit for 2024/25 season'),
(2, 'Away 2024/25', 2024, 'Away kit for 2024/25 season'),
(3, 'Third 2024/25', 2024, 'Third kit for 2024/25 season'),
(4, 'Vintage 1987', 1987, 'Replica of the historic 1987 kit'),
(5, 'Special Edition', 2024, 'Limited Champions League Edition');

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `name`) VALUES
(1, 'A'),
(2, 'B'),
(3, 'C'),
(4, 'D'),
(5, 'E'),
(6, 'F'),
(7, 'G'),
(8, 'H');

--
-- Dumping data for table `groups_nations`
--

INSERT INTO `groups_nations` (`group_id`, `country_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 20),
(2, 8),
(2, 12),
(2, 14),
(2, 22),
(3, 6),
(3, 10),
(3, 17),
(3, 38),
(4, 7),
(4, 19),
(4, 26),
(4, 36),
(5, 5),
(5, 9),
(5, 13),
(5, 23),
(6, 4),
(6, 11),
(6, 16),
(6, 28),
(7, 29),
(7, 30),
(7, 31),
(7, 32),
(8, 18),
(8, 21),
(8, 27),
(8, 34);

--
-- Dumping data for table `leagues`
--

INSERT INTO `leagues` (`league_id`, `name`, `logo`) VALUES
(1, 'Serie A', 'https://cdn.footystats.org/img/competitions/italy-serie-a.png'),
(2, 'Premier League', 'https://cdn.footystats.org/img/competitions/england-premier-league.png'),
(3, 'Bundesliga', 'https://cdn.footystats.org/img/competitions/germany-bundesliga.png'),
(4, 'La Liga', 'https://cdn.footystats.org/img/competitions/spain-la-liga.png'),
(5, 'Ligue 1', 'https://cdn.footystats.org/img/competitions/france-ligue-1.png');

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`status_id`, `status`, `icon`) VALUES
(1, 'Pagato', 'fa-circle-check'),
(2, 'In Lavorazione', 'fa-warehouse'),
(3, 'Spedito', 'fa-truck'),
(4, 'Consegnato', 'fa-house');

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`size_id`, `name`) VALUES
(1, 'XS'),
(2, 'S'),
(3, 'M'),
(4, 'L'),
(5, 'XL'),
(6, 'XXL');

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `name`, `logo`, `country_id`, `league_id`) VALUES
(1, 'Napoli', 'https://example.com/napoli.jpg', 1, 1),
(2, 'Inter', 'https://example.com/inter.jpg', 1, 1),
(3, 'Milan', 'https://example.com/milan.jpg', 1, 1),
(6, 'Albania', 'https://www.gravatar.com/avatar/', 20, NULL),
(7, 'Argentina', 'https://www.gravatar.com/avatar/', 2, NULL),
(8, 'Italy', 'https://www.gravatar.com/avatar/', 1, NULL),
(9, 'Morocco', 'https://www.gravatar.com/avatar/', 3, NULL),
(10, 'Brazil', 'https://www.gravatar.com/avatar/', 12, NULL),
(11, 'Saudi Arabia', 'https://www.gravatar.com/avatar/', 8, NULL),
(12, 'Ukraine', 'https://www.gravatar.com/avatar/', 22, NULL),
(13, 'Uruguay', 'https://www.gravatar.com/avatar/', 14, NULL),
(14, 'Colombia', 'https://www.gravatar.com/avatar/', 38, NULL),
(15, 'Japan', 'https://www.gravatar.com/avatar/', 17, NULL),
(16, 'Netherlands', 'https://www.gravatar.com/avatar/', 6, NULL),
(17, 'USA', 'https://www.gravatar.com/avatar/', 10, NULL),
(18, 'Algeria', 'https://www.gravatar.com/avatar/', 26, NULL),
(19, 'Germany', 'https://www.gravatar.com/avatar/', 7, NULL),
(20, 'Greece', 'https://www.gravatar.com/avatar/', 19, NULL),
(21, 'Iceland', 'https://www.gravatar.com/avatar/', 36, NULL),
(22, 'Egypt', 'https://www.gravatar.com/avatar/', 9, NULL),
(23, 'Portugal', 'https://www.gravatar.com/avatar/', 13, NULL),
(24, 'Senegal', 'https://www.gravatar.com/avatar/', 23, NULL),
(25, 'Spain', 'https://www.gravatar.com/avatar/', 5, NULL),
(26, 'France', 'https://www.gravatar.com/avatar/', 4, NULL),
(27, 'Mexico', 'https://www.gravatar.com/avatar/', 11, NULL),
(28, 'Poland', 'https://www.gravatar.com/avatar/', 16, NULL),
(29, 'Tunisia', 'https://www.gravatar.com/avatar/', 28, NULL),
(30, 'England', 'https://www.gravatar.com/avatar/', 29, NULL),
(31, 'Ireland', 'https://www.gravatar.com/avatar/', 32, NULL),
(32, 'Scotland', 'https://www.gravatar.com/avatar/', 31, NULL),
(33, 'Wales', 'https://www.gravatar.com/avatar/', 30, NULL),
(34, 'Croatia', 'https://www.gravatar.com/avatar/', 21, NULL),
(35, 'South Africa', 'https://www.gravatar.com/avatar/', 27, NULL),
(36, 'South Korea', 'https://www.gravatar.com/avatar/', 18, NULL),
(37, 'Switzerland', 'https://www.gravatar.com/avatar/', 34, NULL),
(38, 'Roma', 'https://www.gravatar.com/avatar/', 1, 1),
(39, 'Juventus', 'https://www.gravatar.com/avatar/', 1, 1),
(40, 'Borussia Dortmund', 'https://www.gravatar.com/avatar/', 7, 3),
(41, 'Bayern Monaco', 'https://www.gravatar.com/avatar/', 7, 3),
(42, 'Bayern Leverkusen', 'https://www.gravatar.com/avatar/', 7, 3),
(43, 'Liepzig', 'https://www.gravatar.com/avatar/', 7, 3),
(44, 'Eintracht Frankfurt', 'https://www.gravatar.com/avatar/', 7, 3),
(45, 'Real Madrid', 'https://www.gravatar.com/avatar/', 5, 4),
(46, 'Barcelona', 'https://www.gravatar.com/avatar/', 5, 4),
(47, 'Atletico Madrid', 'https://www.gravatar.com/avatar/', 5, 4),
(48, 'Real Betis', 'https://www.gravatar.com/avatar/', 5, 4),
(49, 'Valencia', 'https://www.gravatar.com/avatar/', 5, 4),
(50, 'Paris Saint Germain', 'https://www.gravatar.com/avatar/', 4, 5),
(51, 'Olympique Marseille', 'https://www.gravatar.com/avatar/', 4, 5),
(52, 'Olympique Lyon', 'https://www.gravatar.com/avatar/', 4, 5),
(53, 'Nice', 'https://www.gravatar.com/avatar/', 4, 5),
(54, 'Monaco', 'https://www.gravatar.com/avatar/', 4, 5),
(55, 'Liverpool', 'https://www.gravatar.com/avatar/', 29, 2),
(56, 'Arsenal', 'https://www.gravatar.com/avatar/', 29, 2),
(57, 'Manchester City', 'https://www.gravatar.com/avatar/', 29, 2),
(58, 'Manchester United', 'https://www.gravatar.com/avatar/', 29, 2),
(59, 'Chelsea', 'https://www.gravatar.com/avatar/', 29, 2);

--
-- Dumping data for table `tshirts`
--

INSERT INTO `tshirts` (`tshirt_id`, `team_id`, `edition_id`, `price`, `image_url`) VALUES
(1, 1, 1, 0, 'https://example.com/napoli-home.jpg'),
(2, 1, 2, 0, 'https://example.com/napoli-away.jpg'),
(3, 2, 1, 0, 'https://example.com/inter-home.jpg'),
(4, 3, 1, 0, 'https://example.com/milan-home.jpg');

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`item_id`, `tshirt_id`, `size_id`, `availability`) VALUES
(1, 3, 3, 1),
(2, 3, 4, 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
