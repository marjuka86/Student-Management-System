<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$pdo = new PDO("mysql:host=localhost;dbname=ewu_sms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$studentData = null;
$myCourses = [];

if (!empty($_SESSION['student_id'])) {
    $stmt = $pdo->prepare("SELECT s.*, d.Department_Name FROM STUDENTS s LEFT JOIN DEPARTMENTS d ON s.Department_ID = d.Department_ID WHERE s.Student_ID = ?");
    $stmt->execute([$_SESSION['student_id']]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtCourses = $pdo->prepare("
        SELECT e.Semester, e.Section, c.Course_Code, c.Course_Name, c.Credit, f.Full_Name as Faculty_Name, r.Grade, r.GPA
        FROM ENROLLMENTS e
        JOIN COURSES c ON e.Course_ID = c.Course_ID
        LEFT JOIN FACULTIES f ON e.Faculty_ID = f.Faculty_ID
        LEFT JOIN RESULTS r ON e.Enrollment_ID = r.Enrollment_ID
        WHERE e.Student_ID = ?
    ");
    $stmtCourses->execute([$_SESSION['student_id']]);
    $myCourses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>EWU Portal - Student View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background: #f8fafc; color: #1e293b; min-height: 100vh; }
        .topbar { background: #002b49; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #f59e0b; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 18px; } .brand i { color: #f59e0b; font-size: 26px; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 13px; }
        .container { max-width: 950px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 5px solid #002b49; margin-bottom: 25px; }
        .profile-header { display: flex; align-items: center; gap: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 20px; }
        .avatar { width: 70px; height: 70px; background: #e0f2fe; color: #002b49; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-item { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-item span { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; } th, td { padding: 12px; text-align: left; font-size: 13px; } th { background: #002b49; color: white; } tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand"><i class="fa-solid fa-graduation-cap"></i><span>EAST WEST UNIVERSITY — STUDENT PORTAL</span></div>
        <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="container">
        <div class="card">
            <div class="profile-header">
                <div class="avatar"><i class="fa-solid fa-user-graduate"></i></div>
                <div>
                    <h2>Welcome, <?php echo htmlspecialchars($studentData['Full_Name'] ?? $_SESSION['username']); ?> 👋</h2>
                    <p style="color: #64748b; font-size: 13px; font-weight: 600;">Student ID: <strong style="color:#002b49;"><?php echo htmlspecialchars($_SESSION['student_id'] ?? 'N/A'); ?></strong></p>
                </div>
            </div>

            <?php if ($studentData): ?>
                <div class="info-grid">
                    <div class="info-item"><span>Department</span><strong><?php echo htmlspecialchars($studentData['Department_Name']); ?></strong></div>
                    <div class="info-item"><span>Email</span><strong><?php echo htmlspecialchars($studentData['Email']); ?></strong></div>
                    <div class="info-item"><span>Phone</span><strong><?php echo htmlspecialchars($studentData['Phone']); ?></strong></div>
                    <div class="info-item"><span>Blood Group</span><strong><?php echo htmlspecialchars($studentData['Blood_Group']); ?></strong></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-book-open" style="color: #f59e0b;"></i> My Enrolled Courses & Grades</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                        <th>Section</th>
                        <th>Faculty</th>
                        <th>Grade</th>
                        <th>GPA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($myCourses)): ?>
                        <?php foreach($myCourses as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['Course_Code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['Course_Name']); ?></td>
                                <td><?php echo htmlspecialchars($c['Credit']); ?></td>
                                <td>Sec <?php echo htmlspecialchars($c['Section']); ?></td>
                                <td><?php echo htmlspecialchars($c['Faculty_Name'] ?? 'TBA'); ?></td>
                                <td><span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: 800;"><?php echo htmlspecialchars($c['Grade'] ?? 'Pending'); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($c['GPA'] ?? 'N/A'); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No enrolled courses found for this semester.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>