CREATE DATABASE IF NOT EXISTS clubsphere
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE clubsphere;

CREATE TABLE IF NOT EXISTS users (
    u_id        INT(11) NOT NULL AUTO_INCREMENT,

    name        VARCHAR(100) NOT NULL,
    uni_id      VARCHAR(30)  NOT NULL,
    email_id    VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,

    role        ENUM('Member','Moderator','Admin')      DEFAULT 'Member',
    status      ENUM('Pending','Approved','Rejected')   DEFAULT 'Pending',

    game_type   VARCHAR(50)  DEFAULT NULL,
    ranking     VARCHAR(50)  DEFAULT NULL,
    social_link VARCHAR(255) DEFAULT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (u_id),
    UNIQUE KEY uni_id   (uni_id),
    UNIQUE KEY email_id (email_id),
    UNIQUE KEY name     (name)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;


INSERT INTO users
    (u_id, name, uni_id, email_id, password, role, game_type, ranking, social_link, status, created_at)
VALUES
 (1, 'Srity', '93939', 'srity@gmail.com', '$2y$10$PrfKUCwVz4TTqbGqD3kC3ue7Vbrghww7W2ajNFToL.22bebgH4YGC', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-05 11:59:54'),
 (2, 'Jotey', '2222', 'jotey@gmail.com', '$2y$10$KRX.4kZ0wUirehGLOYrd7eeI0YP8qZ2wj/sVcpl59jF8JQ8trpmrq', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-08 07:51:47'),
 (5, 'Maria', '2121-DUP', 'maria@gmail.com', '$2y$10$EKwDvg6poncTYqm/c8sEFOCFpDIqbGnWbajNJK13k2k5cI5wvMIOK', 'Member', NULL, NULL, NULL, 'Rejected', '2026-09-08 08:12:51'),
 (6, 'MariaJ', '222222', 'mj@gmail.com', '$2y$10$Fk4Ln2O9uoExhFuEnmIujeXb/ULFtPWtHnnNA7FuD9sw1ULHp1/nG', 'Admin', 'Age of the Empire', '4', 'http.ig.com', 'Approved', '2026-09-08 09:41:21'),
 (7, 'Nafisa', '', 'nafisa@gmail.com', '$2y$10$lkNaEL51uy0jk.FS2.z9PeDlNS1fkT5Kd80p9hAVKBaJWWhAf5rAe', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-09 14:57:20'),
 (8, 'Panda', '9090', 'panda@gmail.com', '$2y$10$KW4HFBjegY2OFQR6dVqIgePwifbXgiEfTeJbhPGyAtYMBysWK/aum', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 05:38:56'),
 (9, 'Shehzil', '1212', 'shehzil@gmail.com', '$2y$10$FB8rrLKuVnOayKbQLOT0cuTkyBpz9fGb2a7eHKv/wOlvQvA6tppWK', 'Moderator', NULL, NULL, NULL, 'Approved', '2026-09-11 11:30:26'),
 (10, 'Glowynowy', '2121', 'glow@gmail.com', '$2y$10$lPGfxg1Hdxw1HrulDn9unOIoIJB7u3X6XeoADWJIaGDEBMV.w7Eb6', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 11:35:23'),
 (11, 'Bibek', '3333', 'bibek@gmail.com', '$2y$10$YcxpQTL2TwEPrTi2TLyyiOU/c0k0VrQ1UVinIjhfWj8Etn8CM2OsS', 'Member', 'Valorant', '3', 'http.ig.com', 'Approved', '2026-09-11 11:41:08'),
 (12, 'Sayma', '4131', 'sayma@gmail.com', '$2y$10$W0kz/L9mWFvvjFCZvVX0rOcqAB7zMTae8bhE6w1JGJzHT9yXfTUmi', 'Member', NULL, NULL, NULL, 'Approved', '2026-09-11 16:24:47'),
 (13, 'Moon', '8888', 'moon@gmail.com', '$2y$10$yBevTIYiqjNTDlFXpJ7GCOK94OdyUsDicf1cNey5GFZ3t7fx4db6.', 'Member', NULL, NULL, NULL, 'Pending', '2026-09-12 05:08:44'),
 (14, 'Potato', '4352', 'potato@gmail.com', '$2y$10$FoRq.uF4lgw6.s9fvaJKNetqOWxL2AR.7a8V9GXwIEHRsPcWYf03u', 'Member', NULL, NULL, NULL, 'Pending', '2026-09-12 05:31:01');

INSERT IGNORE INTO users (name, uni_id, email_id, password, role, status) VALUES
 ('testadmin',     'TEST-ADMIN', 'testadmin@clubsphere.local',     '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Admin',     'Approved'),
 ('testmoderator', 'TEST-MOD',   'testmoderator@clubsphere.local', '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Moderator', 'Approved'),
 ('testmember',    'TEST-MEM',   'testmember@clubsphere.local',    '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Member',    'Approved');
