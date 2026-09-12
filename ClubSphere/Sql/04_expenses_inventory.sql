USE clubsphere;

CREATE TABLE IF NOT EXISTS expense (
    expense_id     INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT         NOT NULL,
    expense_type   VARCHAR(50) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,

    CONSTRAINT fk_expense_txn FOREIGN KEY (transaction_id)
        REFERENCES transaction (transaction_id) ON DELETE CASCADE,
    CONSTRAINT uq_expense_txn UNIQUE (transaction_id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS inventory_item (
    item_id        INT AUTO_INCREMENT PRIMARY KEY,
    item_name      VARCHAR(100) NOT NULL,
    category       VARCHAR(50)  NOT NULL,
    item_condition VARCHAR(20)  NOT NULL DEFAULT 'Good',
    status         VARCHAR(20)  NOT NULL DEFAULT 'Available',
    assigned_to    VARCHAR(100)          DEFAULT NULL
) ENGINE = InnoDB;

INSERT IGNORE INTO inventory_item (item_id, item_name, category, item_condition, status, assigned_to) VALUES
 (1, 'HyperX Cloud II Headset', 'Peripherals', 'Good', 'Available',   NULL),
 (2, 'Logitech G Pro Keyboard', 'Peripherals', 'New',  'In Use',      'Phoenix Rising'),
 (3, 'ASUS TUF 144Hz Monitor',  'Displays',    'Fair', 'Maintenance', NULL);
