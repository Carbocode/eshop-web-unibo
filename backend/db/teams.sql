-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 15, 2025 alle 18:00
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

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

-- --------------------------------------------------------

--
-- Struttura della tabella `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) NOT NULL DEFAULT 'https://www.gravatar.com/avatar/',
  `country_id` int(11) NOT NULL,
  `league_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `teams`
--

INSERT INTO `teams` (`team_id`, `name`, `logo`, `country_id`, `league_id`) VALUES
(1, 'Napoli', 'https://cdn.footystats.org/img/teams/italy-ssc-napoli.png', 1, 1),
(2, 'Inter', 'https://cdn.footystats.org/img/teams/italy-fc-internazionale-milano.png', 1, 1),
(3, 'Milan', 'https://cdn.footystats.org/img/teams/italy-ac-milan.png', 1, 1),
(6, 'Albania', 'https://cdn.footystats.org/img/teams/albania-albania-national-team.png', 20, NULL),
(7, 'Argentina', 'https://cdn.footystats.org/img/teams/argentina-argentina-national-team.png', 2, NULL),
(8, 'Italy', 'https://cdn.footystats.org/img/teams/italy-italy-national-team.png', 1, NULL),
(9, 'Morocco', 'https://cdn.footystats.org/img/teams/morocco-morocco-national-team.png', 3, NULL),
(10, 'Brazil', 'https://cdn.footystats.org/img/teams/brazil-brazil-national-team.png', 12, NULL),
(11, 'Saudi Arabia', 'https://cdn.footystats.org/img/teams/saudi-arabia-saudi-arabia-national-team.png', 8, NULL),
(12, 'Ukraine', 'https://cdn.footystats.org/img/teams/ukraine-ukraine-national-team.png', 22, NULL),
(13, 'Uruguay', 'https://cdn.footystats.org/img/teams/uruguay-uruguay-national-team.png', 14, NULL),
(14, 'Colombia', 'https://cdn.footystats.org/img/teams/colombia-colombia-national-team.png', 38, NULL),
(15, 'Japan', 'https://cdn.footystats.org/img/teams/japan-japan-national-team.png', 17, NULL),
(16, 'Netherlands', 'https://cdn.footystats.org/img/teams/netherlands-netherlands-national-team.png', 6, NULL),
(17, 'USA', 'https://cdn.footystats.org/img/teams/usa-united-states-mens-national-team.png', 10, NULL),
(18, 'Algeria', 'https://cdn.footystats.org/img/teams/algeria-algeria-national-team.png', 26, NULL),
(19, 'Germany', 'https://cdn.footystats.org/img/teams/germany-germany-national-team.png', 7, NULL),
(20, 'Greece', 'https://cdn.footystats.org/img/teams/greece-greece-national-team.png', 19, NULL),
(21, 'Iceland', 'https://cdn.footystats.org/img/teams/iceland-iceland-national-team.png', 36, NULL),
(22, 'Egypt', 'https://cdn.footystats.org/img/teams/egypt-egypt-national-team.png', 9, NULL),
(23, 'Portugal', 'https://cdn.footystats.org/img/teams/portugal-portugal-national-team.png', 13, NULL),
(24, 'Senegal', 'https://cdn.footystats.org/img/teams/senegal-senegal-national-team.png', 23, NULL),
(25, 'Spain', 'https://cdn.footystats.org/img/teams/spain-spain-national-team.png', 5, NULL),
(26, 'France', 'https://cdn.footystats.org/img/teams/france-france-national-team.png', 4, NULL),
(27, 'Mexico', 'https://cdn.footystats.org/img/teams/mexico-mexico-national-team.png', 11, NULL),
(28, 'Poland', 'https://cdn.footystats.org/img/teams/poland-poland-national-team.png', 16, NULL),
(29, 'Tunisia', 'https://cdn.footystats.org/img/teams/tunisia-tunisia-national-team.png', 28, NULL),
(30, 'England', 'https://cdn.footystats.org/img/teams/england-england-national-team.png', 29, NULL),
(31, 'Ireland', 'https://cdn.footystats.org/img/teams/republic-of-ireland-republic-of-ireland-national-team.png', 32, NULL),
(32, 'Scotland', 'https://cdn.footystats.org/img/teams/scotland-scotland-national-team.png', 31, NULL),
(33, 'Wales', 'https://cdn.footystats.org/img/teams/wales-wales-national-team.png', 30, NULL),
(34, 'Croatia', 'https://cdn.footystats.org/img/teams/croatia-croatia-national-team.png', 21, NULL),
(35, 'South Africa', 'https://cdn.footystats.org/img/teams/south-africa-south-africa-national-team.png', 27, NULL),
(36, 'South Korea', 'https://cdn.footystats.org/img/teams/south-korea-south-korea-national-team.png', 18, NULL),
(37, 'Switzerland', 'https://cdn.footystats.org/img/teams/switzerland-switzerland-national-team.png', 34, NULL),
(38, 'Roma', 'https://cdn.footystats.org/img/teams/italy-as-roma.png', 1, 1),
(39, 'Juventus', 'https://cdn.footystats.org/img/teams/italy-juventus-fc.png', 1, 1),
(40, 'Borussia Dortmund', 'https://cdn.footystats.org/img/teams/germany-bvb-09-borussia-dortmund.png', 7, 3),
(41, 'Bayern Monaco', 'https://cdn.footystats.org/img/teams/germany-fc-bayern-munchen.png', 7, 3),
(42, 'Bayern Leverkusen', 'https://cdn.footystats.org/img/teams/germany-bayer-04-leverkusen.png', 7, 3),
(43, 'Liepzig', 'https://cdn.footystats.org/img/teams/germany-rasen-ballsport-leipzig.png', 7, 3),
(44, 'Eintracht Frankfurt', 'https://cdn.footystats.org/img/teams/germany-eintracht-frankfurt.png', 7, 3),
(45, 'Real Madrid', 'https://cdn.footystats.org/img/teams/spain-real-madrid-cf.png', 5, 4),
(46, 'Barcelona', 'https://cdn.footystats.org/img/teams/spain-fc-barcelona.png', 5, 4),
(47, 'Atletico Madrid', 'https://cdn.footystats.org/img/teams/spain-club-atletico-de-madrid.png', 5, 4),
(48, 'Real Betis', 'https://cdn.footystats.org/img/teams/spain-real-betis-balompie.png', 5, 4),
(49, 'Valencia', 'https://cdn.footystats.org/img/teams/spain-valencia-cf.png', 5, 4),
(50, 'Paris Saint Germain', 'https://cdn.footystats.org/img/teams/france-paris-saint-germain-fc.png', 4, 5),
(51, 'Olympique Marseille', 'https://cdn.footystats.org/img/teams/france-olympique-de-marseille.png', 4, 5),
(52, 'Olympique Lyon', 'https://cdn.footystats.org/img/teams/france-olympique-lyonnais.png', 4, 5),
(53, 'Nice', 'https://cdn.footystats.org/img/teams/france-ogc-nice-cote-dazur.png', 4, 5),
(54, 'Monaco', 'https://cdn.footystats.org/img/teams/monaco-as-monaco-fc.png', 4, 5),
(55, 'Liverpool', 'https://cdn.footystats.org/img/teams/england-liverpool-fc.png', 29, 2),
(56, 'Arsenal', 'https://cdn.footystats.org/img/teams/england-arsenal-fc.png', 29, 2),
(57, 'Manchester City', 'https://cdn.footystats.org/img/teams/england-manchester-city-fc.png', 29, 2),
(58, 'Manchester United', 'https://cdn.footystats.org/img/teams/england-manchester-united-fc.png', 29, 2),
(59, 'Chelsea', 'https://cdn.footystats.org/img/teams/england-chelsea-fc.png', 29, 2);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `country` (`country_id`),
  ADD KEY `id_league` (`league_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
