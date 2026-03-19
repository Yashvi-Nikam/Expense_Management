-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS expense_management;

USE expense_management;

-- USERS TABLE
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    age INT,
    gender VARCHAR(10),
    occupation VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OCCUPATION DETAILS TABLE
CREATE TABLE occupation_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    field_name VARCHAR(100),
    field_value DECIMAL(10,2),
    field_text VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- INCOME TABLE
CREATE TABLE income (
    income_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- EXPENSES TABLE
CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- GOALS TABLE
CREATE TABLE goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    savings_amount DECIMAL(10,2) DEFAULT 0,
    goal_amount DECIMAL(10,2) NOT NULL,
    goal_purpose VARCHAR(100),
    start_month INT NOT NULL,          -- month number 1-12
    start_year INT NOT NULL,   
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);



-- MONTHLY HISTORY TABLE (for past months snapshots)
CREATE TABLE monthly_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_income DECIMAL(10,2) NOT NULL,
    total_expense DECIMAL(10,2) NOT NULL,
    savings DECIMAL(10,2) NOT NULL,
    goal_amount DECIMAL(10,2) NOT NULL,
    goal_purpose VARCHAR(255) NOT NULL,
    month TINYINT NOT NULL CHECK (month BETWEEN 1 AND 12),
    year YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month_year (user_id, month, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE monthly_breakdown (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    month INT,
    year INT,
    type VARCHAR(10), -- income / expense
    category VARCHAR(100),
    amount DECIMAL(10,2)
);
