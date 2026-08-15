<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$departments = $pdo->query("SELECT * FROM DEPARTMENTS")->fetchAll(PDO::FETCH_ASSOC);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $dept_id    = $_POST['department_id'];
    $blood      = $_POST['blood_group'] ?? 'A+';

    try {
        // Check if email exists; if so, make it unique dynamically to avoid duplicate key errors
        $chk = $pdo->prepare("SELECT COUNT(*) FROM STUDENTS WHERE Email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn() > 0) {
            $email = time() . "_" . $email;
        }

        // Insert Student directly without creating user credentials
        $stmt = $pdo->prepare("INSERT INTO STUDENTS (Student_ID, Full_Name, Email, Phone, Department_ID, Blood_Group) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $full_name, $email, $phone, $dept_id, $blood]);

        $message = "Student <strong>" . htmlspecialchars($full_name) . "</strong> registered successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EWU Portal - Add Student</title>
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
        .card { background: white; max-width: 650px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .form-group { margin-bottom: 16px; }
        label { font-size: 13px; font-weight: 700; color: #002b49; display: block; margin-bottom: 6px; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #002b49; }
        .btn { background: #002b49; color: white; border: none; padding: 13px; width: 100%; border-radius: 6px; font-weight: 700; font-size: 15px; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn:hover { background: #0f385c; }
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
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
                <a href="add_student.php" class="nav-item active"><i class="fa-solid fa-user-plus"></i> Add Student</a>
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
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 20px; text-align: center;">Register New Student ➕</h1>
        
        <div class="card">
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. 2024-1-60-100" required>
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="abc@std.ewubd.edu" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="01700000000" required>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" required>
                        <?php foreach($departments as $d): ?>
                            <option value="<?php echo $d['Department_ID']; ?>"><?php echo $d['Department_Name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Blood Group</label>
                    <input type="text" name="blood_group" placeholder="e.g. A+" value="A+">
                </div>

                <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Register Student</button>
            </form>
        </div>
    </div>

</body>
</html>