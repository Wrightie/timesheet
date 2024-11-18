-- Create database and user
CREATE DATABASE task_calendar_db;
CREATE USER 'calendar_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON task_calendar_db.* TO 'calendar_user'@'localhost';
FLUSH PRIVILEGES;

-- Use the new database
USE task_calendar_db;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    custom1 ENUM('Option1', 'Option2', 'Option3') DEFAULT 'Option1',
    custom2 VARCHAR(100),
    custom3 SET('OptionA', 'OptionB', 'OptionC') DEFAULT NULL
);

-- Calendar events table
CREATE TABLE calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT,
    title VARCHAR(255),
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    category_id INT,
    notes TEXT,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert example data
INSERT INTO categories (name, color) VALUES
('Work', '#ff0000'),
('Personal', '#00ff00'),
('Urgent', '#0000ff');

INSERT INTO tasks (name, description, custom1, custom2, custom3) VALUES
('Task Template 1', 'Description for Task 1', 'Option1', 'Dropdown1', 'OptionA,OptionB'),
('Task Template 2', 'Description for Task 2', 'Option2', 'Dropdown2', 'OptionC');

INSERT INTO calendar_events (task_id, title, start_time, end_time, category_id, notes) VALUES
(NULL, 'Sample Event 1', '2024-11-15 10:00:00', '2024-11-15 12:00:00', 1, 'Some notes here');
