-- Import this in phpMyAdmin (Import tab). It creates the database, 4 tables and sample data.
CREATE DATABASE IF NOT EXISTS clinic;
USE clinic;

CREATE TABLE PetOwner (
    petOwnerID    VARCHAR(20) PRIMARY KEY,
    petOwnerFName VARCHAR(50),
    petOwnerLName VARCHAR(50),
    petOwnerBDate DATE,
    petOwnerTelNo VARCHAR(20),
    isDeleted     TINYINT DEFAULT 0      -- 0 = active, 1 = deleted (logical deletion)
);

CREATE TABLE Veterinarian (
    vetID      VARCHAR(20) PRIMARY KEY,
    vetFName   VARCHAR(50),
    vetLName   VARCHAR(50),
    vetAddress VARCHAR(100),
    vetSpecial VARCHAR(50),
    isDeleted  TINYINT DEFAULT 0
);

CREATE TABLE Pet (
    petID      VARCHAR(20) PRIMARY KEY,
    petName    VARCHAR(50),
    petType    VARCHAR(30),
    petBreed   VARCHAR(50),
    petBDate   DATE,
    petOwnerID VARCHAR(20),
    isDeleted  TINYINT DEFAULT 0,
    FOREIGN KEY (petOwnerID) REFERENCES PetOwner(petOwnerID)
);

CREATE TABLE Consultation (
    consultID    VARCHAR(20) PRIMARY KEY,
    petID        VARCHAR(20),
    vetID        VARCHAR(20),
    consultDate  DATETIME,
    diagnoses    TEXT,
    prescription TEXT,
    isDeleted    TINYINT DEFAULT 0,
    FOREIGN KEY (petID) REFERENCES Pet(petID),
    FOREIGN KEY (vetID) REFERENCES Veterinarian(vetID)
);

INSERT INTO PetOwner VALUES
('OWN-001','Juan','Dela Cruz','1990-05-12','09170000001',0),
('OWN-002','Maria','Santos','1988-08-23','09170000002',0),
('OWN-003','Carlo','Ramos','1995-02-15','09170000003',0);

INSERT INTO Veterinarian VALUES
('VET-001','Ana','Reyes','Quezon City','General Practice',0),
('VET-002','Marco','Santos','Makati City','Surgery',0),
('VET-003','Lara','Cruz','Manila','General Practice',0);

INSERT INTO Pet VALUES
('PET-001','Bruno','Dog','Aspin','2021-06-10','OWN-001',0),
('PET-002','Luna','Cat','Puspin','2022-02-14','OWN-001',0),
('PET-003','Max','Dog','Beagle','2020-04-05','OWN-002',0),
('PET-004','Mochi','Cat','Persian','2023-01-18','OWN-003',0);

INSERT INTO Consultation VALUES
('CON-001','PET-001','VET-001','2026-09-01 09:00:00','Routine checkup','Vitamins',0),
('CON-002','PET-002','VET-001','2026-09-05 16:30:00','Skin allergy','Antihistamine',0),
('CON-003','PET-001','VET-002','2026-09-15 10:00:00','Leg injury','Pain reliever',0),
('CON-004','PET-003','VET-003','2026-10-02 13:30:00','Dental cleaning','None',0);
