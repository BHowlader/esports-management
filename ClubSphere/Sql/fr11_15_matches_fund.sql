-- =====================================================================
-- ClubSphere  |  FR11 - FR15
-- Bibek Howlader (23-54606-3)
--
--   FR11  Moderators shall be able to set and modify match times
--         within a tournament.
--   FR12  Members or Moderators shall be able to upload match results
--         and supporting screenshots.
--   FR13  Moderators shall have the ability to verify and finalize
--         submitted match results.
--   FR14  The system shall automatically update rankings and
--         leaderboards based on match outcomes.
--   FR15  Admins shall be able to record income from sponsorships,
--         donations and entry fees.
--
-- HOW TO IMPORT (phpMyAdmin)
--   1. open http://localhost/phpmyadmin
--   2. select the existing  clubsphere  database in the left sidebar
--   3. Import tab -> Choose File -> this file -> Import
--
-- This file NEVER creates or alters the `users` table. That table
-- belongs to the FR1-FR5 module and already exists.
-- =====================================================================

USE clubsphere;

-- Foreign keys only work between InnoDB tables. If `users` was created
-- as MyISAM, every CREATE TABLE below would fail with errno 150.
-- This line is harmless if it is already InnoDB.
ALTER TABLE users ENGINE = InnoDB;


