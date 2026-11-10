-- Employee Attendance Monitoring System for XAMPP / phpMyAdmin
-- WARNING: Importing this script again resets these 3 tables and their data.
CREATE DATABASE IF NOT EXISTS attendance_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_db;

-- Delete child tables first because foreign keys link the tables.
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS departments;

CREATE TABLE departments (
    depCode INT PRIMARY KEY,
    depName VARCHAR(100) NOT NULL,
    depHead VARCHAR(100) NOT NULL,
    depTelNo VARCHAR(30) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE employees (
    empID INT PRIMARY KEY,
    depCode INT NOT NULL,
    empFName VARCHAR(100) NOT NULL,
    empLName VARCHAR(100) NOT NULL,
    empRPH DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (depCode) REFERENCES departments(depCode)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE attendance (
    attRN INT AUTO_INCREMENT PRIMARY KEY,
    empID INT NOT NULL,
    attDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attTimeIn DATETIME NOT NULL,
    attTimeOut DATETIME NULL,
    attStat VARCHAR(20) NOT NULL DEFAULT 'Not Cancelled',
    FOREIGN KEY (empID) REFERENCES employees(empID)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- Sample departments based on the example in the provided photo.
INSERT INTO departments (depCode, depName, depHead, depTelNo) VALUES
(10, 'Accounting', 'Julius', '2557777'),
(11, 'Warehouse', 'Caesar', '2551111'),
(12, 'HR', 'Gretchen', '2552222'),
(13, 'IT', 'Gilbert', '2553333'),
(14, 'Production', 'Troy', '2554444'),
(15, 'QA', 'Evangeline', '2550000'),
(16, 'Marketing', 'Jessica', '2558888');

-- Sample employees based on the example in the provided photo.
INSERT INTO employees (empID, depCode, empFName, empLName, empRPH) VALUES
(234, 10, 'Neil', 'Basabe', 500.00),
(235, 10, 'Heubert', 'Feralino', 300.00),
(236, 11, 'Denis', 'Durano', 200.00),
(237, 13, 'Jennifer', 'Amores', 500.00),
(238, 15, 'Catherine', 'Carumba', 350.00),
(239, 15, 'Leah', 'Ybanez', 345.00),
(240, 14, 'Leo', 'Bermudez', 290.00);

-- Six example shifts of 9 hours each = 54 hours, total pay ₱27,000 for empID 234.
INSERT INTO attendance (empID, attDate, attTimeIn, attTimeOut, attStat) VALUES
(234, '2024-04-15 17:20:00', '2024-04-15 08:20:00', '2024-04-15 17:20:00', 'Not Cancelled'),
(234, '2024-04-16 17:20:00', '2024-04-16 08:20:00', '2024-04-16 17:20:00', 'Not Cancelled'),
(234, '2024-04-17 17:20:00', '2024-04-17 08:20:00', '2024-04-17 17:20:00', 'Not Cancelled'),
(234, '2024-04-18 17:20:00', '2024-04-18 08:20:00', '2024-04-18 17:20:00', 'Not Cancelled'),
(234, '2024-04-19 17:20:00', '2024-04-19 08:20:00', '2024-04-19 17:20:00', 'Not Cancelled'),
(234, '2024-04-20 17:20:00', '2024-04-20 08:20:00', '2024-04-20 17:20:00', 'Not Cancelled');
