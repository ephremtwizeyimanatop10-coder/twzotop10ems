<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        
        $roleStmt = $conn->prepare("SELECT role_name FROM roles WHERE id = ?");
        $roleStmt->execute([$user['role_id']]);
        $role = $roleStmt->fetch();
        $_SESSION['user']['role_name'] = $role['role_name'];

        header("Location: " . $role['role_name'] . "_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            /* Using logo.jpeg as background with a professional dark overlay */
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), 
                        url('logo.jpeg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }

        /* --- Header Styling --- */
        .ems-header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            padding: 10px 50px;
            display: flex;
            justify-content: flex-start; /* Align logo to the left */
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
        }
        .header-logo {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .logo-text {
            color: white;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: 1px;
        }

        /* --- Login Card --- */
        .login-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        .btn-login {
            background: #2563eb;
            border: none;
            padding: 14px;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<header class="ems-header">
    <a href="#" class="header-logo">
        <img src="logo.jpeg" alt="Logo" class="logo-img">
        <span class="logo-text">EMS<span class="text-primary">.</span></span>
    </a>
    </header>

<div class="login-wrapper">
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark mb-1">Employee Portal</h3>
            <p class="text-muted small">Enter your credentials to manage your work.</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger border-0 small py-2 text-center mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-at text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 bg-light" placeholder="email@company.com" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login w-100 shadow-sm">
                LOG IN
            </button>
        </form>
    </div>
</div>

</body>
</html>
