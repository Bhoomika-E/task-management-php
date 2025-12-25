-- taskhero.sql
CREATE DATABASE IF NOT EXISTS taskhero CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE taskhero;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  level INT DEFAULT 1,
  xp INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status ENUM('pending','completed') DEFAULT 'pending',
  due_date DATE,
  due_time TIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
