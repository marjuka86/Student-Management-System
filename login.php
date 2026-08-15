<?php
session_start();

$host = 'localhost';
$db   = 'ewu_sms';
$user = 'root';
$pass = '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $stmt = $pdo->prepare("SELECT * FROM USERS WHERE Username = ?");
            $stmt->execute([$username]);
            $userData = $stmt->fetch();

            if ($userData && ($password === $userData['Password'] || password_verify($password, $userData['Password']))) {
                $_SESSION['user_id']    = $userData['User_ID'] ?? 1;
                $_SESSION['username']   = $userData['Username'];
                $_SESSION['role']       = $userData['Role'];
                $_SESSION['student_id'] = $userData['Student_ID'] ?? null;

                if ($userData['Role'] === 'Admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: student_portal.php");
                }
                exit;
            } else {
                $error = "Invalid Username or Password!";
            }
        } catch (\PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EWU Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        body { background: linear-gradient(135deg, #002b49 0%, #0f385c 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: white; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); width: 100%; max-width: 420px; padding: 40px; border-top: 6px solid #f59e0b; }
        .brand { text-align: center; margin-bottom: 30px; }
        .brand i { font-size: 50px; color: #f59e0b; margin-bottom: 10px; }
        .brand h2 { color: #002b49; font-size: 19px; font-weight: 800; letter-spacing: 0.5px; }
        .brand p { color: #64748b; font-size: 13px; margin-top: 4px; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #002b49; font-weight: 700; font-size: 13px; margin-bottom: 8px; text-transform: uppercase; }
        .input-box { position: relative; }
        .input-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 16px; }
        .input-box input { width: 100%; padding: 12px 14px 12px 42px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
        .input-box input:focus { border-color: #002b49; box-shadow: 0 0 0 3px rgba(0, 43, 73, 0.1); }
        .btn-login { width: 100%; background: #002b49; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px; }
        .btn-login:hover { background: #0f385c; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .demo-credentials { background: #f8fafc; border-radius: 8px; padding: 12px; margin-top: 25px; font-size: 12px; color: #475569; border: 1px dashed #cbd5e1; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand">
            <i class="fa-solid fa-graduation-cap"></i>
            <h2>EAST WEST UNIVERSITY</h2>
            <p>Student Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="demo-credentials">
            <strong><i class="fa-solid fa-key" style="color: #f59e0b;"></i> Logins:</strong><br>
            Admin: <code>admin</code> | <code>123456</code><br>
            Student: <code>marjuka</code> | <code>123456</code>
        </div>
    </div>

</body>
</html>