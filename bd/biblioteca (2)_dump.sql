-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Окт 29 2026 г., 23:36
-- Версия сервера: 10.4.28-MariaDB
-- Версия PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `biblioteca`
--
CREATE DATABASE IF NOT EXISTS `biblioteca` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `biblioteca`;

-- --------------------------------------------------------

--
-- Структура таблицы `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `bonus` int(11) NOT NULL DEFAULT 1,
  `price` decimal(8,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `description`, `category_id`, `image`, `bonus`, `price`) VALUES
(1, 'The Book Eaters', 'Sunyi Dean', '978-1-250-81125-7', 'An unusual sci-fi story about a book eater woman who tries desperately to save her dangerous mind-eater son from tradition and certain death. Complete with dysfunctional family values, light Sapphic romance, and a strong, complex protagonist. Not for the faint of heart.', 1, '../front/assets/images/book1.png', 1, 1.00),
(2, 'Cackle', 'Rachel Harrison', '978-0-593-20202-9', 'Are your Halloween movies of choice The Witches of Eastwick and Practical Magic? Look no further than here - where a woman recovering from a breakup moves to a quaint town in upstate New York and befriends a beautiful witch.', 1, '../front/assets/images/book2.png', 1, 1.50),
(3, 'Dante: Poet of the Secular World', 'Erich Auerbach', NULL, 'Auerbach\'s engaging book places the \'Comedy\' within the tradition of epic, tragedy, and philosophy in general, arguing for Dante\'s uniqueness as one who raised the individual and his drama of soul into something of divine significance—an inspired introduction to Dante\'s main themes.', 1, '../front/assets/images/book3.png', 2, 2.00),
(4, 'The Last Queen', 'Clive Irving', '978-1-250-27439-0', 'A timely and revelatory new biography of Queen Elizabeth (and her family) exploring how the Windsors have evolved and thrived as the modern world has changed around them.', 1, '../front/assets/images/book4.png', 1, 1.00),
(5, 'The Body', 'Stephen King', '978-0-150-02637-6', 'Powerful novel that takes you back to a nostalgic time, exploring both the beauty and danger and loss of innocence that is youth.', 2, '../front/assets/images/book5.png', 2, 2.00),
(6, 'Carry: A Memoir of Survival on Stolen Land', 'Toni Jenson', NULL, 'This memoir about the author\'s relationship with gun violence feels both expansive and intimate, resulting in a lyrical indictment of the way things are.', 2, '../front/assets/images/book6.png', 1, 1.50),
(7, 'Days of Distraction', 'Alexandra Chang', '978-0-06-287075-9', 'A sardonic view of Silicon Valley culture, a meditation on race, and a journal of displacement and belonging, all in one form-defying package of spare prose.', 2, '../front/assets/images/book7.png', 1, 1.00),
(8, 'Dominicana', 'Angie Cruz', '978-1-250-20593-3', 'A fascinating story of a teenage girl who marries a man twice her age with the promise to bring her to America. Her marriage is an opportunity for her family to eventually immigrate. For fans of Isabel Allende and Julia Alvarez.', 2, '../front/assets/images/book8.png', 1, 1.50),
(9, 'Crude: A Memoir', 'Pablo Fajardo & Sophie Tardy-Joubert', NULL, 'Drawing and color by Damien Roudeau | This book illustrates the struggles of a group of indigenous Ecuadoreans as they try to sue the ChevronTexaco company for damage their oil fields did to the Amazon and her people.', 3, '../front/assets/images/book9.png', 1, 1.00),
(10, 'Let My People Go Surfing', 'Yvon Chouinard', '978-0-14-303783-5', 'Chouinard—climber, businessman, environmentalist—shares tales of courage and persistence from his experience of founding and leading Patagonia, Inc. Full title: Let My People Go Surfing: The Education of a Reluctant Businessman, Including 10 More Years of Business Unusual.', 3, '../front/assets/images/book10.png', 2, 2.50),
(11, 'The Octopus Museum: Poems', 'Brenda Shaughnessy', NULL, 'This collection of bold and scathingly beautiful feminist poems imagines what comes after our current age of environmental destruction, racism, sexism, and divisive politics.', 3, '../front/assets/images/book11.png', 1, 1.00),
(12, 'Shark Dialogues: A Novel', 'Kiana Davenport', '978-0-452-27458-7', 'An epic saga of seven generations of one family encompasses the tumultuous history of Hawaii as a Hawaiian woman gathers her four granddaughters together in an erotic tale of villains and dreamers, queens and revolutionaries, lepers and healers.', 3, '../front/assets/images/book12.png', 1, 2.00),
(13, 'Casual Conversation', 'Renia White', NULL, 'White\'s impressive debut collection takes readers through and beyond the concepts of conversation and the casual - both what we say to each other and what we don\'t, examining the possibilities around how we construct and communicate identity.', 4, '../front/assets/images/book13.png', 1, 1.00),
(14, 'The Great Fire', 'Lou Ureneck', '978-0-06-225645-4', 'The harrowing story of an ordinary American and a principled Naval officer who, horrified by the burning of Smyrna, led an extraordinary rescue effort that saved a quarter of a million refugees from the Armenian Genocide.', 4, '../front/assets/images/book14.png', 2, 2.00),
(15, 'Rickey: The Life and Legend', 'Howard Bryant', '978-0-358-04819-9', 'With the fall rolling around, one can\'t help but think of baseball\'s postseason coming up! And what better way to prepare for it than reading the biography of one of the game\'s all-time greatest performers, the Man of Steal, Rickey Henderson?', 4, '../front/assets/images/book15.png', 1, 1.50),
(16, 'Slug: And Other Stories', 'Megan Milks', NULL, 'Exes Tegan and Sara find themselves chained together by hairballs of codependency. A father and child experience the shared trauma of giving birth to gods from their wounds.', 4, '../front/assets/images/book16.png', 1, 1.00),
(17, '1984', 'George Orwell', '978-0-451-52493-5', 'A captivating fictional novel that explores deep human emotions, relationships, and personal growth. It delivers a powerful storyline with memorable characters and thought-provoking themes.', 1, '../front/assets/images/bookDefault.png', 1, 1.50);

-- --------------------------------------------------------

--
-- Структура таблицы `books_category`
--

CREATE TABLE `books_category` (
  `id` int(11) NOT NULL,
  `season` enum('winter','spring','summer','autumn') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `books_category`
--

INSERT INTO `books_category` (`id`, `season`) VALUES
(1, 'winter'),
(2, 'spring'),
(3, 'summer'),
(4, 'autumn');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `book_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 3, '2026-10-03 15:36:08', '2026-10-05 21:32:33'),
(4, 1, 13, 4, '2026-10-03 15:36:45', '2026-10-19 22:01:26'),
(5, 1, 3, 3, '2026-10-03 15:36:47', '2026-10-19 22:01:27'),
(6, 1, 10, 4, '2026-10-03 15:36:49', '2026-10-03 15:36:49'),
(7, 1, 5, 4, '2026-10-03 15:36:51', '2026-10-19 22:01:28'),
(8, 1, 14, 5, '2026-10-03 15:36:53', '2026-10-03 15:36:53'),
(9, 1, 4, 5, '2026-10-03 15:36:54', '2026-10-03 15:36:54'),
(15, 1, 1, 5, '2026-10-16 17:36:18', '2026-10-16 17:36:18'),
(19, 3, 2, 5, '2026-10-23 15:51:20', '2026-10-23 15:51:20');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_count` int(11) NOT NULL DEFAULT 0,
  `bonus` int(11) NOT NULL DEFAULT 0,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `login_count`, `bonus`, `role`) VALUES
(1, 'Jane', 'Valieva', 'test@test.com', '$2y$10$N/Nw8P2LmOewVsQWXQwBJekv20HJb7Cb7Z3dZFa9FBCd4oIIoXbpW', 31, 5, 'user'),
(2, 'Zhanna', 'Ant', 'test1@test.com', '$2y$10$X7NMSvC8moo9H.nX.260e.CNDSQESai0MS.TqQUG.3EFyovSRzRPS', 10, 1, 'user'),
(3, 'Walter', 'Bercunchelli', 'test2@test.com', '$2y$10$I4.hlTHSCn7XzORRWprLe.4RVTBupabARjteLlqHE1s9Nqsazyv3.', 6, 2, 'user'),
(4, 'Karla', 'Medina', 'test3@test.com', '$2y$10$k/njM/2MVqMo5vkImR9N5eX3TctMRZfsKu.RnrvTfvFFrW8WfUAqK', 1, 1, 'user'),
(5, 'Admin', 'Admin', 'admin@test.com', '$2y$10$6pGlFtd3Nq2rzkhBP/o3IOR5L/b1NkIixzngz/3SWXQbRykA9AUoi', 18, 0, 'admin'),
(6, 'Rodrigo', 'Nuñez', 'test4@test.com', '$2y$10$2r9tvyIwli1Ih2ca1fWEIe5xJbLdDRJQFQQrGeTCaBlOtZSjYQjWe', 1, 0, 'user');

-- --------------------------------------------------------

--
-- Структура таблицы `user_books`
--

CREATE TABLE `user_books` (
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_books`
--

INSERT INTO `user_books` (`user_id`, `book_id`, `purchased_at`) VALUES
(1, 1, '2026-10-16 17:36:18'),
(1, 3, '2026-10-03 15:43:21'),
(1, 4, '2026-10-03 15:43:21'),
(1, 5, '2026-10-03 15:43:21'),
(1, 10, '2026-10-03 15:43:21'),
(1, 13, '2026-10-03 15:43:21'),
(1, 14, '2026-10-03 15:43:21'),
(2, 3, '2026-10-03 15:43:21'),
(3, 2, '2026-10-23 15:50:07'),
(4, 13, '2026-10-03 15:43:21');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `fk_books_category` (`category_id`);

--
-- Индексы таблицы `books_category`
--
ALTER TABLE `books_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `season` (`season`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_book_review` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_books`
--
ALTER TABLE `user_books`
  ADD PRIMARY KEY (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `books_category`
--
ALTER TABLE `books_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `books_category` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_books`
--
ALTER TABLE `user_books`
  ADD CONSTRAINT `user_books_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
