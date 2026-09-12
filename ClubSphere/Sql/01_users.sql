-- =====================================================================
-- ClubSphere  |  FR1 - FR5  :  users table
--
-- Reconstructed from the FR1-FR5 module's own code
-- (Models/userModels.php, Controls/registerControls.php,
--  Controls/loginControls.php, Controls/adminControls.php)
-- and verified by running that module against it end to end:
-- register -> admin approves -> login as Admin / Moderator / Member.
--
-- WHY THIS FILE EXISTS
--   The `users` table was created by hand in phpMyAdmin, so it lived on
--   one laptop only. Anyone cloning the repo got PHP querying a table
--   that did not exist - no registration, no login, nothing testable.
--   Committing the schema is what makes the project reproducible.
--
-- IMPORT FIRST, before every other file in this folder. The other
-- modules have foreign keys pointing at users(u_id).
--
--   phpMyAdmin -> Import -> choose this file -> Go
-- =====================================================================

CREATE DATABASE IF NOT EXISTS clubsphere
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE clubsphere;


CREATE TABLE IF NOT EXISTS users (
    u_id        INT AUTO_INCREMENT PRIMARY KEY,

    name        VARCHAR(100) NOT NULL,
    uni_id      VARCHAR(20)  NOT NULL,
    email_id    VARCHAR(100) NOT NULL,

    -- password_hash() output. 255 chars, never shorter: PASSWORD_DEFAULT
    -- is allowed to change algorithm in a future PHP version and produce
    -- a longer hash. A VARCHAR(60) column silently truncates it and
    -- every login then fails with no error message.
    password    VARCHAR(255) NOT NULL,

    -- registerUser() inserts only name, uni_id, email_id and password,
    -- so BOTH of these defaults have to be right or registration breaks.
    role        VARCHAR(20)  NOT NULL DEFAULT 'Member',
    status      VARCHAR(20)  NOT NULL DEFAULT 'Pending',

    -- updateProfile() (FR3)
    game_type   VARCHAR(50)  DEFAULT NULL,
    ranking     VARCHAR(50)  DEFAULT NULL,
    social_link VARCHAR(255) DEFAULT NULL,

    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- checkUserExists() treats all three as "already taken", so the
    -- database should enforce that too rather than trusting the check
    CONSTRAINT uq_users_name   UNIQUE (name),
    CONSTRAINT uq_users_email  UNIQUE (email_id),
    CONSTRAINT uq_users_uni_id UNIQUE (uni_id)

) ENGINE = InnoDB;
-- InnoDB is required: the other modules add foreign keys to u_id, and a
-- foreign key to a MyISAM table fails with errno 150.


-- ---------------------------------------------------------------------
-- BOOTSTRAP ADMIN  -  do not delete this row
--
-- Without it the system deadlocks on a fresh database:
--
--   registerUser() inserts with the defaults above -> role 'Member',
--   status 'Pending'
--   loginControls.php refuses to log in anyone whose status is not
--   'Approved'
--   only a user with role 'Admin' can approve anybody
--   ...so on an empty database nobody can ever be approved, and the
--   whole application is unusable.
--
-- One pre-approved Admin breaks that circle. Everyone else registers
-- through the real form and gets approved by this account.
--
--   Username : admin
--   Password : admin1234
--
-- CHANGE THE PASSWORD after the group's own accounts are approved:
-- log in as admin, or run
--   UPDATE users SET password = '<new password_hash() output>'
--   WHERE name = 'admin';
-- ---------------------------------------------------------------------
INSERT INTO users (name, uni_id, email_id, password, role, status)
SELECT 'admin', 'ADMIN-0001', 'admin@clubsphere.local',
       '$2y$12$9mD8Q0xtq0i5EWa43or.XuGa0CJ1SMmz9WXR8W1ukYx/URMVB2V4C',
       'Admin', 'Approved'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE name = 'admin');


-- =====================================================================
-- CHECK THIS AGAINST MARIA'S phpMyAdmin BEFORE COMMITTING
--
-- Open her screenshot of  clubsphere -> users -> Structure  and confirm
-- these four things. Everything else can differ harmlessly.
--
--   1. COLUMN NAMES match exactly:
--        u_id  name  uni_id  email_id  password  role  status
--        game_type  ranking  social_link
--      The PHP refers to them by name, so a rename breaks it.
--
--   2. u_id is INT, PRIMARY KEY, AUTO_INCREMENT.
--
--   3. role and status DEFAULTS are 'Member' and 'Pending', spelled with
--      a capital letter. The code compares against 'Admin', 'Moderator',
--      'Member' and 'Approved', 'Pending', 'Rejected' - and MySQL string
--      comparison is case-insensitive by default, so this is about
--      matching her data, not about correctness.
--
--   4. password is at least VARCHAR(255).
--
-- If her columns are ENUMs rather than VARCHARs, that is fine and
-- arguably better - just make sure every value the PHP uses is in the
-- ENUM list.
-- =====================================================================
