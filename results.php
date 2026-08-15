<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Delete Result
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM RESULTS WHERE Result_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: results.php"); exit;
}

// Add Result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_result'])) {
    $stmt = $pdo->prepare("INSERT INTO RESULTS (Enrollment_ID, Grade, GPA) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['enrollment_id'], $_POST['grade'], $_POST['gpa']]);
    header("Location: results.php"); exit;
}

$enrollments = $pdo->query("
    SELECT e.Enrollment_ID, s.Student_ID, s.Full_Name as Student_Name, c.Course_Code 
    FROM ENROLLMENTS e 
    JOIN STUDENTS s ON e.Student_ID = s.Student_ID 
    JOIN COURSES c ON e.Course_ID = c.Course_ID 
    ORDER BY e.Enrollment_ID DESC
")->fetchAll(PDO::FETCH_ASSOC);

$results = $pdo->query("
    SELECT r.*, s.Student_ID, s.Full_Name as Student_Name, c.Course_Code 
    FROM RESULTS r 
    JOIN ENROLLMENTS e ON r.Enrollment_ID = e.Enrollment_ID 
    JOIN STUDENTS s ON e.Student_ID = s.Student_ID 
    JOIN COURSES c ON e.Course_ID = c.Course_ID 
    ORDER BY r.Result_ID DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>EWU Portal - Results & Grades</title>
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
                <a href="enrollments.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Enrollments</a>
                <a href="results.php" class="nav-item active"><i class="fa-solid fa-award"></i> Results & Grades</a>
                <a href="users_manage.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> User Accounts</a>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 20px;">Results & Grades 🏅</h1>
        
        <div class="grid">
            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Publish Student Grade</h3>
                <form method="POST">
                    <input type="hidden" name="add_result" value="1">
                    <div class="form-group">
                        <label>Select Enrollment</label>
                        <select name="enrollment_id" required>
                            <?php foreach($enrollments as $en): ?>
                                <option value="<?php echo $en['Enrollment_ID']; ?>"><?php echo $en['Student_Name'] . " (" . $en['Course_Code'] . ")"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grade Letter</label>
                        <select name="grade" required>
                            <option>A+</option><option>A</option><option>A-</option>
                            <option>B+</option><option>B</option><option>B-</option>
                            <option>C+</option><option>C</option><option>F</option>
                        </select>
                    </div>
                    <div class="form-group"><label>GPA Point</label><input type="number" step="0.01" name="gpa" placeholder="e.g. 4.00" required></div>
                    <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Publish Grade</button>
                </form>
            </div>

            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Published Grades</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Grade</th>
                            <th>GPA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach($results as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['Student_Name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['Course_Code']); ?></td>
                                    <td><span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: 800;"><?php echo htmlspecialchars($r['Grade']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($r['GPA']); ?></strong></td>
                                    <td>
                                        <a href="results.php?delete_id=<?php echo $r['Result_ID']; ?>" class="btn-delete" onclick="return confirm('Delete grade?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 15px;">No grades published yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>