<?php
session_start();
include "db.php";

// ✅ 1. Security Check: Only Admin and Manager can see logs
if (!isset($_SESSION['user']) || (strtolower($_SESSION['user']['role_name']) != "admin" && strtolower($_SESSION['user']['role_name']) != "manager")) {
    header("Location: login.php");
    exit();
}

$user_role = strtolower($_SESSION['user']['role_name']);

// ✅ 2. Fetch Records: Joining tables for names and departments
$result = $conn->query("
    SELECT users.full_name, roles.role_name, users.department,
           attendance.check_in, attendance.check_out
    FROM attendance
    JOIN users ON attendance.user_id = users.id
    JOIN roles ON users.role_id = roles.id
    ORDER BY attendance.check_in DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            /* NEW BACKGROUND: Midnight Slate Professional Gradient */
            background: #0f172a;
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            background-attachment: fixed;
            color: #f8fafc; 
            min-height: 100vh;
        }
        
        .main-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.3); 
            background: white; 
            overflow: hidden;
        }
        
        /* Table Styling */
        .table thead th { 
            background-color: #f1f5f9; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 0.05em; 
            color: #475569; 
            border: none; 
            padding: 1.2rem 1rem;
        }
        .table td { vertical-align: middle; padding: 1.2rem 1rem; border-color: #f1f5f9; color: #334155; }

        /* Custom Badges */
        .badge-status { padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600; font-size: 0.75rem; }
        .bg-on-duty { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .bg-completed { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .back-link { text-decoration: none; color: #94a3b8; font-size: 0.9rem; margin-bottom: 1rem; display: inline-block; transition: 0.3s; }
        .back-link:hover { color: #ffffff; transform: translateX(-3px); }

        .btn-print { border-radius: 10px; font-weight: 500; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
        .btn-print:hover { background: white; color: #0f172a; }

        @media print {
            .back-link, .btn-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .main-card { box-shadow: none; border: 1px solid #ddd; }
            h2 { color: black !important; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    
    <a href="<?= $user_role ?>_dashboard.php" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #ffffff;">Attendance Records</h2>
            <p class="text-muted mb-0">Monitor real-time staff activity and history.</p>
        </div>
        <button class="btn btn-print shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print Report
        </button>
    </div>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)) { 
                            $isOnDuty = is_null($row['check_out']);
                            $dateOnly = date('M d, Y', strtotime($row['check_in']));
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold" style="color: #0f172a;"><?= htmlspecialchars($row['full_name']) ?></div>
                                <small class="text-muted"><?= ucfirst($row['role_name']) ?></small>
                            </td>
                            <td><span class="text-secondary fw-medium"><?= htmlspecialchars($row['department'] ?? 'N/A') ?></span></td>
                            <td><?= $dateOnly ?></td>
                            <td class="text-primary fw-bold"><?= date('h:i A', strtotime($row['check_in'])) ?></td>
                            <td>
                                <?= $isOnDuty ? '<span class="text-muted italic">--:--</span>' : '<span class="text-dark fw-bold">'.date('h:i A', strtotime($row['check_out'])).'</span>' ?>
                            </td>
                            <td class="text-center pe-4">
                                <?php if($isOnDuty): ?>
                                    <span class="badge-status bg-on-duty"><i class="fas fa-circle-notch fa-spin me-1"></i> On Duty</span>
                                <?php else: ?>
                                    <span class="badge-status bg-completed">Finished</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                        
                        <?php if($result->rowCount() == 0): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No attendance logs found in the database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>