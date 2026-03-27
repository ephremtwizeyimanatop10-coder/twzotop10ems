<?php
session_start();
include "db.php";

// ✅ 1. Security Check
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role_name']) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$status = ""; 

// ✅ 2. Fetch roles
$roles = $conn->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// ✅ 3. Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];
    $department = trim($_POST['department']);

    if (!empty($name) && !empty($email) && !empty($_POST['password'])) {
        try {
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->execute([$email]);
            
            if ($check_email->rowCount() > 0) {
                $message = "Iyi email isanzwe ikoreshwa!";
                $status = "danger";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role_id, department, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $email, $password, $role_id, $department]);
                
                $message = "Umukozi yongewe neza muri system!";
                $status = "success";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $status = "danger";
        }
    } else {
        $message = "Tuzuye neza imyanya yose!";
        $status = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee | EMS Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            /* New Professional Dark Background */
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .add-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            color: white;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white !important;
            padding: 12px;
            border-radius: 12px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }
        .form-select option { background: #1e293b; color: white; }
        
        .btn-add {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            padding: 14px;
            font-weight: 700;
            border-radius: 12px;
            transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4); }
        
        .back-link { text-decoration: none; color: rgba(255, 255, 255, 0.6); transition: 0.3s; }
        .back-link:hover { color: #3b82f6; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="mb-4">
                <a href="manage_employees.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <div class="card add-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-user-plus fs-2"></i>
                    </div>
                    <h2 class="fw-bold">Onboard Staff</h2>
                    <p class="text-white-50 small">Create a new secure account for the employee</p>
                </div>

                <?php if($message): ?>
                    <div class="alert alert-<?= $status ?> border-0 bg-opacity-25 text-white shadow-sm" role="alert">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold opacity-75">FULL NAME</label>
                        <input class="form-control" name="name" placeholder="E.g. Niyodusaba Doks" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold opacity-75">EMAIL ADDRESS</label>
                        <input class="form-control" name="email" type="email" placeholder="name@company.com" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold opacity-75">PASSWORD</label>
                            <input class="form-control" name="password" type="password" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold opacity-75">ROLE</label>
                            <select class="form-select" name="role_id" required>
                                <?php foreach($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= ucfirst($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold opacity-75">DEPARTMENT</label>
                        <select class="form-select" name="department" required>
                            <option value="" disabled selected>Choose department...</option>
                            <option value="IT">IT & Systems</option>
                            <option value="HR">Human Resources</option>
                            <option value="Finance">Finance</option>
                            <option value="Nursing">Nursing / Clinical</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-add shadow-sm">
                        Confirm Registration
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>