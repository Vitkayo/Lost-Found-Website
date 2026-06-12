-- Run as root in MySQL Workbench (Local Instance Connection)
-- Gives the Laravel app user access to your Lost & Found database

GRANT ALL PRIVILEGES ON LostFoundDB.* TO 'laravel'@'localhost';
FLUSH PRIVILEGES;
