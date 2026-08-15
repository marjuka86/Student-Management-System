# 🎓 Student Management System - East West University (EWU)

**Course:** CSE302 - Database Systems  
**Institution:** East West University  
**Technology Stack:** PHP (PDO), MySQL, HTML5, CSS3, JavaScript, FontAwesome, XAMPP  

---

## 📌 Project Overview
This project is a web-based **Student Management System** designed specifically within the context of **East West University (EWU)**. The primary goal of the application is to streamline academic and administrative record-keeping, including managing students, departments, faculty members, course offerings, course enrollments, and academic grades through a clear Graphical User Interface (GUI).

---

## 🚀 Key Features & CRUD Operations
The application fully supports complete **CRUD (Create, Read, Update, Delete)** operations across all primary database entities:

* **Department Management:** Add new academic departments, view all departments, and manage structural records.
* **Student Record Management:** Register new students with unique system IDs, display student profiles, update info, and remove records.
* **Faculty Directory:** Maintain faculty profiles with designations and departmental assignments.
* **Course Administration:** Add and manage offered courses, credit units, and course codes.
* **Enrollment System:** Process student course enrollments per semester and section.
* **Grade & Result Tracking:** Record, compute, and display GPA and course letter grades.

---

## 🔐 User Roles & Access Control
The system implements **Role-Based Access Control (RBAC)** to ensure data security and proper authorization:

* **Admin Role:** Complete access to all CRUD modules, system user account management, course setup, and grade assignments.
* **Student Role:** Restricted read-only access to view personal profile details, enrolled courses, and academic performance/results.

---

## 🗄️ Database Architecture & Entities
The relational schema design follows standard normalization principles and strictly enforces foreign key constraints across the following 7 tables:
1. `DEPARTMENTS` (Department_ID, Department_Name, Department_Code)
2. `STUDENTS` (Student_ID, Full_Name, Date_of_Birth, Gender, Blood_Group, Phone, Email, Address, Department_ID)
3. `FACULTIES` (Faculty_ID, Full_Name, Email, Phone, Designation, Department_ID)
4. `COURSES` (Course_ID, Course_Code, Course_Name, Credit, Department_ID)
5. `ENROLLMENTS` (Enrollment_ID, Student_ID, Course_ID, Faculty_ID, Semester, Section)
6. `RESULTS` (Result_ID, Enrollment_ID, Grade, GPA)
7. `USERS` (User_ID, Username, Password, Role, Student_ID)

---

## ⚙️ Installation & Setup Guide

1. **Clone/Download Project:**
   Download this repository and place the folder into your local web server environment (e.g., `C:\xampp\htdocs\ewu_sms` or `D:\xampp\htdocs\ewu_sms`).

2. **Database Import:**
   - Start **Apache** and **MySQL** modules from the XAMPP Control Panel.
   - Open browser and navigate to `http://localhost/phpmyadmin/`.
   - Create a new database named `ewu_sms` (or let the script auto-create it).
   - Go to the **Import** tab, select the provided `ewu_sms.sql` file from the repository, and click **Import**.

3. **Run Application:**
   Open your web browser and access:
   `http://localhost/ewu_sms/login.php`

4. **Default Credentials:**
   - **Username:** `admin`
   - **Password:** `123456`
