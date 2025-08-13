-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0
-- Время создания: Авг 13 2025 г., 18:59
-- Версия сервера: 8.0.41
-- Версия PHP: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `Reader2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Book`
--

CREATE TABLE `Book` (
  `id` int NOT NULL,
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Book`
--

INSERT INTO `Book` (`id`, `title`, `author`, `description`, `user_id`, `is_public`) VALUES
(48, 'Title1', 'Author1', 'Description1', 6, 1),
(49, 'Title1', 'Author1', 'Description1', 6, 0),
(50, 'Title1', 'Author1', 'Description1', 6, 0),
(51, 'Title1', 'Author1', 'Description1', 6, 0),
(52, 'Title1', 'Author1', 'Description1', 6, 0),
(53, 'Title1', 'Author1', 'Description1', 6, 0),
(54, 'Title1', 'Author1', 'Description1', 6, 0),
(55, 'Title1', 'Author1', 'Description1', 6, 0),
(56, 'Title1', 'Author1', 'Description1', 6, 0),
(57, 'Title1', 'Author1', 'Description1', 6, 0),
(58, 'Title1', 'Author1', 'Description1', 7, 0),
(59, 'Title1', 'Author1', 'Description1', 7, 0),
(60, 'Title1', 'Author1', 'Description1', 7, 0),
(61, 'Title1', 'Author1', 'Description1', 7, 0),
(62, 'Title1', 'Author1', 'Description1', 7, 0),
(63, 'Title1', 'Author1', 'Description1', 7, 0),
(64, 'Title1', 'Author1', 'Description1', 7, 0),
(65, 'Title1', 'Author1', 'Description1', 7, 0),
(66, 'Title1', 'Author1', 'Description1', 7, 0),
(67, 'Title1', 'Author1', 'Description1', 7, 0),
(68, 'Title1', 'Author1', 'Description1', 7, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Book`
--
ALTER TABLE `Book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Book`
--
ALTER TABLE `Book`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Book`
--
ALTER TABLE `Book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
