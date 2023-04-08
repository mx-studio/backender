SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET time_zone = "+00:00";

--
-- База данных: `app6242cdfdeab89`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
                       `id` int NOT NULL,
                       `user_id` varchar(128) NOT NULL,
                       `tag_group_id` int NOT NULL,
                       `name` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tag_group`
--

DROP TABLE IF EXISTS `tag_group`;
CREATE TABLE `tag_group` (
                             `id` int NOT NULL,
                             `name` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tag_rel`
--

DROP TABLE IF EXISTS `tag_rel`;
CREATE TABLE `tag_rel` (
                           `id` int NOT NULL,
                           `object_id` int NOT NULL,
                           `tag_id` int NOT NULL,
                           `user_id` varchar(128) NOT NULL,
                           `tag_group_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `tag`
--
ALTER TABLE `tag`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag` (`user_id`,`tag_group_id`,`name`);

--
-- Индексы таблицы `tag_group`
--
ALTER TABLE `tag_group`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Индексы таблицы `tag_rel`
--
ALTER TABLE `tag_rel`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag_rel` (`user_id`,`object_id`,`tag_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `tag`
--
ALTER TABLE `tag`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tag_group`
--
ALTER TABLE `tag_group`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tag_rel`
--
ALTER TABLE `tag_rel`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

