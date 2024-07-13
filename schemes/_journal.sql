SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `journal`;
create table journal
(
    id       int auto_increment,
    juid     varchar(32)  COLLATE utf8mb4_general_ci not null,
    datetime timestamp   not null DEFAULT CURRENT_TIMESTAMP,
    ip       varchar(15) COLLATE utf8mb4_general_ci not null,
    action  varchar(255)  COLLATE utf8mb4_general_ci       not null,
    referer  varchar(255)  COLLATE utf8mb4_general_ci       null,
    params   text  COLLATE utf8mb4_general_ci       null,
    useragent  varchar(255)  COLLATE utf8mb4_general_ci       not null,
    constraint journal_pk
        primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;