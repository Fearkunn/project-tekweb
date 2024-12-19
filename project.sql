-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2024 at 07:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_genre`
--

CREATE TABLE `detail_genre` (
  `id_detail` int(11) NOT NULL,
  `id_game` int(11) DEFAULT NULL,
  `id_genre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_genre`
--

INSERT INTO `detail_genre` (`id_detail`, `id_game`, `id_genre`) VALUES
(1, 1, 1),
(2, 1, 11),
(3, 1, 21),
(4, 2, 2),
(5, 2, 21),
(6, 3, 1),
(7, 3, 7),
(8, 3, 8),
(9, 3, 11),
(10, 3, 21),
(11, 4, 11),
(12, 4, 15),
(13, 5, 7),
(14, 5, 11),
(15, 6, 1),
(16, 6, 15),
(17, 7, 1),
(18, 7, 11),
(19, 7, 28),
(20, 8, 6),
(21, 8, 11),
(22, 9, 6),
(23, 9, 11),
(24, 10, 1),
(25, 10, 11),
(26, 10, 15),
(27, 11, 11),
(28, 11, 15),
(29, 12, 29),
(30, 13, 2),
(31, 13, 4),
(32, 13, 21),
(33, 14, 2),
(34, 14, 4),
(35, 15, 1),
(36, 15, 2),
(37, 15, 4),
(38, 16, 7),
(39, 16, 11),
(40, 17, 11),
(41, 17, 15),
(42, 18, 16),
(43, 18, 21),
(44, 19, 1),
(45, 19, 2),
(46, 19, 4),
(47, 19, 9),
(48, 19, 11),
(49, 20, 1),
(50, 20, 2),
(51, 20, 5),
(52, 20, 7),
(53, 20, 8);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id_game` int(11) NOT NULL,
  `game_name` varchar(255) NOT NULL,
  `game_desc` text DEFAULT NULL,
  `is_admit` tinyint(1) NOT NULL,
  `release_date` date DEFAULT NULL,
  `like_count` int(11) DEFAULT 0,
  `games_image` varchar(255) DEFAULT NULL,
  `id_publisher` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id_game`, `game_name`, `game_desc`, `is_admit`, `release_date`, `like_count`, `games_image`, `id_publisher`) VALUES
(1, 'Grand Theft Auto V', 'Grand Theft Auto V for PC offers players the option to explore the award-winning world of Los Santos and Blaine County in resolutions of up to 4k and beyond, as well as the chance to experience the game running at 60 frames per second.', 1, '2024-12-19', 1, 'https://i.ibb.co/yFj4L1G/33c7fa158ffa.png', 1),
(2, 'Red Dead Redemption 2', 'Winner of over 175 Game of the Year Awards and recipient of over 250 perfect scores, RDR2 is the epic tale of outlaw Arthur Morgan and the infamous Van der Linde gang, on the run across America at the dawn of the modern age. Also includes access to the shared living world of Red Dead Online.', 1, '2024-12-19', 0, 'https://i.ibb.co/7X8rm9n/e8e8be389386.jpg', 1),
(3, 'Need for Speed™ Unbound', 'Race to the top, definitely don’t flop. Outsmart the cops, and enter weekly qualifiers for The Grand: the ultimate street race. Pack your garage with precision-tuned, custom rides, and light up the streets with your style.', 1, '2024-12-19', 0, 'https://i.ibb.co/X5C21nH/01783a7a6274.png', 2),
(4, 'Battlefield™ 2042', 'Battlefield™ 2042 is a first-person shooter that marks the return to the iconic all-out warfare of the franchise.', 1, '2024-12-19', 0, 'https://i.ibb.co/j6Md9PZ/ee436dd818b1.jpg', 2),
(5, 'EA SPORTS™ FIFA 22', 'Powered by Football™, EA SPORTS™ FIFA 22 brings the game even closer to the real thing with fundamental gameplay advances and a new season of innovation across every mode.', 1, '2024-12-19', 1, 'https://i.ibb.co/z6J7HTR/93d88296b7bd.jpg', 2),
(6, 'VALORANT', 'VALORANT is a character-based 5v5 tactical shooter set on the global stage. Outwit, outplay, and outshine your competition with tactical abilities, precise gunplay, and adaptive teamwork.', 1, '2024-12-19', 0, 'https://i.ibb.co/JzzTrft/6f8e42eb6d22.jpg', 3),
(7, 'League Of Legends', 'With more than 140 champions, you’ll find the perfect match for your playstyle. Master one, or master them all.', 1, '2024-12-19', 0, 'https://i.ibb.co/nBsH8ZS/710d0b52d103.png', 3),
(8, 'Teamfight Tactics', 'Battle for the convergence. Draft, deploy, and dominate with a revolving roster of League of Legends champions in a round-based battle for supremacy. Outsmart your opponents and adapt as you go—the strategy is all up to you.', 1, '2024-12-19', 0, 'https://i.ibb.co/sy9WDrh/bf32ec29c99c.png', 3),
(9, 'Dota 2', 'Every day, millions of players worldwide enter battle as one of over a hundred Dota heroes. And no matter if it\'s their 10th hour of play or 1,000th, there\'s always something new to discover. With regular updates that ensure a constant evolution of gameplay, features, and heroes, Dota 2 has taken on a life of its own.', 1, '2024-12-20', 0, 'https://i.ibb.co/SvCxg3y/44e246a8d2b0.jpg', 4),
(10, 'Counter-Strike 2', 'For over two decades, Counter-Strike has offered an elite competitive experience, one shaped by millions of players from across the globe. And now the next chapter in the CS story is about to begin. This is Counter-Strike 2.', 1, '2024-12-20', 0, 'https://i.ibb.co/QDkB8PL/189c6a9e18dd.jpg', 4),
(11, 'Left 4 Dead 2', 'Set in the zombie apocalypse, Left 4 Dead 2 (L4D2) is the highly anticipated sequel to the award-winning Left 4 Dead, the #1 co-op game of 2008. This co-operative action horror FPS takes you and your friends through the cities, swamps and cemeteries of the Deep South, from Savannah to New Orleans.', 1, '2024-12-20', 0, 'https://i.ibb.co/mCk8v7X/0d1355b716d7.jpg', 4),
(12, 'World of Warcraft', 'Get back in the fight as you defend Azeroth from the shadows below. Journey through never-before-seen subterranean worlds filled with hidden wonders and lurking perils, down to the dark depths of the nerubian empire, where the malicious Harbinger of the Void is gathering arachnid forces to bring Azeroth to its knees.', 1, '2024-12-20', 0, 'https://i.ibb.co/YPp1yrS/ada871be3c23.png', 5),
(13, 'Genshin Impact', 'Embark on a journey across Teyvat to find your lost sibling and seek answers from The Seven — the gods of each element. Explore this wondrous world, join forces with a diverse range of characters, and unravel the countless mysteries that Teyvat holds.', 1, '2024-12-20', 0, 'https://i.ibb.co/mBWMtvn/cea24b8e71bc.jpg', 6),
(14, 'Honkai: Star Rail', 'Honkai: Star Rail is a new HoYoverse space fantasy RPG. Hop aboard the Astral Express and experience the galaxy\'s infinite wonders on this journey filled with adventure and thrills.', 1, '2024-12-20', 0, 'https://i.ibb.co/RSN5Wtt/8d5e1be29b11.png', 6),
(15, 'Honkai Impact 3rd', 'Honkai Impact 3rd is an anime action RPG game and playable for free across platforms. In a world corrupted by Honkai, brave girls lead a fledgling resistance of Valkyries. You will guide Valkyries with different equipment and strategies to protect the world.', 1, '2024-12-20', 0, 'https://i.ibb.co/rsyk5jG/18a4c9a88640.jpg', 6),
(16, 'eFootball™', 'The feverish football game with a worldwide total of 750 million downloads is waiting for you! Play eFootball™ with users around the world!', 1, '2024-12-20', 0, 'https://i.ibb.co/92gXr3H/f6b2744dd8de.png', 7),
(17, 'Marvel Rivals', 'Marvel Rivals is a Super Hero Team-Based PVP Shooter! Assemble an all-star Marvel squad, devise countless strategies by combining powers to form unique Team-Up skills and fight in destructible, ever-changing battlefields across the continually evolving Marvel universe!', 1, '2024-12-20', 0, 'https://i.ibb.co/r4XyPYy/520e52c3d9ce.jpg', 8),
(18, 'Once Human', 'Once Human is a multiplayer open-world survival game set in a post-apocalyptic world. Join forces with friends to fight for survival, construct your sanctuary, and conquer terrifying aberrations to unravel the truth behind the apocalypse. Do you still hold the answer to what it means to be human?', 1, '2024-12-20', 0, 'https://i.ibb.co/7CZQxgL/b4c21deaed59.jpg', 8),
(19, 'ARK: Survival Evolved', 'Stranded on the shores of a mysterious island, you must learn to survive. Use your cunning to kill or tame the primeval creatures roaming the land, and encounter other players to survive, dominate... and escape!', 1, '2024-12-20', 0, 'https://i.ibb.co/4tPgRnz/2083d7e38028.png', 9),
(20, 'Forza Horizon 5', 'Explore the vibrant open world landscapes of Mexico with limitless, fun driving action in the world’s greatest cars. Join a thrilling game of chase with our new 5v1 Multiplayer Experience: Hide & Seek.', 1, '2024-12-20', 0, 'https://i.ibb.co/2qQ03nv/9abb39c730d9.jpg', 10);

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `id_genre` int(11) NOT NULL,
  `genre_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id_genre`, `genre_name`) VALUES
(1, 'Action'),
(2, 'Adventure'),
(3, 'Casual'),
(4, 'RPG'),
(5, 'Simulation'),
(6, 'Strategy'),
(7, 'Sports'),
(8, 'Racing'),
(9, 'Indie'),
(10, 'Early Access'),
(11, 'Multiplayer'),
(12, 'Singleplayer'),
(13, 'Action-Adventure'),
(14, 'Horror'),
(15, 'Shooter'),
(16, 'Survival'),
(17, 'Platformer'),
(18, 'Puzzle'),
(19, 'Visual Novel'),
(20, 'Metroidvania'),
(21, 'Open World'),
(22, 'Sci-Fi & Cyberpunk'),
(23, 'Fantasy'),
(24, 'Simulation - Space & Flight'),
(25, 'Building & Automation'),
(26, 'Card & Board'),
(27, 'Turn-Based Strategy'),
(28, 'Real-Time Strategy'),
(29, 'MMORPG');

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

CREATE TABLE `library` (
  `id_library` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_game` int(11) DEFAULT NULL,
  `is_like` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library`
--

INSERT INTO `library` (`id_library`, `id_user`, `id_game`, `is_like`) VALUES
(1, 1, 1, 1),
(2, 1, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `id_publisher` int(11) NOT NULL,
  `publisher_name` varchar(255) NOT NULL,
  `publisher_password` varchar(255) NOT NULL,
  `publisher_logo` varchar(255) DEFAULT NULL,
  `publisher_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publisher`
--

INSERT INTO `publisher` (`id_publisher`, `publisher_name`, `publisher_password`, `publisher_logo`, `publisher_email`) VALUES
(1, 'Rockstar Games', '$2y$10$mMHG.mcReK/gv2bFXRaFru0yPn6D42zr4oZL5gHJykN6liwlTB3mS', NULL, 'rockstar@publisher.com'),
(2, 'EA', '$2y$10$tTZAzGZ2wvWngdiu11eJX.Wt6rOsaUsIbSAZEabXVKSV8Fpks.y5O', NULL, 'ea@publisher.com'),
(3, 'Riot Games', '$2y$10$eDxEmepxi59Qu/KYIaGn4uNq59ZWoTVcGpAIt9PmOS/YbP1a71ClK', NULL, 'riot@publisher.com'),
(4, 'Valve', '$2y$10$P/dkeAum4ntSJ0rDMgxJBeJL6fJRRvjSghPOGLw/M5ureQEQu7GBW', NULL, 'valve@publisher.com'),
(5, 'Blizzard', '$2y$10$gzN7tqIScpWgcGJ9V4hFd.IEvsaxCTHq9TUKZwmY2Yg6my9.FRCHS', NULL, 'blizzard@publisher.com'),
(6, 'HoYoverse', '$2y$10$rWes7pK1f0INLu0PasimRuBIjcL2cfIx5M4bnaaHzU2xcoB5cBXiq', NULL, 'hoyoverse@publisher.com'),
(7, 'KONAMI', '$2y$10$oSUzj9/yY/qwsG27h726We3pB/NVqtuRa5sRruvLCuWoLrU.IjipG', NULL, 'konami@publisher.com'),
(8, 'NetEase Games', '$2y$10$Ag3rGk06xJXlJcZPyl7i3erXrUasr0.qfZNEhF9f2yMU1s17GrR.u', NULL, 'netease@publisher.com'),
(9, 'Studio Wildcard', '$2y$10$Gq089lQ4rymaDgItaBlR0eVadvKdl0vNAwbWMazNpl4b9ESvayvLG', NULL, 'studio@publisher.com'),
(10, 'Xbox Game Studios', '$2y$10$qjYAZu98eUTfw32w8on0EuVbkhWqA40RaSFZb3iLFAKGhfUMieyfu', NULL, 'xbox@publisher.com');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id_review` int(11) NOT NULL,
  `review_content` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_game` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id_review`, `review_content`, `id_user`, `id_game`) VALUES
