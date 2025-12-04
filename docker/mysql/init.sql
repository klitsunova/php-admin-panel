CREATE DATABASE IF NOT EXISTS `att_test_db`;
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `att_test_db`;

CREATE USER IF NOT EXISTS 'app_user'@'%' IDENTIFIED BY 'app_pass';
GRANT ALL PRIVILEGES ON `att_test_db`.* TO 'app_user'@'%';
FLUSH PRIVILEGES;