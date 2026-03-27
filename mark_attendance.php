<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$message = "";

// ✅ 1. Check current status: Is there an open session today?
$statusStmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = :uid AND check_out IS NULL ORDER BY id DESC LIMIT 1");
$statusStmt->execute(['uid' => $user_id]);
$currentSession = $statusStmt->fetch();

// ✅ 2. Handle Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['check_in']) && !$currentSession) {
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, check_in) VALUES (:uid, NOW())");
        $stmt->execute(['uid' => $user_id]);
        $message = "✅ Success! You have checked in.";
    }

    if (isset($_POST['check_out']) && $currentSession) {
        $stmt = $conn->prepare("UPDATE attendance SET check_out = NOW() WHERE id = :id");
        $stmt->execute(['id' => $currentSession['id']]);
        $message = "👋 Goodbye! You have checked out.";
    }
    
    // Refresh page to update status view
    header("Refresh:1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .attendance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        .status-header {
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .btn-attendance {
            height: 120px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .status-dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-active { background-color: #28a745; box-shadow: 0 0 10px #28a745; }
        .status-inactive { background-color: #dc3545; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            
            <div class="mb-4">
                <a href="employee_dashboard.php" class="text-decoration-none text-muted">← Back to Dashboard</a>
            </div>

            <div class="card attendance-card">
                <div class="status-header">
                    <h5 class="text-muted mb-2">Current Status</h5>
                    <?php if ($currentSession): ?>
                        <div class="h4 fw-bold text-success">
                            <span class="status-dot status-active"></span> CLOCKED IN
                        </div>
                        <p class="small text-muted mb-0">Started at: <?= date('H:i A', strtotime($currentSession['check_in'])) ?></p>
                    <?php else: ?>
                        <div class="h4 fw-bold text-danger">
                            <span class="status-dot status-inactive"></span> CLOCKED OUT
                        </div>
                        <p class="small text-muted mb-0">Ready to start your shift?</p>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4">
                    <?php if($message): ?>
                        <div class="alert alert-info text-center py-2"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-12">
                            <button name="check_in" class="btn btn-success btn-attendance w-100 shadow-sm" <?= $currentSession ? 'disabled' : '' ?>>
                                <i class="fas fa-sign-in-alt fa-2x"></i>
                                CHECK IN
                            </button>
                        </div>
                        <div class="col-12">
                            <button name="check_out" class="btn btn-danger btn-attendance w-100 shadow-sm" <?= !$currentSession ? 'disabled' : '' ?>>
                                <i class="fas fa-sign-out-alt fa-2x"></i>
                                CHECK OUT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center mt-4 text-muted small">Server Time: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</div>

</body>
</html>