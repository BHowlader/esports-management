-- ============================================================
--  ClubSphere - Esports Club Management System
--  Group 8 | Section H | CSC 3215 Web Technologies
--
--  Import this ONE file. It builds every table the whole project
--  needs: the users table for FR1-FR5 and the five tables for
--  FR6-FR10 (teams, tournaments, knockout brackets).
--
--  NOTE: the ER diagram calls the match table "MATCH", but MATCH is a
--        reserved keyword in MySQL, so the table is named `matches`.
-- ============================================================

CREATE DATABASE IF NOT EXISTS clubsphere;
USE clubsphere;

DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS tournament_register;
DROP TABLE IF EXISTS tournament;
DROP TABLE IF EXISTS team_member;
DROP TABLE IF EXISTS team;
DROP TABLE IF EXISTS users;


-- ------------------------------------------------------------
-- USERS                                      FR1 - FR5
--   `name` is the username, so it is UNIQUE - loginUser() looks a
--   user up by it.
--   role and status have defaults because registerUser() only inserts
--   name, uni_id, email_id and password. A new account is always a
--   Pending Member until an Admin approves it on adminMembers.php.
-- ------------------------------------------------------------
CREATE TABLE users (
    u_id        INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    uni_id      VARCHAR(20)  NOT NULL UNIQUE,
    email_id    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('Admin','Moderator','Member') NOT NULL DEFAULT 'Member',
    game_type   VARCHAR(50)  DEFAULT NULL,
    ranking     VARCHAR(50)  DEFAULT NULL,
    social_link VARCHAR(255) DEFAULT NULL,
    status      ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------
-- TEAM                   FR6  (a Member creates a team)
-- ------------------------------------------------------------
CREATE TABLE team (
    team_id      INT AUTO_INCREMENT PRIMARY KEY,
    team_name    VARCHAR(100) NOT NULL UNIQUE,
    game_name    VARCHAR(50)  NOT NULL,
    description  TEXT         DEFAULT NULL,
    status       ENUM('Active','Disbanded') NOT NULL DEFAULT 'Active',
    created_date DATE         NOT NULL,
    captain_id   INT          NOT NULL,
    CONSTRAINT fk_team_captain FOREIGN KEY (captain_id)
        REFERENCES users(u_id) ON DELETE CASCADE
);


-- ------------------------------------------------------------
-- TEAM_MEMBER      FR6 (invite)  /  FR7 (Moderator edits roster)
-- ------------------------------------------------------------
CREATE TABLE team_member (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id        INT NOT NULL,
    u_id           INT NOT NULL,
    team_role      ENUM('Captain','Player','Substitute','Coach') NOT NULL DEFAULT 'Player',
    joined_date    DATE DEFAULT NULL,
    status         ENUM('Invited','Accepted','Declined','Removed') NOT NULL DEFAULT 'Invited',
    CONSTRAINT uq_team_user UNIQUE (team_id, u_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES team(team_id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (u_id)    REFERENCES users(u_id)   ON DELETE CASCADE
);


-- ------------------------------------------------------------
-- TOURNAMENT             FR8  (an Admin creates a tournament)
-- ------------------------------------------------------------
CREATE TABLE tournament (
    tournament_id INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(120) NOT NULL,
    game_title    VARCHAR(50)  NOT NULL,
    rules         TEXT         DEFAULT NULL,
    description   TEXT         DEFAULT NULL,
    prize         VARCHAR(50)  DEFAULT NULL,
    region        VARCHAR(50)  DEFAULT 'Bangladesh',
    status        ENUM('Upcoming','Registration Open','Ongoing','Completed','Cancelled')
                  NOT NULL DEFAULT 'Registration Open',
    start_date    DATE NOT NULL,
    end_date      DATE NOT NULL,
    created_by    INT  NOT NULL,
    CONSTRAINT fk_tour_admin FOREIGN KEY (created_by)
        REFERENCES users(u_id) ON DELETE CASCADE
);


-- ------------------------------------------------------------
-- TOURNAMENT_REGISTER    FR9  (a Member enters their own team)
-- ------------------------------------------------------------
CREATE TABLE tournament_register (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id   INT NOT NULL,
    team_id         INT NOT NULL,
    status          ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
    registered_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_tour_team UNIQUE (tournament_id, team_id),
    CONSTRAINT fk_tr_tour FOREIGN KEY (tournament_id) REFERENCES tournament(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_tr_team FOREIGN KEY (team_id)       REFERENCES team(team_id)             ON DELETE CASCADE
);


-- ------------------------------------------------------------
-- MATCHES (= MATCH)      FR10 (automatic knockout bracket)
--   round_no and slot_no are what make the bracket a tree: the
--   winner of slot n in round r moves into slot ceil(n/2) of round r+1.
-- ------------------------------------------------------------
CREATE TABLE matches (
    match_id      INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    round_no      INT NOT NULL,
    slot_no       INT NOT NULL,
    team1_id      INT DEFAULT NULL,
    team2_id      INT DEFAULT NULL,
    winner_id     INT DEFAULT NULL,
    match_time    DATETIME DEFAULT NULL,
    status        ENUM('Pending','Scheduled','Completed','Bye') NOT NULL DEFAULT 'Pending',
    CONSTRAINT uq_round_slot UNIQUE (tournament_id, round_no, slot_no),
    CONSTRAINT fk_m_tour  FOREIGN KEY (tournament_id) REFERENCES tournament(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_m_team1 FOREIGN KEY (team1_id)  REFERENCES team(team_id) ON DELETE SET NULL,
    CONSTRAINT fk_m_team2 FOREIGN KEY (team2_id)  REFERENCES team(team_id) ON DELETE SET NULL,
    CONSTRAINT fk_m_win   FOREIGN KEY (winner_id) REFERENCES team(team_id) ON DELETE SET NULL
);


-- ============================================================
--  SAMPLE DATA
--  Every password is 12345678 (8 characters, so it also passes the
--  "at least 8 characters" rule in registerControls.php).
--
--     mint    Admin      12345678
--     wei     Moderator  12345678
--     nafisa  Member     12345678    <- captain of Asterisk
--
--  The members below nafisa exist so the six teams have captains and
--  the invite list is not empty. All are Approved so they can log in.
-- ============================================================
INSERT INTO users (name, uni_id, email_id, password, role, game_type, ranking, status) VALUES
('mint',   '23-51517-1', 'mint@clubsphere.com',   '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Admin',     'Valorant', 'Radiant',   'Approved'),
('wei',    '23-54213-3', 'wei@clubsphere.com',    '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Moderator', 'Valorant', 'Immortal',  'Approved'),
('nafisa', '23-51826-2', 'nafisa@clubsphere.com', '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'Valorant', 'Diamond',   'Approved'),
('hasib',  '23-51793-2', 'hasib@clubsphere.com',  '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'Valorant', 'Ascendant', 'Approved'),
('seam',   '23-51158-1', 'seam@clubsphere.com',   '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'MLBB',     'Mythic',    'Approved'),
('jotey',  '23-52958-2', 'jotey@clubsphere.com',  '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'Valorant', 'Platinum',  'Approved'),
('bibek',  '23-54606-3', 'bibek@clubsphere.com',  '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'Valorant', 'Gold',      'Approved'),
('sayma',  '23-52022-2', 'sayma@clubsphere.com',  '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'MLBB',     'Legend',    'Approved'),
('moon',   '22-47872-2', 'moon@clubsphere.com',   '$2y$12$7sPMRE/fRmxxL2SADAsvguyLA.k7k5s5./F2yRnshrHvQ0BHblHaO', 'Member',    'Valorant', 'Silver',    'Approved');

-- the eight teams from the Figma "Registered Teams" screen
INSERT INTO team (team_name, game_name, description, created_date, captain_id) VALUES
('Asterisk',          'Valorant', 'Asia-Pacific roster of the club.',       '2026-01-10', 3),
('Revenant XSpark',   'Valorant', 'Second Valorant squad.',                 '2026-01-12', 4),
('Gods Reign',        'Valorant', 'Rookie squad from the Spring intake.',   '2026-01-15', 6),
('S8UL Esports',      'Valorant', 'Content plus competitive mixed roster.', '2026-01-18', 7),
('DOT EXE',           'MLBB',     'MLBB main roster.',                      '2026-02-01', 5),
('The Rad Syndicate', 'MLBB',     'MLBB secondary roster.',                 '2026-02-03', 8);

INSERT INTO team_member (team_id, u_id, team_role, joined_date, status) VALUES
(1, 3, 'Captain', '2026-01-10', 'Accepted'),
(1, 9, 'Player',  '2026-01-11', 'Accepted'),
(2, 4, 'Captain', '2026-01-12', 'Accepted'),
(3, 6, 'Captain', '2026-01-15', 'Accepted'),
(4, 7, 'Captain', '2026-01-18', 'Accepted'),
(5, 5, 'Captain', '2026-02-01', 'Accepted'),
(6, 8, 'Captain', '2026-02-03', 'Accepted');

-- the two tournaments from the Figma tournament screens
INSERT INTO tournament (title, game_title, rules, description, prize, region, status, start_date, end_date, created_by) VALUES
('Valorant Championship 2026', 'Valorant',
 '5v5 single elimination. Best of 3 until the final, the final is best of 5. Roster lock 24 hours before the first match.',
 'Join the ultimate Valorant tournament and compete with top players. Exciting rewards await!',
 '$500', 'Bangladesh', 'Registration Open', '2026-05-05', '2026-05-11', 1),
('MLBB Championship 2026', 'MLBB',
 '5v5 knockout. Best of 3 for every round.',
 'Join the ultimate MLBB tournament and compete with top players. Exciting rewards await!',
 '---', 'Bangladesh', 'Upcoming', '2026-06-10', '2026-06-15', 1);

INSERT INTO tournament_register (tournament_id, team_id, status) VALUES
(1, 1, 'Approved'),
(1, 2, 'Approved'),
(1, 3, 'Approved'),
(1, 4, 'Approved');
