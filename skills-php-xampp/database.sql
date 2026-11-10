-- Converted from the uploaded SQLite skills.db
-- Import this file using http://localhost/phpmyadmin/
CREATE DATABASE IF NOT EXISTS `skills_php` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skills_php`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idno` VARCHAR(10) NOT NULL UNIQUE,
  `lastname` VARCHAR(50) NOT NULL,
  `firstname` VARCHAR(50) NOT NULL,
  `course` VARCHAR(10) NOT NULL,
  `level` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- These are the original demo users, with passwords converted to secure hashes.
-- Each has the same demo password from the original database: hello123
INSERT IGNORE INTO `users` (`id`,`email`,`password`) VALUES (1,'alpha@uc.com','$2y$12$3XwvFX92LQI0N1TYbgIyceRgRc6AFUHQz38hElRkfxQHsi84Sgp.2');
INSERT IGNORE INTO `users` (`id`,`email`,`password`) VALUES (2,'bravo@uc.com','$2y$12$3vVloa/ayNCyu6hIx3i6leKqV4aQdicyerp.R631JkM63cxzgnxyK');
INSERT IGNORE INTO `users` (`id`,`email`,`password`) VALUES (3,'charlie@uc.com','$2y$12$pbHRItwXb23ZxBk83Y4fBOItfW0HMDWqGWVcXKfTYQd3ZMgn3D9si');

-- Original student records from the SQLite database:
INSERT IGNORE INTO `students` (`id`,`idno`,`lastname`,`firstname`,`course`,`level`) VALUES (2,'1001','bravo','yankee','bsit','3');
INSERT IGNORE INTO `students` (`id`,`idno`,`lastname`,`firstname`,`course`,`level`) VALUES (3,'1002','charlie','whiskey','bsit','2');
INSERT IGNORE INTO `students` (`id`,`idno`,`lastname`,`firstname`,`course`,`level`) VALUES (4,'1003','delta','victor','bsit','1');
INSERT IGNORE INTO `students` (`id`,`idno`,`lastname`,`firstname`,`course`,`level`) VALUES (6,'1004','xxxx','aaaa','bscpe','4');
