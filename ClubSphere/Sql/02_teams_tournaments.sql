USE clubsphere;

CREATE TABLE IF NOT EXISTS team (
    team_id      INT AUTO_INCREMENT PRIMARY KEY,
    team_name    VARCHAR(100) NOT NULL UNIQUE,
    game_name    VARCHAR(50)  NOT NULL,
    description  TEXT         DEFAULT NULL,
    status       ENUM('Active','Disbanded') NOT NULL DEFAULT 'Active',
    created_date DATE         NOT NULL,
    captain_id   INT          NOT NULL,
    CONSTRAINT fk_team_captain FOREIGN KEY (captain_id)
        REFERENCES users(u_id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS team_member (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id        INT NOT NULL,
    u_id           INT NOT NULL,
    team_role      ENUM('Captain','Player','Substitute','Coach') NOT NULL DEFAULT 'Player',
    joined_date    DATE DEFAULT NULL,
    status         ENUM('Invited','Accepted','Declined','Removed') NOT NULL DEFAULT 'Invited',
    CONSTRAINT uq_team_user UNIQUE (team_id, u_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES team(team_id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (u_id)    REFERENCES users(u_id)   ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS tournament (
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
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS tournament_register (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id   INT NOT NULL,
    team_id         INT NOT NULL,
    status          ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
    registered_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_tour_team UNIQUE (tournament_id, team_id),
    CONSTRAINT fk_tr_tour FOREIGN KEY (tournament_id) REFERENCES tournament(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_tr_team FOREIGN KEY (team_id)       REFERENCES team(team_id)             ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS matches (
    match_id      INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    round_no      INT NOT NULL,
    slot_no       INT NOT NULL,
    team1_id      INT DEFAULT NULL,
    team2_id      INT DEFAULT NULL,
    winner_id     INT DEFAULT NULL,
    match_time    DATETIME DEFAULT NULL,
    venue         VARCHAR(100) DEFAULT NULL,
    status        ENUM('Pending','Scheduled','Completed','Cancelled','Bye') NOT NULL DEFAULT 'Pending',
    scheduled_by  INT DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_round_slot UNIQUE (tournament_id, round_no, slot_no),
    KEY idx_match_time (match_time),
    CONSTRAINT fk_m_tour  FOREIGN KEY (tournament_id) REFERENCES tournament(tournament_id) ON DELETE CASCADE,
    CONSTRAINT fk_m_team1 FOREIGN KEY (team1_id)     REFERENCES team(team_id)  ON DELETE SET NULL,
    CONSTRAINT fk_m_team2 FOREIGN KEY (team2_id)     REFERENCES team(team_id)  ON DELETE SET NULL,
    CONSTRAINT fk_m_win   FOREIGN KEY (winner_id)    REFERENCES team(team_id)  ON DELETE SET NULL,
    CONSTRAINT fk_m_sched FOREIGN KEY (scheduled_by) REFERENCES users(u_id)    ON DELETE SET NULL
) ENGINE = InnoDB;


INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'Asterisk', 'Valorant', 'Asia-Pacific roster of the club.', '2026-01-10', u_id FROM users WHERE name = 'Nafisa';

INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'Revenant XSpark', 'Valorant', 'Second Valorant squad.', '2026-01-12', u_id FROM users WHERE name = 'Panda';

INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'Gods Reign', 'Valorant', 'Rookie squad from the Spring intake.', '2026-01-15', u_id FROM users WHERE name = 'Jotey';

INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'S8UL Esports', 'Valorant', 'Content plus competitive mixed roster.', '2026-01-18', u_id FROM users WHERE name = 'Bibek';

INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'DOT EXE', 'MLBB', 'MLBB main roster.', '2026-02-01', u_id FROM users WHERE name = 'Glowynowy';

INSERT IGNORE INTO team (team_name, game_name, description, created_date, captain_id)
SELECT 'The Rad Syndicate', 'MLBB', 'MLBB secondary roster.', '2026-02-03', u_id FROM users WHERE name = 'Sayma';

INSERT IGNORE INTO team_member (team_id, u_id, team_role, joined_date, status)
SELECT team_id, captain_id, 'Captain', created_date, 'Accepted' FROM team;

INSERT IGNORE INTO team_member (team_id, u_id, team_role, joined_date, status)
SELECT t.team_id, u.u_id, 'Player', '2026-01-11', 'Accepted'
FROM team t JOIN users u ON u.name = 'testmember'
WHERE t.team_name = 'Asterisk';

INSERT INTO tournament (title, game_title, rules, description, prize, region, status, start_date, end_date, created_by)
SELECT 'Valorant Championship 2026', 'Valorant',
       '5v5 single elimination. Best of 3 until the final, the final is best of 5. Roster lock 24 hours before the first match.',
       'Join the ultimate Valorant tournament and compete with top players. Exciting rewards await!',
       '$500', 'Bangladesh', 'Registration Open', '2026-05-05', '2026-05-11', u_id
FROM users
WHERE name = 'testadmin'
  AND NOT EXISTS (SELECT 1 FROM tournament WHERE title = 'Valorant Championship 2026');

INSERT INTO tournament (title, game_title, rules, description, prize, region, status, start_date, end_date, created_by)
SELECT 'MLBB Championship 2026', 'MLBB',
       '5v5 knockout. Best of 3 for every round.',
       'Join the ultimate MLBB tournament and compete with top players. Exciting rewards await!',
       '---', 'Bangladesh', 'Upcoming', '2026-06-10', '2026-06-15', u_id
FROM users
WHERE name = 'testadmin'
  AND NOT EXISTS (SELECT 1 FROM tournament WHERE title = 'MLBB Championship 2026');

INSERT IGNORE INTO tournament_register (tournament_id, team_id, status)
SELECT tr.tournament_id, t.team_id, 'Approved'
FROM tournament tr
JOIN team t ON t.team_name IN ('Asterisk', 'Revenant XSpark', 'Gods Reign', 'S8UL Esports')
WHERE tr.title = 'Valorant Championship 2026'
ORDER BY t.team_id;
