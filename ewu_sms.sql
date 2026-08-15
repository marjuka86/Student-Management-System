-- 1. Create Database
CREATE DATABASE IF NOT EXISTS ewu_sms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ewu_sms;

-- Disable Foreign Key checks for clean drop
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS RESULTS;
DROP TABLE IF EXISTS ENROLLMENTS;
DROP TABLE IF EXISTS USERS;
DROP TABLE IF EXISTS COURSES;
DROP TABLE IF EXISTS FACULTIES;
DROP TABLE IF EXISTS STUDENTS;
DROP TABLE IF EXISTS DEPARTMENTS;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Create DEPARTMENTS Table
CREATE TABLE DEPARTMENTS (
    Department_ID INT AUTO_INCREMENT PRIMARY KEY,
    Department_Name VARCHAR(100) NOT NULL,
    Department_Code VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 3. Create STUDENTS Table
CREATE TABLE STUDENTS (
    Student_ID VARCHAR(20) PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Date_of_Birth DATE NULL,
    Gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    Blood_Group VARCHAR(5) DEFAULT 'A+',
    Phone VARCHAR(20) NULL,
    Email VARCHAR(100) NOT NULL,
    Address TEXT NULL,
    Department_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES DEPARTMENTS(Department_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Create FACULTIES Table
CREATE TABLE FACULTIES (
    Faculty_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Phone VARCHAR(20) NULL,
    Designation VARCHAR(50) DEFAULT 'Lecturer',
    Department_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES DEPARTMENTS(Department_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Create COURSES Table
CREATE TABLE COURSES (
    Course_ID INT AUTO_INCREMENT PRIMARY KEY,
    Course_Code VARCHAR(20) NOT NULL UNIQUE,
    Course_Name VARCHAR(100) NOT NULL,
    Credit DECIMAL(3,1) NOT NULL DEFAULT 3.0,
    Department_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES DEPARTMENTS(Department_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Create ENROLLMENTS Table
CREATE TABLE ENROLLMENTS (
    Enrollment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID VARCHAR(20) NOT NULL,
    Course_ID INT NOT NULL,
    Faculty_ID INT NULL,
    Semester VARCHAR(20) NOT NULL DEFAULT 'Summer 2026',
    Section VARCHAR(10) NOT NULL DEFAULT '1',
    FOREIGN KEY (Student_ID) REFERENCES STUDENTS(Student_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Course_ID) REFERENCES COURSES(Course_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Faculty_ID) REFERENCES FACULTIES(Faculty_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 7. Create RESULTS Table
CREATE TABLE RESULTS (
    Result_ID INT AUTO_INCREMENT PRIMARY KEY,
    Enrollment_ID INT NOT NULL UNIQUE,
    Grade VARCHAR(5) NOT NULL,
    GPA DECIMAL(3,2) NOT NULL,
    FOREIGN KEY (Enrollment_ID) REFERENCES ENROLLMENTS(Enrollment_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 8. Create USERS Table
CREATE TABLE USERS (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Admin', 'Faculty', 'Student') NOT NULL DEFAULT 'Student',
    Student_ID VARCHAR(20) NULL,
    FOREIGN KEY (Student_ID) REFERENCES STUDENTS(Student_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- INSERT INITIAL SAMPLE DATA
-- =========================================================

-- Sample Departments
INSERT INTO DEPARTMENTS (Department_ID, Department_Name, Department_Code) VALUES
(1, 'Computer Science & Engineering', 'CSE'),
(2, 'Business Administration', 'BBA'),
(3, 'Electrical & Electronic Engineering', 'EEE');

-- Sample Admin User (Login: admin / 123456)
INSERT INTO USERS (Username, Password, Role) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe11e6pD832D0315Z6A55030225102', 'Admin');

-- Sample Students
INSERT INTO STUDENTS (Student_ID, Full_Name, Date_of_Birth, Gender, Blood_Group, Phone, Email, Address, Department_ID) VALUES
('2024-1-60-100', 'John Doe', '2002-05-15', 'Male', 'A+', '01700000001', 'john@std.ewubd.edu', 'Dhaka, Bangladesh', 1),
('2024-1-60-101', 'Jane Smith', '2003-08-20', 'Female', 'B+', '01800000002', 'jane@std.ewubd.edu', 'Dhaka, Bangladesh', 1);

-- Sample Faculties
INSERT INTO FACULTIES (Faculty_ID, Full_Name, Email, Phone, Designation, Department_ID) VALUES
(1, 'Dr. Ahmed Khan', 'ahmed@ewubd.edu', '01900000001', 'Professor', 1),
(2, 'Ms. Sarah Rahman', 'sarah@ewubd.edu', '01900000002', 'Senior Lecturer', 1);

-- Sample Courses
INSERT INTO COURSES (Course_ID, Course_Code, Course_Name, Credit, Department_ID) VALUES
(1, 'CSE110', 'Programming I', 3.0, 1),
(2, 'CSE302', 'Database Systems', 3.0, 1),
(3, 'FIN101', 'Principles of Finance', 3.0, 2);

-- Sample Enrollments
INSERT INTO ENROLLMENTS (Enrollment_ID, Student_ID, Course_ID, Faculty_ID, Semester, Section) VALUES
(1, '2024-1-60-100', 2, 1, 'Summer 2026', '1'),
(2, '2024-1-60-101', 1, 2, 'Summer 2026', '2');

-- Sample Results
INSERT INTO RESULTS (Result_ID, Enrollment_ID, Grade, GPA) VALUES
(1, 1, 'A+', 4.00);