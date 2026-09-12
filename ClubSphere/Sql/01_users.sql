-- =====================================================================
-- ClubSphere  |  FR1 - FR5  :  users table
--
-- Source: the FR1-FR5 owner's own phpMyAdmin export (MariaDB 10.4.32),
-- with two changes, both explained below:
--   1. it can now be imported into an empty server
--   2. the stored data has been trimmed of stray whitespace
--
-- IMPORT THIS FIRST, before every other file in this folder. The other
-- modules have foreign keys pointing at users(u_id).
--
--   phpMyAdmin -> Import -> choose this file -> Go
-- =====================================================================

CREATE DATABASE IF NOT EXISTS clubsphere
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE clubsphere;

-- The original export assumed the database already existed and declared
-- the primary key in a trailing ALTER TABLE. Both are folded in here so
-- the file works on a completely fresh machine, which is what everyone
-- cloning the repo actually has.

CREATE TABLE IF NOT EXISTS users (
    u_id        INT(11) NOT NULL AUTO_INCREMENT,

    name        VARCHAR(100) NOT NULL,
    uni_id      VARCHAR(30)  NOT NULL,
    email_id    VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,

    -- registerUser() inserts only name, uni_id, email_id and password,
    -- so these two defaults are what every new account gets.
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
-- InnoDB is required: the other modules add foreign keys to u_id, and a
-- foreign key to a MyISAM table fails with errno 150.


-- ---------------------------------------------------------------------
-- EXISTING ACCOUNTS
--
-- These are the real rows from the export, with one fix: every `name`
-- and `uni_id` has been TRIMMED.
--
-- Why that mattered. registerControls.php reads the form with
--     $name = $_POST["name"];
-- and never calls trim(), so whatever spaces a browser sends get stored.
-- Nine of the twelve accounts had leading or trailing spaces.
--
-- MySQL ignores TRAILING spaces when comparing strings, but not LEADING
-- ones - so loginUser()'s  WHERE name = ?  simply could not find any
-- account whose stored name started with a space. In the original export
-- that meant Nafisa, Sayma, Moon and - worst of all - Shehzil, the only
-- Moderator in the whole database, were unable to log in at all. Nobody
-- could have tested a single moderator screen.
--
-- One account also had a uni_id consisting entirely of spaces, because
-- empty("   ") is false in PHP, so the "cannot be empty" check passed.
--
-- The code fix belongs in registerControls.php and has been raised with
-- its owner. This file just makes sure the data we all share is clean.
--
-- ONE DUPLICATE HAD TO BE RESOLVED
--
-- Trimming exposed a collision the whitespace had been hiding:
--   u_id 5  'Maria'      uni_id '2121'                 status Rejected
--   u_id 10 'Glowynowy'  uni_id '               2121'  status Approved
--
-- Those are the SAME university ID. The UNIQUE key on uni_id only let
-- both exist because one was padded with spaces - so the missing trim()
-- did not just make logins fail, it let a duplicate university ID into a
-- table that is explicitly meant to forbid them.
--
-- Both rows are kept. The REJECTED one (u_id 5) carries '2121-DUP' so
-- the file imports; the approved account keeps the real ID. Correct it
-- when you know which ID actually belongs to whom:
--     UPDATE users SET uni_id = '<real id>' WHERE u_id = 5;
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- TEST ACCOUNTS  -  password for all three:  clubsphere123
--
-- The accounts above are real people's, and nobody knows anyone else's
-- password - the export contains only hashes. Without a known login the
-- team clones the repo and cannot get in as anybody, and cannot even
-- register their way in, because a new account is created 'Pending' and
-- only an existing Admin can approve it.
--
-- These three break that circle and give one ready account per role, so
-- any member - or the faculty on defense day - can open the project and
-- immediately see all three dashboards.
--
--   testadmin      / clubsphere123   Admin
--   testmoderator  / clubsphere123   Moderator
--   testmember     / clubsphere123   Member
--
-- INSERT IGNORE so re-importing this file never fails on a duplicate.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO users (name, uni_id, email_id, password, role, status) VALUES
 ('testadmin',     'TEST-ADMIN', 'testadmin@clubsphere.local',     '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Admin',     'Approved'),
 ('testmoderator', 'TEST-MOD',   'testmoderator@clubsphere.local', '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Moderator', 'Approved'),
 ('testmember',    'TEST-MEM',   'testmember@clubsphere.local',    '$2y$12$febhVFXqDJ.wOsOwlz0tneuixqL.XQ4f3PfnMETppLX1QSF6EBebS', 'Member',    'Approved');


-- ---------------------------------------------------------------------
-- Handy one-liners
--
--   promote someone to Moderator so they can test FR11 / FR13:
--     UPDATE users SET role = 'Moderator' WHERE name = 'Bibek';
--
--   approve an account without going through the admin screen:
--     UPDATE users SET status = 'Approved' WHERE name = 'Moon';
--
--   see who can actually log in:
--     SELECT u_id, name, role, status FROM users WHERE status = 'Approved';
--
--   u_id 7 (Nafisa) has an EMPTY uni_id - the original value was nothing
--   but spaces, so trimming left an empty string. It imports fine and is
--   unique, but it shows as blank on the members list. Give it a real
--   value when you know it:
--     UPDATE users SET uni_id = '23-54213-3' WHERE name = 'Nafisa';
-- ---------------------------------------------------------------------
