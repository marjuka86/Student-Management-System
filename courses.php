<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Detect exact column names in COURSES table dynamically
$cols = $pdo->query("DESCRIBE COURSES")->fetchAll(PDO::FETCH_COLUMN);
$titleCol = in_array('Course_Name', $cols) ? 'Course_Name' : (in_array('Title', $cols) ? 'Title' : 'Course_Title');
$creditCol = in_array('Credit_Hours', $cols) ? 'Credit_Hours' : (in_array('Credit', $cols) ? 'Credit' : 'Credits');

// Delete Course
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM COURSES WHERE Course_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: courses.php"); exit;
}

// Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $sql = "INSERT INTO COURSES (Course_Code, $titleCol, $creditCol, Department_ID) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['course_code'], $_POST['course_title'], $_POST['credits'], $_POST['department_id']]);
    header("Location: courses.php"); exit;
}

$courses = $pdo->query("SELECT c.*, d.Department_Code FROM COURSES c LEFT JOIN DEPARTMENTS d ON c.Department_ID = d.Department_ID ORDER BY c.Course_ID ASC")->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query("SELECT * FROM DEPARTMENTS")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>EWU Portal - Courses</title>
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
                <a href="courses.php" class="nav-item active"><i class="fa-solid fa-book"></i> Courses</a>
                <a href="enrollments.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Enrollments</a>
                <a href="results.php" class="nav-item"><i class="fa-solid fa-award"></i> Results & Grades</a>
                <a href="users_manage.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> User Accounts</a>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 20px;">Course Management 📚</h1>
        
        <div class="grid">
            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Add New Course</h3>
                <form method="POST">
                    <input type="hidden" name="add_course" value="1">
                    <div class="form-group"><label>Course Code</label><input type="text" name="course_code" placeholder="e.g. CSE110" required></div>
                    <div class="form-group"><label>Course Title</label><input type="text" name="course_title" placeholder="e.g. Programming I" required></div>
                    <div class="form-group"><label>Credits</label><input type="number" step="0.5" name="credits" placeholder="3.0" required></div>
                    <div class="form-group"><label>Department</label>
                        <select name="department_id" required>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['Department_ID']; ?>"><?php echo $d['Department_Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Save Course</button>
                </form>
            </div>

            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Course List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Credits</th>
                            <th>Dept</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($courses)): ?>
                            <?php foreach($courses as $c): ?>
                                <?php 
                                    $titleVal = $c['Course_Title'] ?? $c['Course_Name'] ?? $c['Title'] ?? 'N/A';
                                    $creditVal = $c['Credits'] ?? $c['Credit'] ?? $c['Credit_Hours'] ?? '3.0';
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($c['Course_Code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($titleVal); ?></td>
                                    <td><?php echo htmlspecialchars($creditVal); ?> Credits</td>
                                    <td><span style="background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 11px;"><?php echo htmlspecialchars($c['Department_Code'] ?? 'CSE'); ?></span></td>
                                    <td>
                                        <a href="courses.php?delete_id=<?php echo $c['Course_ID']; ?>" class="btn-delete" onclick="return confirm('Delete this course?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 15px;">No courses found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>