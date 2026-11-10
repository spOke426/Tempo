-- Import this file in phpMyAdmin (http://localhost/phpmyadmin).
-- Database and 7 main tables from the school activity.
CREATE DATABASE IF NOT EXISTS uc_intramurals CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uc_intramurals;

CREATE TABLE IF NOT EXISTS Registration (
    userName VARCHAR(30) PRIMARY KEY,                 -- student/employee ID
    password VARCHAR(255) NOT NULL,                   -- hashed PHP password
    `role` ENUM('admin', 'athlete', 'tournament_manager', 'coach', 'dean') NOT NULL
);
-- confirmPassword is checked during registration and deliberately NOT stored.

CREATE TABLE IF NOT EXISTS Department (
    deptID INT AUTO_INCREMENT PRIMARY KEY,
    depName VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS TournamentManager (
    userName VARCHAR(30) PRIMARY KEY,
    fName VARCHAR(80) NOT NULL,
    lName VARCHAR(80) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    deptID INT NOT NULL,
    FOREIGN KEY (userName) REFERENCES Registration(userName) ON DELETE CASCADE,
    FOREIGN KEY (deptID) REFERENCES Department(deptID)
);

CREATE TABLE IF NOT EXISTS Coach (
    userName VARCHAR(30) PRIMARY KEY,
    fName VARCHAR(80) NOT NULL,
    lName VARCHAR(80) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    deptID INT NOT NULL,
    FOREIGN KEY (userName) REFERENCES Registration(userName) ON DELETE CASCADE,
    FOREIGN KEY (deptID) REFERENCES Department(deptID)
);

CREATE TABLE IF NOT EXISTS Dean (
    userName VARCHAR(30) PRIMARY KEY,
    fName VARCHAR(80) NOT NULL,
    lName VARCHAR(80) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    deptID INT NOT NULL,
    FOREIGN KEY (userName) REFERENCES Registration(userName) ON DELETE CASCADE,
    FOREIGN KEY (deptID) REFERENCES Department(deptID)
);

CREATE TABLE IF NOT EXISTS `Event` (
    eventID INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('Athletic', 'Cultural', 'Academic') NOT NULL,
    eventName VARCHAR(120) NOT NULL,
    noOfParticipant INT NOT NULL,
    tournamentManager VARCHAR(30) NOT NULL,
    FOREIGN KEY (tournamentManager) REFERENCES TournamentManager(userName)
);

CREATE TABLE IF NOT EXISTS AthletesProfile (
    idNum VARCHAR(30) PRIMARY KEY,                   -- registration ID
    eventID INT NOT NULL,
    deptID INT NOT NULL,
    lastName VARCHAR(80) NOT NULL,
    firstName VARCHAR(80) NOT NULL,
    middleInit VARCHAR(10),
    course VARCHAR(80) NOT NULL,
    `year` VARCHAR(20) NOT NULL,
    civilStatus VARCHAR(30) NOT NULL,
    gender VARCHAR(30) NOT NULL,
    birthdate DATE NOT NULL,
    contactNo VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    coachID VARCHAR(30) NULL,
    deanID VARCHAR(30) NULL,
    FOREIGN KEY (idNum) REFERENCES Registration(userName) ON DELETE CASCADE,
    FOREIGN KEY (eventID) REFERENCES `Event`(eventID),
    FOREIGN KEY (deptID) REFERENCES Department(deptID),
    FOREIGN KEY (coachID) REFERENCES Coach(userName),
    FOREIGN KEY (deanID) REFERENCES Dean(userName)
);

-- Sample departments for testing. No hard-coded account passwords.
INSERT IGNORE INTO Department (deptID, depName) VALUES
(1, 'College of Computer Studies (CCS)'),
(2, 'College of Engineering (COE)'),
(3, 'College of Business Administration (CBA)'),
(4, 'College of Teacher Education (CTE)'),
(5, 'College of Criminal Justice (CCJ)');