-- ---------------------------------------------------------------------
-- STUB TABLES - team / tournament
--
-- These belong to the FR6-FR10 module, which is not merged yet. My
-- five requirements cannot be demonstrated without somewhere for teams
-- and tournaments to live, so I create the minimum here with
-- CREATE TABLE IF NOT EXISTS.
--
-- When the FR6-FR10 branch lands, that member owns these tables and
-- extends them. Because of IF NOT EXISTS, importing their file after
-- mine will not wipe anything. Delete this whole block once their
-- module is merged and re-import.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS team (
    team_id      INT AUTO_INCREMENT PRIMARY KEY,
    team_name    VARCHAR(100) NOT NULL UNIQUE,
    game_name    VARCHAR(50)  NOT NULL,
    status       VARCHAR(20)  NOT NULL DEFAULT 'Active',
    description  TEXT                  DEFAULT NULL,
    created_date DATE                  DEFAULT NULL,
    captain_id   INT                   DEFAULT NULL,
    CONSTRAINT fk_team_captain FOREIGN KEY (captain_id)
        REFERENCES users (u_id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS team_member (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id        INT NOT NULL,
    user_id        INT NOT NULL,
    team_role      VARCHAR(20) NOT NULL DEFAULT 'Player',
    joined_date    DATE                 DEFAULT NULL,
    status         VARCHAR(20) NOT NULL DEFAULT 'Active',
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users (u_id)  ON DELETE CASCADE,
    CONSTRAINT uq_team_user UNIQUE (team_id, user_id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tournament (
    tournament_id INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(150) NOT NULL,
    game_title    VARCHAR(50)  NOT NULL,
    rules         TEXT                  DEFAULT NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'Upcoming',
    start_date    DATE                  DEFAULT NULL,
    end_date      DATE                  DEFAULT NULL,
    created_by    INT                   DEFAULT NULL,
    CONSTRAINT fk_tour_creator FOREIGN KEY (created_by)
        REFERENCES users (u_id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tournament_register (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id   INT NOT NULL,
    team_id         INT NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'Approved',
    CONSTRAINT fk_treg_tour FOREIGN KEY (tournament_id) REFERENCES tournament (tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_treg_team FOREIGN KEY (team_id)       REFERENCES team (team_id)             ON DELETE CASCADE,
    CONSTRAINT uq_tour_team  UNIQUE (tournament_id, team_id)
) ENGINE = InnoDB;


-- =====================================================================
-- MY TABLES START HERE
-- =====================================================================

-- ---------------------------------------------------------------------
-- FR11 : matches
--
-- The SRS calls this entity MATCH, but MATCH is a reserved word in
-- MySQL (MATCH ... AGAINST full-text search). Naming the table
-- `matches` means no query ever needs backticks. Deliberate, documented
-- deviation from the SRS.
--
-- status lifecycle:
--   Pending    created, no time set yet
--   Scheduled  FR11 - a moderator has set match_time
--   Completed  FR13 - a moderator verified the result
--   Cancelled  called off
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matches (
    match_id      INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    round_no      INT NOT NULL DEFAULT 1,
    team1_id      INT NOT NULL,
    team2_id      INT NOT NULL,
    winner_id     INT          DEFAULT NULL,
    match_time    DATETIME     DEFAULT NULL,
    venue         VARCHAR(100) DEFAULT NULL,
    status        VARCHAR(20)  NOT NULL DEFAULT 'Pending',
    scheduled_by  INT          DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_match_tournament FOREIGN KEY (tournament_id)
        REFERENCES tournament (tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_match_team1  FOREIGN KEY (team1_id)     REFERENCES team (team_id),
    CONSTRAINT fk_match_team2  FOREIGN KEY (team2_id)     REFERENCES team (team_id),
    CONSTRAINT fk_match_winner FOREIGN KEY (winner_id)    REFERENCES team (team_id) ON DELETE SET NULL,
    CONSTRAINT fk_match_sched  FOREIGN KEY (scheduled_by) REFERENCES users (u_id)   ON DELETE SET NULL,

    CONSTRAINT chk_match_teams CHECK (team1_id <> team2_id)
) ENGINE = InnoDB;

CREATE INDEX idx_match_tournament ON matches (tournament_id, status);
CREATE INDEX idx_match_time       ON matches (match_time);


-- ---------------------------------------------------------------------
-- FR12 + FR13 : match_result
--
-- One row per submission. A rejected submission is KEPT, not deleted -
-- that is the audit trail - and the team submits again. So a match may
-- have several rows but at most one 'Verified'. That rule is enforced
-- in PHP (resultModel.php) because MySQL cannot express a partial
-- unique index.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS match_result (
    result_id           INT AUTO_INCREMENT PRIMARY KEY,
    match_id            INT NOT NULL,
    submitted_by        INT NOT NULL,
    score_team1         INT NOT NULL,
    score_team2         INT NOT NULL,
    screenshot          VARCHAR(255) DEFAULT NULL,
    verification_status VARCHAR(20)  NOT NULL DEFAULT 'Pending',
    verified_by         INT          DEFAULT NULL,
    verified_at         DATETIME     DEFAULT NULL,
    remarks             VARCHAR(255) DEFAULT NULL,
    submitted_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_result_match     FOREIGN KEY (match_id)     REFERENCES matches (match_id) ON DELETE CASCADE,
    CONSTRAINT fk_result_submitter FOREIGN KEY (submitted_by) REFERENCES users (u_id),
    CONSTRAINT fk_result_verifier  FOREIGN KEY (verified_by)  REFERENCES users (u_id) ON DELETE SET NULL,

    CONSTRAINT chk_scores CHECK (score_team1 >= 0 AND score_team2 >= 0)
) ENGINE = InnoDB;

CREATE INDEX idx_result_status ON match_result (verification_status);


-- ---------------------------------------------------------------------
-- FR14 : team_rating  (the leaderboard)
--
-- One row per (tournament, team). Written ONLY by the verification step
-- in FR13, never edited by hand, so every number on the leaderboard can
-- be traced back to a verified match result.
--
-- Points: win = 3, draw = 1, loss = 0.
-- Club-wide standings = SUM(...) GROUP BY team_id across all rows.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS team_rating (
    rating_id      INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id  INT NOT NULL,
    team_id        INT NOT NULL,
    played         INT NOT NULL DEFAULT 0,
    won            INT NOT NULL DEFAULT 0,
    drawn          INT NOT NULL DEFAULT 0,
    lost           INT NOT NULL DEFAULT 0,
    rounds_for     INT NOT NULL DEFAULT 0,
    rounds_against INT NOT NULL DEFAULT 0,
    points         INT NOT NULL DEFAULT 0,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rating_tournament FOREIGN KEY (tournament_id)
        REFERENCES tournament (tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_rating_team FOREIGN KEY (team_id)
        REFERENCES team (team_id) ON DELETE CASCADE,
    CONSTRAINT uq_rating_tour_team UNIQUE (tournament_id, team_id)
) ENGINE = InnoDB;


-- ---------------------------------------------------------------------
-- FR15 : sponsor / transaction / income
--
-- The SRS normalises finance to 3NF (proposal page 5): a generic
-- TRANSACTION row holds what every money movement has in common, and
-- INCOME holds only what is specific to money coming in.
--
-- FR16 (expenses) writes to the SAME transaction table with
-- transaction_type = 'Expense' plus its own `expense` table. Neither of
-- us touches the other's file.
--
-- `sponsor` is created here because income has a foreign key to it. The
-- member who owns FR25 builds the sponsor directory screens on top of
-- this table - they do not need to re-create it.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sponsor (
    sponsor_id         INT AUTO_INCREMENT PRIMARY KEY,
    sponsor_name       VARCHAR(120)  NOT NULL,
    contact_info       VARCHAR(150)  DEFAULT NULL,
    sponsorship_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    contract_start     DATE          DEFAULT NULL,
    contract_end       DATE          DEFAULT NULL,
    notes              TEXT          DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS transaction (
    transaction_id   INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type VARCHAR(20)   NOT NULL,
    amount           DECIMAL(12,2) NOT NULL,
    category         VARCHAR(60)   DEFAULT NULL,
    description      VARCHAR(255)  DEFAULT NULL,
    transaction_date DATE          NOT NULL,
    recorded_by      INT           NOT NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_txn_user FOREIGN KEY (recorded_by) REFERENCES users (u_id),
    CONSTRAINT chk_amount_positive CHECK (amount > 0)
) ENGINE = InnoDB;

CREATE INDEX idx_txn_type_date ON transaction (transaction_type, transaction_date);

CREATE TABLE IF NOT EXISTS income (
    income_id      INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    sponsor_id     INT DEFAULT NULL,
    source_name    VARCHAR(120) NOT NULL,
    source_type    VARCHAR(20)  NOT NULL,

    CONSTRAINT fk_income_txn FOREIGN KEY (transaction_id)
        REFERENCES transaction (transaction_id) ON DELETE CASCADE,
    CONSTRAINT fk_income_sponsor FOREIGN KEY (sponsor_id)
        REFERENCES sponsor (sponsor_id) ON DELETE SET NULL,
    CONSTRAINT uq_income_txn UNIQUE (transaction_id)
) ENGINE = InnoDB;


-- =====================================================================
-- DEMO DATA
--
-- Written so it works with whatever users are already in the database -
-- it never inserts a user and never assumes a particular u_id.
-- =====================================================================

INSERT IGNORE INTO team (team_name, game_name, created_date) VALUES
 ('Phoenix Rising','Valorant', CURDATE()),
 ('Night Owls',    'Valorant', CURDATE()),
 ('Iron Wolves',   'Valorant', CURDATE()),
 ('Blue Comets',   'Valorant', CURDATE());

-- spread every approved user across the four teams
INSERT IGNORE INTO team_member (team_id, user_id, team_role, joined_date)
SELECT (u_id % 4) + 1, u_id, 'Player', CURDATE()
FROM users
WHERE status = 'Approved';

-- the lowest-numbered member of each team becomes its captain
UPDATE team t
SET captain_id = (
    SELECT MIN(tm.user_id) FROM team_member tm WHERE tm.team_id = t.team_id
)
WHERE captain_id IS NULL;

INSERT IGNORE INTO tournament (title, game_title, rules, status, start_date, end_date)
VALUES ('AIUB Valorant Cup 2026','Valorant','Best of 3. Knockout format.',
        'Ongoing', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY));

INSERT IGNORE INTO tournament_register (tournament_id, team_id, status)
SELECT 1, team_id, 'Approved' FROM team WHERE team_id <= 4;

INSERT IGNORE INTO matches (match_id, tournament_id, round_no, team1_id, team2_id, status) VALUES
 (1, 1, 1, 1, 2, 'Pending'),
 (2, 1, 1, 3, 4, 'Pending');

INSERT IGNORE INTO sponsor (sponsor_id, sponsor_name, contact_info, sponsorship_amount, contract_start, contract_end) VALUES
 (1, 'Ryans Computers','sales@ryans.com',   50000.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 180 DAY)),
 (2, 'Star Tech',      'info@startech.com', 30000.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 180 DAY));