(1, 'keren', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_profile` varchar(255) DEFAULT NULL,
  `role_user` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `user_password`, `user_email`, `user_profile`, `role_user`) VALUES
(1, 'tes', '$2y$10$Tjj.vPIX/MfcvPumd1IJ7eI4ciQlsKfbHqK8JzrDPY2OsS55Vc6rG', 'tes@gmail.com', 'https://i.ibb.co/NY8XXTY/ee6a020b54bd.png', 'USER');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_genre`
--
ALTER TABLE `detail_genre`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_genre` (`id_genre`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id_game`),
  ADD KEY `id_publisher` (`id_publisher`);

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`id_genre`);

--
-- Indexes for table `library`
--
ALTER TABLE `library`
  ADD PRIMARY KEY (`id_library`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_game` (`id_game`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`id_publisher`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_game` (`id_game`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_genre`
--
ALTER TABLE `detail_genre`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id_game` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `id_genre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `library`
--
ALTER TABLE `library`
  MODIFY `id_library` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `publisher`
--
ALTER TABLE `publisher`
  MODIFY `id_publisher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_genre`
--
ALTER TABLE `detail_genre`
  ADD CONSTRAINT `detail_genre_ibfk_1` FOREIGN KEY (`id_game`) REFERENCES `games` (`id_game`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_genre_ibfk_2` FOREIGN KEY (`id_genre`) REFERENCES `genre` (`id_genre`) ON DELETE CASCADE;

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`id_publisher`) REFERENCES `publisher` (`id_publisher`) ON DELETE CASCADE;

--
-- Constraints for table `library`
--
ALTER TABLE `library`
  ADD CONSTRAINT `library_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_ibfk_2` FOREIGN KEY (`id_game`) REFERENCES `games` (`id_game`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`id_game`) REFERENCES `games` (`id_game`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
