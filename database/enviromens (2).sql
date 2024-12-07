-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2024 at 10:42 PM
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
-- Database: `enviromens`
--

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `drive_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `donation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donation_drives`
--

CREATE TABLE `donation_drives` (
  `drive_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `goal_amount` int(11) NOT NULL,
  `current_amount` int(11) DEFAULT 0,
  `creator_id` int(11) NOT NULL,
  `end_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_drives`
--

INSERT INTO `donation_drives` (`drive_id`, `title`, `description`, `goal_amount`, `current_amount`, `creator_id`, `end_date`, `created_at`) VALUES
(1, 'points for a tree', 'donate a point to plant a tree', 10000000, 0, 1, '2025-01-03', '2024-12-07 05:22:23');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `reference_link` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `content`, `reference_link`, `created_at`) VALUES
(1, 1, 'The purpose of saving our environment lies at the very heart of ensuring the survival and well-being of all living organisms, including humanity. A healthy environment provides us with clean air to breathe, safe water to drink, fertile soil to grow our food, and the biodiversity that sustains ecosystems. Preserving natural habitats and reducing pollution are vital to mitigating climate change, which threatens the stability of our planet. Protecting the environment is also about safeguarding the future for generations to come, ensuring they inherit a world rich in resources, beauty, and opportunity. Beyond survival, saving the environment fosters a deeper sense of responsibility, unity, and respect for the intricate balance of nature. It is an urgent call to action to restore harmony between human activity and the natural world, recognizing that every effort we make today—whether reducing waste, conserving energy, or planting trees—contributes to a collective global mission to preserve life on Earth.', 'https://www.wwf.org.uk/what-we-do/valuing-nature#:~:text=It%20underpins%20our%20economy%2C%20our,our%20health%2C%20happiness%20and%20prosperity.', '2024-12-07 00:00:56'),
(2, 2, 'Saving our environment is essential for maintaining the delicate balance that sustains life on Earth. It ensures the health of ecosystems that provide vital resources such as clean air, water, and food, which are indispensable for our survival. Protecting the environment also means combating the devastating impacts of climate change, preserving biodiversity, and preventing the loss of species that play crucial roles in our planet’s ecosystems. Beyond its practical benefits, environmental conservation nurtures a deeper connection to nature, reminding us of the beauty and interconnectedness of life. It is a shared responsibility to protect and restore our planet, not only for ourselves but for future generations, ensuring they inherit a world that thrives with life and opportunity. Every action, big or small, contributes to the global effort to safeguard the environment, fostering hope and resilience in the face of growing environmental challenges.', 'https://greensuggest.com/11-reasons-why-protecting-the-environment-is-important/', '2024-12-07 03:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `post_reactions`
--

CREATE TABLE `post_reactions` (
  `reaction_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_reactions`
--

INSERT INTO `post_reactions` (`reaction_id`, `post_id`, `user_id`, `reaction_type`, `created_at`) VALUES
(1, 1, 1, 'like', '2024-12-06 16:03:44'),
(2, 2, 1, 'like', '2024-12-06 19:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `task_leader` int(11) NOT NULL,
  `cause` varchar(255) DEFAULT NULL,
  `category` enum('air','water','land','Natural Calamities') NOT NULL,
  `points` int(11) DEFAULT 0,
  `status` enum('active','completed','failed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `due_date`, `task_leader`, `cause`, `category`, `points`, `status`, `created_at`) VALUES
(1, 'general cleaning', '2024-12-03', 1, 'to clean our space', 'land', 100, 'active', '2024-12-05 12:09:48'),
(2, 'tree planting', '2024-12-25', 1, 'to clean our space', 'land', 100, 'active', '2024-12-05 12:25:23'),
(3, 'drainage cleaning', '2025-01-11', 3, 'to prevent flooding', 'land', 50, 'active', '2024-12-06 17:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `task_evidence`
--

CREATE TABLE `task_evidence` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_evidence`
--

INSERT INTO `task_evidence` (`id`, `task_id`, `user_id`, `file_path`, `upload_date`) VALUES
(1, 2, 2, '6753371496715_3.jpg', '2024-12-07 01:40:36'),
(2, 2, 2, '67533758a36f5_3.jpg', '2024-12-07 01:41:44'),
(3, 2, 3, '67533d8f1371d_3.png', '2024-12-07 02:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `task_participants`
--

CREATE TABLE `task_participants` (
  `participant_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `join_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_status` enum('pending','completed','failed') DEFAULT 'pending',
  `status` enum('done','ongoing','notdone') DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_participants`
--

INSERT INTO `task_participants` (`participant_id`, `task_id`, `user_id`, `join_date`, `completion_status`, `status`) VALUES
(1, 2, 2, '2024-12-05 14:14:05', 'completed', 'done'),
(2, 1, 2, '2024-12-05 17:43:51', 'pending', 'ongoing'),
(3, 2, 3, '2024-12-06 18:08:01', 'failed', 'notdone'),
(4, 3, 1, '2024-12-06 20:26:00', 'pending', 'ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `continent` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'profile1.png',
  `bio` text DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `tasks_completed` int(11) DEFAULT 0,
  `join_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `age`, `gender`, `continent`, `password`, `profile_picture`, `bio`, `total_points`, `tasks_completed`, `join_date`) VALUES
(1, 'rayne', 19, 'male', 'europe', '$2y$10$QLBascUN6Pta5S4yWIEG1O.bGdJMDfw8IIonnaaNZLvtcDFOpU3NC', 'profile3.png', NULL, 0, 0, '2024-12-05 12:09:29'),
(2, 'oshu', 18, 'female', 'asia', '$2y$10$UKUXMMzAdkcxEWT6KNuOIOvSE8cjmz6DqBWcBJGWshlGNi6giumui', 'profile2.png', NULL, 300, 3, '2024-12-05 12:50:28'),
(3, 'emma', 16, 'female', 'asia', '$2y$10$u1OVB6Sd4/gAEbiYhVn4AeLkx4NmINmgzwuMkU8WGqxfV9QzfMHT6', 'profile2.png', NULL, 100, 1, '2024-12-06 09:09:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `drive_id` (`drive_id`);

--
-- Indexes for table `donation_drives`
--
ALTER TABLE `donation_drives`
  ADD PRIMARY KEY (`drive_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD PRIMARY KEY (`reaction_id`),
  ADD UNIQUE KEY `unique_reaction` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_leader` (`task_leader`);

--
-- Indexes for table `task_evidence`
--
ALTER TABLE `task_evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `task_participants`
--
ALTER TABLE `task_participants`
  ADD PRIMARY KEY (`participant_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donation_drives`
--
ALTER TABLE `donation_drives`
  MODIFY `drive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `reaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_evidence`
--
ALTER TABLE `task_evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_participants`
--
ALTER TABLE `task_participants`
  MODIFY `participant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`drive_id`) REFERENCES `donation_drives` (`drive_id`);

--
-- Constraints for table `donation_drives`
--
ALTER TABLE `donation_drives`
  ADD CONSTRAINT `donation_drives_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD CONSTRAINT `post_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`),
  ADD CONSTRAINT `post_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`task_leader`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `task_evidence`
--
ALTER TABLE `task_evidence`
  ADD CONSTRAINT `task_evidence_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_evidence_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `task_participants`
--
ALTER TABLE `task_participants`
  ADD CONSTRAINT `task_participants_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
