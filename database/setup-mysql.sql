-- Run as root (Workbench: Local Instance Connection)
CREATE DATABASE IF NOT EXISTS LostFoundDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'laravel'@'localhost' IDENTIFIED BY '2103#Davit';
ALTER USER 'laravel'@'localhost' IDENTIFIED BY '2103#Davit';
GRANT ALL PRIVILEGES ON LostFoundDB.* TO 'laravel'@'localhost';
FLUSH PRIVILEGES;
