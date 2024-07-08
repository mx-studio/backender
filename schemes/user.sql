SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
                        `id` int NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `password` varchar(32) NOT NULL,
                        `refresh_token` varchar(32) DEFAULT NULL,
                        `refresh_token_expire` datetime DEFAULT NULL,
                        `roles` varchar(255) NOT NULL,
                        `deleted_time` datetime DEFAULT NULL,
                        `blocked_time` datetime DEFAULT NULL,
                        `network` enum('VK','Google','Ok','MailRu') DEFAULT NULL,
                        `network_user_id` varchar(30) DEFAULT NULL,
                        `created` datetime NOT NULL,
                        `activated_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci


-- --------------------------------------------------------

--
-- Структура таблицы `user_meta`
--

DROP TABLE IF EXISTS `user_meta`;
CREATE TABLE `user_meta` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_uindex` (`id`),
  ADD UNIQUE KEY `unique_user` (`email`,`network`);

--
-- Индексы таблицы `user_meta`
--
ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_meta_id_uindex` (`id`),
  ADD UNIQUE KEY `unique_meta` (`user_id`,`name`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_meta`
--
ALTER TABLE `user_meta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

