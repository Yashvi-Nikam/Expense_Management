-- Create database if it does not exist
/*CREATE DATABASE IF NOT EXISTS expense_management;

USE expense_management;*/

-- USERS TABLE
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
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
    detail_id SERIAL PRIMARY KEY,
    user_id INT,
    field_name VARCHAR(100),
    field_value DECIMAL(10,2),
    field_text VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_occ_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- INCOME TABLE
CREATE TABLE income (
    income_id SERIAL PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_income_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- EXPENSES TABLE
CREATE TABLE expenses (
    expense_id SERIAL PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expense_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- GOALS TABLE
CREATE TABLE goals (
    goal_id SERIAL PRIMARY KEY,
    user_id INT,
    savings_amount DECIMAL(10,2) DEFAULT 0,
    goal_amount DECIMAL(10,2) NOT NULL,
    goal_purpose VARCHAR(100),
    start_month INT NOT NULL CHECK (start_month BETWEEN 1 AND 12),
    start_year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- MONTHLY HISTORY TABLE
CREATE TABLE monthly_history (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    total_income DECIMAL(10,2) NOT NULL,
    total_expense DECIMAL(10,2) NOT NULL,
    savings DECIMAL(10,2) NOT NULL,
    goal_amount DECIMAL(10,2) NOT NULL,
    goal_purpose VARCHAR(255) NOT NULL,
    month SMALLINT NOT NULL CHECK (month BETWEEN 1 AND 12),
    year SMALLINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT unique_user_month_year UNIQUE (user_id, month, year)
);

-- MONTHLY BREAKDOWN TABLE
CREATE TABLE monthly_breakdown (
    id SERIAL PRIMARY KEY,
    user_id INT,
    month SMALLINT,
    year SMALLINT,
    type VARCHAR(10), -- income / expense
    category VARCHAR(100),
    amount DECIMAL(10,2)
);
