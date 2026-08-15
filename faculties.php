<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); 
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Delete Faculty Logic
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM FACULTIES WHERE Faculty_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: faculties.php"); 
    exit;
}

// Add Faculty Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    $stmt = $pdo->prepare("INSERT INTO FACULTIES (Full_Name, Email, Phone, Designation, Department_ID) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['designation'], $_POST['department_id']]);
    header("Location: faculties.php"); 
    exit;
}

$faculties = $pdo->query("SELECT f.*, d.Department_Code FROM FACULTIES f LEFT JOIN DEPARTMENTS d ON f.Department_ID = d.Department_ID ORDER BY f.Faculty_ID ASC")->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query("SELECT * FROM DEPARTMENTS")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EWU Portal - Faculties</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { display: flex; min-height: 100vh; background: #f8fafc; color: #1e293b; }
        
        /* Sidebar */
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
        .form-group { margin-bottom: 14px; } 
        label { font-size: 12px; font-weight: 700; color: #002b49; display: block; margin-bottom: 5px; } 
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
        
        .btn { background: #002b49; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #0f385c; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; } 
        th, td { padding: 12px 10px; text-align: left; font-size: 13px; } 
        th { background: #002b49; color: white; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; } 
        tr:nth-child(even) { background: #f8fafc; }
        
        .btn-delete { color: #ef4444; background: #fee2e2; padding: 6px 10px; border-radius: 5px; text-decoration: none; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
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
                <a href="faculties.php" class="nav-item active"><i class="fa-solid fa-chalkboard-user"></i> Faculties</a>
                <a href="courses.php" class="nav-item"><i class="fa-solid fa-book"></i> Courses</a>
                <a href="enrollments.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Enrollments</a>
                <a href="results.php" class="nav-item"><i class="fa-solid fa-award"></i> Results & Grades</a>
                <a href="users_manage.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> User Accounts</a>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-size: 24px; color: #002b49; font-weight: 800; margin-bottom: 20px;">Faculty Management 👨‍🏫</h1>
        
        <div class="grid">
            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Add New Faculty</h3>
                <form method="POST">
                    <input type="hidden" name="add_faculty" value="1">
                    <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                    <div class="form-group"><label>Designation</label><input type="text" name="designation" placeholder="e.g. Lecturer / Associate Professor"></div>
                    <div class="form-group"><label>Department</label>
                        <select name="department_id" required>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['Department_ID']; ?>"><?php echo $d['Department_Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Save Faculty</button>
                </form>
            </div>

            <div class="card">
                <h3 style="color: #002b49; font-size: 16px; margin-bottom: 15px;">Faculty List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Dept</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($faculties)): ?>
                            <?php $sl = 1; ?>
                            <?php foreach($faculties as $f): ?>
                                <tr>
                                    <td>#<?php echo $sl++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($f['Full_Name']); ?></strong><br><span style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($f['Email']); ?></span></td>
                                    <td><?php echo htmlspecialchars($f['Designation'] ?? 'N/A'); ?></td>
                                    <td><span style="background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 11px;"><?php echo htmlspecialchars($f['Department_Code'] ?? 'CSE'); ?></span></td>
                                    <td>
                                        <a href="faculties.php?delete_id=<?php echo $f['Faculty_ID']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Are you sure you want to delete this faculty member?');">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 15px;">No faculty members found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>