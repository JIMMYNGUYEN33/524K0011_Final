CREATE DATABASE IF NOT EXISTS e_wallet;
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
    balance DECIMAL(15,2) DEFAULT 0,
    is_first_login BOOLEAN DEFAULT TRUE,
    wrong_login_count INT DEFAULT 0,
    locked_until DATETIME NULL,
    is_permanently_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Cards (
    card_number VARCHAR(10) PRIMARY KEY,
    expiration_date VARCHAR(20),
    cvv VARCHAR(5),
    note TEXT
);

INSERT INTO Cards (card_number, expiration_date, cvv, note) VALUES
('111111', '10/10/2022', '411', 'Không giới hạn số lần nạp và số tiền mỗi lần nạp'),
('222222', '11/11/2022', '443', 'Không giới hạn số lần nạp nhưng chỉ được nạp tối đa 1 triệu/lần'),
('333333', '12/12/2022', '577', 'Thẻ hết tiền');


CREATE TABLE Transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(20) UNIQUE,
    user_id INT NOT NULL,
    receiver_id INT NULL,
    type ENUM('deposit', 'withdraw', 'transfer', 'buy_card') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    fee DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES Users(id) ON DELETE SET NULL
);


CREATE TABLE PhoneCards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    carrier VARCHAR(50) NOT NULL, ne
    card_code VARCHAR(10) NOT NULL UNIQUE, 
    amount INT NOT NULL, 
    FOREIGN KEY (transaction_id) REFERENCES Transactions(id) ON DELETE CASCADE
);


INSERT INTO Users (role, phone, email, password, full_name, status, is_first_login) VALUES
('admin', '0000000000', 'admin@ewallet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'verified', FALSE);