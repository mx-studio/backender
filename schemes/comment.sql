SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Структура таблицы `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
                           `id` int NOT NULL,
                           `object_id` int NOT NULL,
                           `user_id` int NOT NULL,
                           `text` text COLLATE utf8mb4_general_ci NOT NULL,
                           `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           `parent_comment_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `comment`
--
ALTER TABLE `comment`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comment`
--
ALTER TABLE `comment`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
