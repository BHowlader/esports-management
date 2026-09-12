USE clubsphere;

ALTER TABLE users ENGINE = InnoDB;

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

INSERT IGNORE INTO sponsor (sponsor_id, sponsor_name, contact_info, sponsorship_amount, contract_start, contract_end) VALUES
 (1, 'Ryans Computers','sales@ryans.com',   50000.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 180 DAY)),
 (2, 'Star Tech',      'info@startech.com', 30000.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 180 DAY));
