DROP DATABASE IF EXISTS e_wallet;
CREATE DATABASE e_wallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE e_wallet;

CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'user') DEFAULT 'user',

    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    full_name VARCHAR(100),
    dob DATE,
    address TEXT,
    id_front VARCHAR(255),
    id_back VARCHAR(255),

    status ENUM('pending', 'verified', 'disabled', 'waiting_update') DEFAULT 'pending',
    balance DECIMAL(15,2) DEFAULT 10000000.00,

    is_first_login BOOLEAN DEFAULT TRUE,

    wrong_login_count INT DEFAULT 0,
    abnormal_login_count INT DEFAULT 0,
    locked_until DATETIME NULL,
    is_permanently_locked BOOLEAN DEFAULT FALSE,
    permanently_locked_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Cards (
    card_number VARCHAR(10) PRIMARY KEY,
    expiration_date VARCHAR(20) NOT NULL,
    cvv VARCHAR(5) NOT NULL,
    note TEXT
);

INSERT INTO Cards (card_number, expiration_date, cvv, note) VALUES
('111111', '10/10/2022', '411', 'Unlimited deposit count and amount'),
('222222', '11/11/2022', '443', 'Unlimited deposit count but maximum 1,000,000 VND per deposit'),
('333333', '12/12/2022', '577', 'Card is out of money');

CREATE TABLE Transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(20) UNIQUE,

    user_id INT NOT NULL,
    receiver_id INT NULL,

    type ENUM('deposit', 'withdraw', 'transfer', 'buy_card') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    fee DECIMAL(15,2) DEFAULT 0,
    fee_payer ENUM('sender', 'receiver') NULL,

    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    note TEXT,

    approved_by INT NULL,
    approved_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES Users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES Users(id) ON DELETE SET NULL
);

CREATE TABLE PhoneCards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    carrier ENUM('Viettel', 'Mobifone', 'Vinaphone') NOT NULL,
    card_code VARCHAR(10) NOT NULL UNIQUE,
    amount INT NOT NULL,

    FOREIGN KEY (transaction_id) REFERENCES Transactions(id) ON DELETE CASCADE
);

CREATE TABLE OTP_Codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,

    otp_hash VARCHAR(255) NOT NULL,
    type ENUM('transfer', 'reset_password') NOT NULL,

    expires_at DATETIME NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

INSERT INTO Users (
    role,
    phone,
    email,
    password,
    full_name,
    status,
    balance,
    is_first_login
) VALUES (
    'admin',
    '0000000000',
    'admin@ewallet.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System Administrator',
    'verified',
    0,
    FALSE
);
