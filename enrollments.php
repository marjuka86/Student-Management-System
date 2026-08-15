<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Delete Enrollment
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM ENROLLMENTS WHERE Enrollment_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: enrollments.php"); exit;
}

// Add Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_enrollment'])) {
    $stmt = $pdo->prepare("INSERT INTO ENROLLMENTS (Student_ID, Course_ID, Faculty_ID, Semester, Section) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['student_id'], $_POST['course_id'], $_POST['faculty_id'], $_POST['semester'], $_POST['section']]);
    header("Location: enrollments.php"); exit;
}

$students = $pdo->query("SELECT Student_ID, Full_Name FROM STUDENTS ORDER BY Student_ID DESC")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT * FROM COURSES ORDER BY Course_Code ASC")->fetchAll(PDO::FETCH_ASSOC);
$faculties = $pdo->query("SELECT Faculty_ID, Full_Name FROM FACULTIES ORDER BY Full_Name ASC")->fetchAll(PDO::FETCH_ASSOC);

$enrollments = $pdo->query("
    SELECT e.*, s.Full_Name as Student_Name, c.Course_Code, f.Full_Name as Faculty_Name 
    FROM ENROLLMENTS e 
    JOIN STUDENTS s ON e.Student_ID = s.Student_ID 
    JOIN COURSES c ON e.Course_ID = c.Course_ID 
    LEFT JOIN FACULTIES f ON e.Faculty_ID = f.Faculty_ID 
    ORDER BY e.Enrollment_ID DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>EWU Portal - Enrollments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { display: flex; min-height: 100vh; background: #f8fafc; color: #1e293b; }
        
        .sidebar { width: 260px; background: #002b49; color: white; padding: 25px 20px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .brand { text-align: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .brand i { font-size: 38px; color: #f59e0b; margin-bottom: 8px; }
        .brand h2 { font-size: 15px; font-weight: 800; color: #ffffff; }
        .brand p { font-size: 10px; color: #94a3b8; font-weight: 700; margin-top: 4px; text-transform: uppercase; }
        
        .nav-links { display: flex; flex-direction: column; gap: 6px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 16px; color: #cbd5e1; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13.5px; transition: all 0.2s; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); color: white; }
        .nav-item.active { background: #0f385c; color: white; border-left: 4px solid #f59e0b; }
        .nav-item.logout { color: #f87171; background: rgba(239, 68, 68, 0.1); margin-top: 15px; }
        .nav-item.logout:hover { background: #ef4444; color: white; }

        .main-content { flex: 1; padding: 35px 40px; overflow-y: auto; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .form-group { margin-bottom: 14px; } label { font-size: 12px; font-weight: 700; color: #002b49; display: block; margin-bottom: 5px; } input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
        .btn { background: #002b49; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn:hover { background: #0f385c; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; } th, td { padding: 12px 10px; text-align: left; font-size: 13px; } th { background: #002b49; color: white; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; } tr:nth-child(even) { background: #f8fafc; }
        .btn-delete { color: #ef4444; background: #fee2e2; padding: 6px 10px; border-radius: 5px; text-decoration: none; font-weight: 700; font-size: 12px; }
        .btn-delete:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand">
                <i class="fa-solid fa-graduation-cap"></i>
                <h2>EAST WEST UNIVERSITY</h2>
                <p>Student Management System</p>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="students.php" class="nav-item"><i class="fa-solid fa-user-graduate"></i> All Students</a>
                <a href="add_student.php" class="nav-item"><i class="fa-solid fa-user-plus"></i> Add Student</a>
                <a href="faculties.php" class="nav-item"><i class="fa-solid fa-chalkboard-user"></i> Faculties</a>
                <a href="courses.php" class="nav-item"><i class="fa-solid fa-book"></i> Courses</a>
                <a href="enrollments.php" class="nav-item active"><i class="fa-solid fa-clipboard-list"></i> Enrollments</a>
                <a href="results.php" class="nav-item"><i class="fa-solid fa-award"></i> Results & Grades</a>
                <a href="users_manage.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> User Accounts</a>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 20px;">Course Enrollments 📋</h1>
        
        <div class="grid">
            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Enroll Student in Course</h3>
                <form method="POST">
                    <input type="hidden" name="add_enrollment" value="1">
                    <div class="form-group">
                        <label>Student</label>
                        <select name="student_id" required>
                            <?php foreach($students as $s): ?>
                                <option value="<?php echo $s['Student_ID']; ?>"><?php echo $s['Student_ID'] . " - " . $s['Full_Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course_id" required>
                            <?php foreach($courses as $c): ?>
                                <?php $cName = $c['Course_Name'] ?? $c['Course_Title'] ?? $c['Title'] ?? ''; ?>
                                <option value="<?php echo $c['Course_ID']; ?>"><?php echo $c['Course_Code'] . ($cName ? " - " . $cName : ""); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Faculty Instructor</label>
                        <select name="faculty_id">
                            <option value="">None Assigned</option>
                            <?php foreach($faculties as $f): ?>
                                <option value="<?php echo $f['Faculty_ID']; ?>"><?php echo $f['Full_Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Semester</label><input type="text" name="semester" value="Summer 2026" required></div>
                    <div class="form-group"><label>Section</label><input type="text" name="section" value="1" required></div>
                    <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Submit Enrollment</button>
                </form>
            </div>

            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Enrolled Students List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Faculty</th>
                            <th>Sec</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($enrollments)): ?>
                            <?php foreach($enrollments as $e): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($e['Student_Name']); ?></strong><br><span style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($e['Student_ID']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($e['Course_Code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($e['Faculty_Name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($e['Section']); ?></td>
                                    <td>
                                        <a href="enrollments.php?delete_id=<?php echo $e['Enrollment_ID']; ?>" class="btn-delete" onclick="return confirm('Remove enrollment?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 15px;">No active enrollments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>