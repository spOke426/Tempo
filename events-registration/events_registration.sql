-- Import this file in XAMPP > phpMyAdmin > Import.
CREATE DATABASE IF NOT EXISTS events_registration
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE events_registration;

-- The table/column names match the provided skills-test paper.
CREATE TABLE IF NOT EXISTS Events (
    evCode INT AUTO_INCREMENT PRIMARY KEY,
    evName VARCHAR(150) NOT NULL,
    evDate DATE NOT NULL,
    evVenue VARCHAR(150) NOT NULL,
    evRFee DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Participants (
    partID INT AUTO_INCREMENT PRIMARY KEY,
    evCode INT NOT NULL,
    partFName VARCHAR(100) NOT NULL,
    partLName VARCHAR(100) NOT NULL,
    partDRate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (evCode) REFERENCES Events(evCode) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Registration (
    regCode INT AUTO_INCREMENT PRIMARY KEY,
    partID INT NOT NULL UNIQUE,
    regDate DATE NOT NULL,
    regFPaid DECIMAL(10,2) NOT NULL,
    regPMode ENUM('Card','Cash') NOT NULL,
    FOREIGN KEY (partID) REFERENCES Participants(partID) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Sample events.
INSERT INTO Events (evCode, evName, evDate, evVenue, evRFee) VALUES
(101, 'Web Development Workshop', '2026-11-20', 'UC Main Computer Lab', 500.00),
(102, 'Sports Festival', '2026-11-25', 'UC Main Gym', 300.00),
(103, 'Cultural Night', '2026-12-02', 'UC Main Auditorium', 250.00);

-- Sample participants; partDRate means a peso discount, based on the sheet's formula.
INSERT INTO Participants (partID, evCode, partFName, partLName, partDRate) VALUES
(1001, 101, 'Anna', 'Santos', 100.00),
(1002, 101, 'Mark', 'Reyes', 50.00),
(1003, 101, 'Julia', 'Cruz', 0.00),
(1004, 102, 'Paolo', 'Garcia', 75.00),
(1005, 102, 'Mia', 'Flores', 25.00),
(1006, 103, 'John', 'Lim', 50.00),
(1007, 103, 'Sarah', 'Dela Cruz', 0.00);

-- Paid fees: evRFee - partDRate. Sorted in PHP newest to oldest.
INSERT INTO Registration (regCode, partID, regDate, regFPaid, regPMode) VALUES
(1, 1001, '2026-11-08', 400.00, 'Cash'),
(2, 1002, '2026-11-09', 450.00, 'Card'),
(3, 1004, '2026-11-09', 225.00, 'Cash'),
(4, 1005, '2026-11-10', 275.00, 'Card'),
(5, 1006, '2026-11-10', 200.00, 'Cash');
