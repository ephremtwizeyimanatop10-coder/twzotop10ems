<?php
session_start();
include "db.php";

// ✅ Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] == 'employee') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role_name'];

// ✅ Logic: Admin sees all, Manager sees their Dept
if ($role == 'admin') {
    $stmt = $conn->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id");
    $stmt->execute();
} else {
    $dept = $user['department'];
    $stmt = $conn->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE department = :dept");
    $stmt->execute(['dept' => $dept]);
}

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Directory | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .page-header { background: white; padding: 2rem 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 3rem; }
        
        /* Profile Card Styling */
        .user-card {
            background: white;
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            text-align: center;
            padding: 2rem;
            height: 100%;
        }
        .user-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
        }

        .role-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .badge-admin { background: #fee2e2; color: #991b1b; }
        .badge-manager { background: #fef3c7; color: #92400e; }
        .badge-employee { background: #dcfce7; color: #166534; }

        .contact-btn {
            margin-top: 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold mb-1">Team Directory</h1>
            <p class="text-muted mb-0">
                <?= $role == 'admin' ? 'Viewing all employees across the organization.' : 'Viewing members of the <strong>' . htmlspecialchars($dept) . '</strong> department.' ?>
            </p>
        </div>
        <a href="<?= $role ?>_dashboard.php" class="btn btn-outline-secondary btn-sm">Return to Dashboard</a>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <?php foreach($result as $row): 
            // Get Initials for Avatar
            $initials = strtoupper(substr($row['full_name'], 0, 1));
            
            // Badge Logic
            $badgeClass = "badge-employee";
            if($row['role_name'] == 'admin') $badgeClass = "badge-admin";
            if($row['role_name'] == 'manager') $badgeClass = "badge-manager";
        ?>
        <div class="col-md-4 col-lg-3">
            <div class="user-card">
                <div class="avatar-circle">
                    <?= $initials ?>
                </div>
                
                <span class="role-badge <?= $badgeClass ?> mb-2 d-inline-block">
                    <?= $row['role_name'] ?>
                </span>
                
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['full_name']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($row['department'] ?? 'General') ?></p>
                
                <hr class="my-3 opacity-25">
                
                <a href="mailto:<?= $row['email'] ?>" class="contact-btn">
                    <i class="far fa-envelope"></i> Send Email
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>