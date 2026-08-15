<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$host = 'localhost'; $db = 'ewu_sms'; $user = 'root'; $pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Fetch metrics for summary cards
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM STUDENTS")->fetchColumn();
$totalCourses    = $pdo->query("SELECT COUNT(*) FROM COURSES")->fetchColumn();
$totalDepts      = $pdo->query("SELECT COUNT(*) FROM DEPARTMENTS")->fetchColumn();
$totalFaculties  = $pdo->query("SELECT COUNT(*) FROM FACULTIES")->fetchColumn();
$totalEnrollments= $pdo->query("SELECT COUNT(*) FROM ENROLLMENTS")->fetchColumn();
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM USERS")->fetchColumn();

// Fetch recently registered students
$recentStudents = $pdo->query("SELECT Student_ID, Full_Name, Email FROM STUDENTS ORDER BY Student_ID DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EWU Portal Dashboard</title>
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

        /* Main Content */
        .main-content { flex: 1; padding: 35px 40px; overflow-y: auto; }
        .header-bar h1 { font-size: 24px; color: #002b49; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .header-bar p { color: #64748b; font-size: 13px; font-weight: 500; margin-top: 4px; margin-bottom: 25px; }

        /* Summary Cards Grid */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 30px; }
        .metric-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #002b49; }
        .metric-card.students { border-color: #002b49; }
        .metric-card.courses { border-color: #f59e0b; }
        .metric-card.depts { border-color: #10b981; }
        .metric-card.faculties { border-color: #8b5cf6; }
        .metric-card.enrollments { border-color: #ec4899; }
        .metric-card.users { border-color: #3b82f6; }
        
        .metric-info h3 { font-size: 28px; font-weight: 800; color: #002b49; line-height: 1; }
        .metric-info p { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.5px; }
        .metric-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .students .metric-icon { background: #e0f2fe; color: #0284c7; }
        .courses .metric-icon { background: #fef3c7; color: #d97706; }
        .depts .metric-icon { background: #d1fae5; color: #059669; }
        .faculties .metric-icon { background: #ede9fe; color: #7c3aed; }
        .enrollments .metric-icon { background: #fce7f3; color: #db2777; }
        .users .metric-icon { background: #dbeafe; color: #2563eb; }

        /* Table Card */
        .table-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .table-card h2 { font-size: 16px; color: #002b49; font-weight: 800; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; text-align: left; font-size: 13px; }
        th { background: #002b49; color: white; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        tr:nth-child(even) { background: #f8fafc; }
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
                <a href="dashboard.php" class="nav-item active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="departments.php" class="nav-item"><i class="fa-solid fa-building-columns"></i> Departments</a>
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
        <div class="header-bar">
            <h1>Welcome to EWU Portal Dashboard 👋</h1>
            <p>Overview of academic metrics and recent system activity</p>
        </div>

        <div class="metrics-grid">
            <div class="metric-card students">
                <div class="metric-info">
                    <h3><?php echo $totalStudents; ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            </div>

            <div class="metric-card courses">
                <div class="metric-info">
                    <h3><?php echo $totalCourses; ?></h3>
                    <p>Total Courses</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-book"></i></div>
            </div>

            <div class="metric-card depts">
                <div class="metric-info">
                    <h3><?php echo $totalDepts; ?></h3>
                    <p>Departments</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-landmark"></i></div>
            </div>

            <div class="metric-card faculties">
                <div class="metric-info">
                    <h3><?php echo $totalFaculties; ?></h3>
                    <p>Faculties</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
            </div>

            <div class="metric-card enrollments">
                <div class="metric-info">
                    <h3><?php echo $totalEnrollments; ?></h3>
                    <p>Enrollments</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-clipboard-list"></i></div>
            </div>

            <div class="metric-card users">
                <div class="metric-info">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>System Users</p>
                </div>
                <div class="metric-icon"><i class="fa-solid fa-users-gear"></i></div>
            </div>
        </div>

        <div class="table-card">
            <h2><i class="fa-solid fa-clock-rotate-left" style="color: #f59e0b;"></i> Recently Registered Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($recentStudents)): ?>
                        <?php foreach($recentStudents as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['Student_ID']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['Full_Name']); ?></td>
                                <td><?php echo htmlspecialchars($student['Email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>