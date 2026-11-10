-- Import this in phpMyAdmin (Import tab). It creates the database, 4 tables and sample data.
CREATE DATABASE IF NOT EXISTS election;
USE election;

CREATE TABLE Positions (
    posID          INT AUTO_INCREMENT PRIMARY KEY,
    posName        VARCHAR(50),
    numOfPositions INT,                          -- how many winners/votes allowed (Senator = 12)
    posStat        VARCHAR(10) DEFAULT 'open'    -- open / closed
);

CREATE TABLE Voters (
    voterID    VARCHAR(20) PRIMARY KEY,
    voterPass  VARCHAR(50),
    voterFName VARCHAR(50),
    voterMName VARCHAR(50),
    voterLName VARCHAR(50),
    voterStat  VARCHAR(10) DEFAULT 'active',     -- active / inactive
    voted      CHAR(1) DEFAULT 'N'               -- Y / N
);

CREATE TABLE Candidates (
    candID    INT AUTO_INCREMENT PRIMARY KEY,
    candFName VARCHAR(50),
    candMName VARCHAR(50),
    candLName VARCHAR(50),
    posID     INT,
    candStat  VARCHAR(10) DEFAULT 'active',      -- active / inactive
    FOREIGN KEY (posID) REFERENCES Positions(posID)
);

CREATE TABLE Votes (
    posID   INT,
    voterID VARCHAR(20),
    candID  INT,
    FOREIGN KEY (posID)   REFERENCES Positions(posID),
    FOREIGN KEY (voterID) REFERENCES Voters(voterID),
    FOREIGN KEY (candID)  REFERENCES Candidates(candID)
);

INSERT INTO Positions (posName, numOfPositions, posStat) VALUES
('President', 1, 'open'),
('Vice President', 1, 'open'),
('Senator', 3, 'open');

INSERT INTO Voters VALUES
('V001','1234','Alex','','Dela Cruz','active','N'),
('V002','1234','Sam','','Garcia','active','N'),
('V003','1234','Jamie','','Lopez','active','Y'),
('V004','1234','Andrea','','Tan','active','Y'),
('V005','1234','Carlo','','Reyes','inactive','N');

INSERT INTO Candidates (candFName, candMName, candLName, posID, candStat) VALUES
('Isabel','','Reyes',1,'active'),
('Gabriel','','Santos',1,'active'),
('Rafael','','Cruz',1,'active'),
('Elena','','Navarro',2,'active'),
('Marco','','Villanueva',2,'active'),
('Sofia','','Mendoza',3,'active'),
('Andres','','Castillo',3,'active'),
('Clara','','Ramos',3,'active'),
('Miguel','','Torres',3,'active'),
('Lucia','','Flores',3,'active');

-- Votes already cast by V003 and V004
INSERT INTO Votes (posID, voterID, candID) VALUES
(1,'V003',1),(2,'V003',4),(3,'V003',6),(3,'V003',7),
(1,'V004',2),(2,'V004',4),(3,'V004',6),(3,'V004',8),(3,'V004',9);
