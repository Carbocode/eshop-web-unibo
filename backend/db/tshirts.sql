-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 15, 2025 alle 17:49
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
-- Struttura della tabella `tshirts`
--

CREATE TABLE `tshirts` (
  `tshirt_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `image_url` varchar(255) NOT NULL DEFAULT 'https://www.gravatar.com/avatar/'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tshirts`
--

INSERT INTO `tshirts` (`tshirt_id`, `team_id`, `edition_id`, `price`, `image_url`) VALUES
(1, 1, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5056/591596/Screen_Shot_2024-08-22_at_16.20.47__94222__75678.1736563456.png?c=1'),
(2, 1, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5055/591593/Screen_Shot_2024-08-22_at_16.17.22__82337__26827.1736563453.png?c=1'),
(4, 2, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4732/589409/ihf__65792__81012.1736562803.jpg?c=1'),
(5, 2, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4941/590885/24N001M0501_01__80381__14942.1736563241.jpg?c=1'),
(6, 2, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5042/591508/24N001M1907_01__22868__16386__08124.1736563427.jpg?c=1'),
(7, 3, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4455/587569/file__20413__67891.1736562222.png?c=1'),
(8, 3, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4775/589715/Untitled-2__15552__36105.1736562892.jpg?c=1'),
(9, 3, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4967/591048/1__41454__69392.1736563286.jpg?c=1'),
(10, 38, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5035/591482/Untitled-1__81699__78036__97561.1736563417.jpg?c=1'),
(11, 38, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5036/591485/file_1__57980__40355__45647.1736563419.png?c=1'),
(12, 38, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5037/591490/file_3__09649__43863__06876.1736563420.png?c=1'),
(13, 39, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4688/589129/jf__82053__56905.1736562719.jpg?c=1'),
(14, 39, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4812/589965/Screen_Shot_2024-08-01_at_10.27.28__38555__89764.1736562972.png?c=1'),
(15, 39, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4812/589965/Screen_Shot_2024-08-01_at_10.27.28__38555__89764.1736562972.png?c=1'),
(16, 56, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4435/587443/Screen_Shot_2024-05-16_at_14.36.45__87604__62362__41074.1736562179.png?c=1'),
(17, 56, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4675/589024/f__54630__60825.1736562684.jpg?c=1'),
(18, 56, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4896/590598/hom__10697__71879__62390.1736563157.jpg?c=1'),
(19, 59, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4641/588773/1__21631__95070.1736562610.jpg?c=1'),
(20, 59, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4781/589755/au__22078__04406.1736562906.jpg?c=1'),
(21, 59, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5018/591193/st__86820__69413.1736563329.jpg?c=1'),
(22, 55, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4418/587296/Screen_Shot_2024-05-16_at_14.26.18__77231__63571__04951.1736562133.png?c=1'),
(23, 55, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/640w/products/4788/589803/Screen_Shot_2024-08-02_at_8.43.57__87481__70163.1736562920.png?c=1'),
(24, 55, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4990/591215/stad__20499__58865.1736563337.jpg?c=1'),
(25, 57, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4434/587435/701230876001_pp_01_mcfc__84168__70508__59574.1736562176.jpg?c=1'),
(26, 57, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4852/590258/701230949001_pp_01_mcfc__77493__04952.1736563056.jpg?c=1'),
(27, 57, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4738/589462/Untitled-1__46836__26564.1736562820.jpg?c=1'),
(28, 58, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4544/588151/Screen_Shot_2024-07-01_at_9.26.04__37997__63600.1736562411.png?c=1'),
(29, 58, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4727/589376/united_hs__32566__77164.1736562793.jpg?c=1'),
(30, 58, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4902/590644/auth__13293__53016__82472.1736563170.jpg?c=1'),
(31, 54, 1, 89.99, 'https://www.futbolemotion.com/imagesarticulos/200518/grandes/camiseta-kappa-as-monaco-primera-equipacion-2023-2024-red-white-0.webp'),
(32, 54, 2, 89.99, 'https://cdn.footballkitarchive.com/2024/07/19/LwPej8BKx08QRTx.jpg'),
(33, 54, 3, 89.99, 'https://cdn.footballkitarchive.com/2024/08/08/PUBVA9pXALrTRmd.jpg'),
(34, 53, 1, 89.99, 'https://www.lecoqsportif.com/cdn/shop/files/2421886_2_04868340-48a7-4a57-9965-4f8acfe2021f.jpg?v=1720481335'),
(35, 53, 2, 89.99, 'https://cdn.footballkitarchive.com/2024/09/16/s5e4MIOvNwVoF6m.jpg'),
(36, 53, 3, 89.99, 'https://cdn.footballkitarchive.com/2024/09/18/nFsEKoXeX04Vngg.jpg'),
(37, 52, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/3480/581685/Screen_Shot_2023-08-02_at_10.17.39__23330__06520__44607__61060__00480.1736560327.png?c=1'),
(38, 52, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/3480/581685/Screen_Shot_2023-08-02_at_10.17.39__23330__06520__44607__61060__00480.1736560327.png?c=1'),
(40, 51, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/3663/582918/Untitled-1__33191__91902__12205__02914__49301.1736560704.jpg?c=1'),
(41, 51, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/3480/581685/Screen_Shot_2023-08-02_at_10.17.39__23330__06520__44607__61060__00480.1736560327.png?c=1'),
(43, 50, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4428/587385/Screen_Shot_2024-05-13_at_11.46.11__20371__07101__06973.1736562161.png?c=1'),
(44, 50, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4635/588737/Screen_Shot_2024-07-16_at_10.10.13__52708__18743.1736562598.png?c=1'),
(46, 47, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4711/589278/madrid__49515__69333.1736562761.jpg?c=1'),
(47, 47, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4787/589797/Screen_Shot_2024-08-01_at_10.23.34__84772__77439.1736562917.png?c=1'),
(49, 46, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4680/589065/bs__37035__29131.1736562698.jpg?c=1'),
(50, 46, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280x1280/products/5024/591408/__86796__44201.1736563396.jpg?c=1&imbypass=on'),
(51, 46, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5075/591708/t__23368__94763.1736563492.jpg?c=1'),
(52, 48, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/3368/580863/Untitled-1__13666__82616__30352__84912__64811.1736560078.jpg?c=1'),
(55, 45, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4468/587668/RMCFMZ0195-01-1__83081__47582.1736562251.jpg?c=1'),
(56, 45, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4696/589186/f__42702__44112.1736562736.jpg?c=1'),
(57, 45, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4906/590672/RMCFMZ0205-01__99862__59689__32560.1736563178.jpg?c=1'),
(58, 49, 1, 89.99, 'https://www.futbolemotion.com/imagesarticulos/201284/grandes/camiseta-castore-sevilla-fc-primera-equipacion-2023-2024-brilliant-white-true-red-0.webp'),
(60, 42, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4489/587806/2002878_Heimtrikot-ERW-Castore-2425_Front1_636732_XL__80519__72032.1736562294.jpg?c=1'),
(61, 42, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4707/589255/k__25785__60508.1736562755.jpg?c=1'),
(62, 41, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4423/587349/file_3__91907__10998__50750.1736562149.png?c=1'),
(63, 41, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4684/589103/au__14065__99483.1736562709.jpg?c=1'),
(64, 41, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4916/590731/Untitled-1__21384__36003.1736563194.jpg?c=1'),
(65, 40, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4458/587601/Screen_Shot_2024-05-22_at_11.09.10__29847__75563.1736562232.png?c=1'),
(66, 40, 2, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4992/591301/away__17165__62297.1736563365.jpg?c=1'),
(67, 40, 3, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5079/591730/o__94701__57233.1736563499.jpg?c=1'),
(68, 44, 5, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/4233/586242/Screen_Shot_2024-03-05_at_11.39.32__01794__99675__57421.1736561792.png?c=1'),
(69, 43, 1, 89.99, 'https://cdn11.bigcommerce.com/s-j1usnk9sn6/images/stencil/1280w/products/5172/592271/Untitled-1_copy-AdJU47T90-transformed__40665__81477.1736563675.png?c=1'),
(81, 6, 2, 89.99, 'https://www.macron.com/cdn-cgi/image/quality=85/media/catalog/product/cache/7854776c5a059fa4314600f243e72018/2/2/22daf6385159eb1ac93bb61afebcd6cd400051480001.jpg'),
(82, 7, 1, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1091466000&iset=0108&iindex=0007'),
(84, 7, 2, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1091464000&iset=0108&iindex=0007'),
(85, 8, 1, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1091626000&iset=0108&iindex=0007'),
(86, 8, 2, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1091624000&iset=0108&iindex=0007'),
(87, 10, 1, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1097871000&iset=0108&iindex=0007'),
(88, 10, 2, 89.99, 'https://www.imagehandler.net/preview/?istyle=0000&fmt=jpg&w=1000&h=1000&cmp=100&c=999&img=A1097869000&iset=0108&iindex=0007'),
(89, 9, 2, 89.99, 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_550,h_550/global/777706/02/fnd/EEA/fmt/png/Morocco-2025-Away-Jersey-Men'),
(90, 11, 1, 89.99, 'https://cdn.media.amplience.net/i/frasersdev/36245715_o?fmt=auto&upscale=false&w=116&h=116&sm=scaleFit&$h-ttl$'),
(91, 12, 1, 89.99, 'https://feeds.frgimages.com/ss4/https://feeds.frgimages.com/ss4/altimages/ss4/p-12013940_pv-1_u-ixs8r5cg7evup3g1ao39_v-5499d2475d684c3e888b5dfe91b4c957.jpg'),
(92, 13, 1, 89.99, 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_2000,h_2000/global/752576/01/fnd/PNA/fmt/png/Uruguay-Home-Replica-Jersey'),
(93, 14, 1, 89.99, 'https://m.media-amazon.com/images/I/51kglo5a2vL._AC_UY1000_.jpg'),
(94, 15, 1, 89.99, 'https://calcioitalia.com/media/cache/sylius_shop_product_large_thumbnail/32/9a/8f5f94b8fd1e3308c8a4e1b642b7.jpeg'),
(95, 16, 1, 89.99, 'https://cdn.media.amplience.net/i/frasersdev/37995312_o?fmt=auto&upscale=false&w=767&h=767&sm=scaleFit&$h-ttl$'),
(96, 16, 2, 89.99, 'https://calcioitalia.com/media/cache/sylius_shop_product_original/71/87/d77f24884894e103be4e8feda711.jpeg'),
(97, 17, 1, 89.99, 'https://www.officinadellosport.com/media/prodotti/varianti/DN0706/DN0706-101-PHSFH001-1000.png'),
(98, 17, 2, 89.99, 'https://www.officinadellosport.com/media/prodotti/varianti/DN0705/DN0705-454-PHSFH001-1000.png'),
(99, 18, 1, 89.99, 'https://m.media-amazon.com/images/I/51eKtDXphrL._AC_UY1000_.jpg'),
(100, 19, 1, 89.99, 'https://images.footballfanatics.com/germany-national-team/dfb-adidas-home-shirt-2024_ss5_p-200915256+u-wvrgffvpe9mwhm2mf5cs+v-kgqkjoova9oinpocxc5c.jpg?_hv=2&w=340'),
(101, 20, 1, 89.99, 'https://eposhop.gr/wp-content/uploads/2024/09/Home-Vapor-front-V2-APPROVED-CAT-1-003.png'),
(102, 21, 1, 89.99, 'https://images.footballfanatics.com/iceland-national-team/iceland-puma-home-shirt-2024-kids_ss5_p-14421184+u-0yjtsbhsgex4i0boymga+v-rmf0nkvgfwryeivtrdl8.jpg?_hv=2&w=340'),
(103, 22, 1, 89.99, 'https://www.natitrikot.ch/images/product_images/original_images/aegypten-trikot-2022-23.png'),
(104, 23, 1, 89.99, 'https://i.ebayimg.com/images/g/VsUAAOSwBwtjdZSp/s-l1200.jpg'),
(105, 25, 1, 89.99, 'https://i.ebayimg.com/images/g/CfcAAOSwn9djKqEJ/s-l1200.jpg'),
(106, 26, 1, 89.99, 'https://i.ebayimg.com/images/g/HcUAAOSwIpJjH68n/s-l1200.jpg'),
(107, 28, 1, 89.99, 'https://thumblr.uniid.it/product/345048/268f374a9c54.jpg?width=3840&format=webp&q=75'),
(108, 30, 1, 89.99, 'https://classicfootballkit.co.uk/cdn/shop/files/resize-8326-main1688128505.jpg?v=1700260854'),
(117, 18, 1, 89.99, 'https://m.media-amazon.com/images/I/51eKtDXphrL._AC_UY1000_.jpg'),
(127, 27, 1, 89.99, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqLC8qAWImtW_A8OQtMgu1ydqbzwJRMEKPWw&s'),
(128, 29, 1, 89.99, 'https://i.ebayimg.com/images/g/jEIAAOSwul1jh49f/s-l1200.jpg'),
(129, 29, 2, 89.99, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbgoogLPYRJE6bDZXCQeH3xpxbdWVXMGmhlA&s'),
(130, 31, 1, 89.99, 'https://cdn.media.amplience.net/i/frasersdev/37136015_o.jpg?v=00010101000000'),
(131, 32, 1, 89.99, 'https://calcioitalia.com/media/cache/sylius_shop_product_original/70/e4/b29ab8f8d1646fe9a0fb3597318b.jpeg'),
(132, 33, 1, 89.99, 'https://i8.amplience.net/i/jpl/jd_702672_a?qlt=92'),
(133, 34, 1, 89.99, 'https://i.ebayimg.com/images/g/5GAAAOSwS3pfyIy3/s-l1200.jpg'),
(134, 35, 1, 89.99, 'https://i.ebayimg.com/00/s/MTIwMFgxNjAw/z/COwAAOSwbPlj36TO/$_57.JPG?set_id=880000500F'),
(135, 36, 1, 89.99, 'https://thumblr.uniid.it/product/352813/04e4b1569301.jpg?width=3840&format=webp&q=75'),
(136, 37, 1, 89.99, 'https://images.footballfanatics.com/switzerland-national-team/switzerland-puma-home-shirt-2024-womens_ss5_p-14421177+pv-2+u-1i2fibsohguwm8gxco9v+v-d0ytqixidqjysmqwjzo8.jpg?_hv=2&w=600'),
(137, 24, 1, 89.99, 'https://m.media-amazon.com/images/I/516EfIfdOSL._AC_SX679_.jpg');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `tshirts`
--
ALTER TABLE `tshirts`
  ADD PRIMARY KEY (`tshirt_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `edition_id` (`edition_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `tshirts`
--
ALTER TABLE `tshirts`
  MODIFY `tshirt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `tshirts`
--
ALTER TABLE `tshirts`
  ADD CONSTRAINT `tshirts_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `tshirts_ibfk_2` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`edition_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
