<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message = '';
$error = '';

// Add Department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dept'])) {
    $dept_name = trim($_POST['department_name']);
    $dept_code = trim($_POST['department_code']);

    if (!empty($dept_name) && !empty($dept_code)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO DEPARTMENTS (Department_Name, Department_Code) VALUES (?, ?)");
            $stmt->execute([$dept_name, strtoupper($dept_code)]);
            $message = "Department added successfully!";
        } catch (PDOException $e) {
            $error = "Error: Department Code already exists or database issue!";
        }
    }
}

// Delete Department
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM DEPARTMENTS WHERE Department_ID = ?");
        $stmt->execute([$del_id]);
        header("Location: departments.php"); exit;
    } catch (PDOException $e) {
        $error = "Cannot delete department assigned to students/faculties!";
    }
}

$departments = $pdo->query("SELECT * FROM DEPARTMENTS ORDER BY Department_ID DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EWU Portal - Departments</title>
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
        .grid-container { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .form-group { margin-bottom: 16px; }
        label { font-size: 13px; font-weight: 700; color: #002b49; display: block; margin-bottom: 6px; }
        input { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
        .btn { background: #002b49; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn:hover { background: #0f385c; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f1f5f9; color: #002b49; font-weight: 700; }
        .btn-delete { color: #ef4444; text-decoration: none; font-size: 16px; }
        .btn-delete:hover { color: #dc2626; }
        .alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
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
                <a href="departments.php" class="nav-item active"><i class="fa-solid fa-building-columns"></i> Departments</a>
                <a href="students.php" class="nav-item"><i class="fa-solid fa-user-graduate"></i> All Students</a>
                <a href="add_student.php" class="nav-item"><i class="fa-solid fa-user-plus"></i> Add Student</a>
                <a href="faculties.php" class="nav-item"><i class="fa-solid fa-chalkboard-user"></i> Faculties</a>
                <a href="courses.php" class="nav-item"><i class="fa-solid fa-book"></i> Courses</a>
                <a href="enrollments.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Enrollments</a>
                <a href="results.php" class="nav-item"><i class="fa-solid fa-award"></i> Results & Grades</a>
                <a href="users_manage.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> User Accounts</a>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 25px;">Manage Departments 🏛️</h1>

        <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <div class="grid-container">
            <!-- Add Department Form -->
            <div class="card">
                <h3 style="margin-bottom: 15px; color: #002b49;">Add New Department</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Department Code</label>
                        <input type="text" name="department_code" placeholder="e.g. CSE" required>
                    </div>
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="department_name" placeholder="Computer Science & Engineering" required>
                    </div>
                    <button type="submit" name="add_dept" class="btn"><i class="fa-solid fa-plus"></i> Add Department</button>
                </form>
            </div>

            <!-- Department List -->
            <div class="card">
                <h3 style="margin-bottom: 15px; color: #002b49;">All Departments</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($departments as $d): ?>
                            <tr>
                                <td><?php echo $d['Department_ID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($d['Department_Code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($d['Department_Name']); ?></td>
                                <td>
                                    <a href="departments.php?delete_id=<?php echo $d['Department_ID']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this department?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>