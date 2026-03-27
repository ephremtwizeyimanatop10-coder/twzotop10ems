<?php
session_start();
include "db.php";

// ✅ 1. Security Check: Only Admin and Manager can see reports
// Nashyizemo strtolower() kugira ngo bidateza ikibazo kuri role_name
if (!isset($_SESSION['user']) || (strtolower($_SESSION['user']['role_name']) != "admin" && strtolower($_SESSION['user']['role_name']) != "manager")) {
    header("Location: login.php");
    exit();
}

$user_role = strtolower($_SESSION['user']['role_name']);

// ✅ 2. Total Employees Count
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// ✅ 3. Active Sessions Today (Checked in but not out)
$activeNow = $conn->query("SELECT COUNT(*) FROM attendance WHERE check_out IS NULL AND DATE(check_in) = CURDATE()")->fetchColumn();

// ✅ 4. Department Breakdown Data
$deptData = $conn->query("SELECT department, COUNT(*) as count FROM users GROUP BY department")->fetchAll(PDO::FETCH_ASSOC);

// ✅ 5. Recent Attendance Log
$recentLogs = $conn->query("
    SELECT users.full_name, attendance.check_in, attendance.check_out 
    FROM attendance 
    JOIN users ON attendance.user_id = users.id 
    ORDER BY attendance.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .report-card { border: none; border-radius: 16px; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .stat-box { padding: 1.5rem; border-radius: 12px; color: white; }
        .bg-gradient-blue { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .bg-gradient-green { background: linear-gradient(135deg, #10b981, #059669); }
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.75rem; color: #64748b; }
        .back-btn { transition: all 0.3s ease; text-decoration: none; }
        .back-btn:hover { background: #e2e8f0; transform: translateX(-3px); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">System Reports</h2>
            <p class="text-muted small">Overview of company-wide attendance and workforce.</p>
        </div>
        
        <a href="<?= $user_role ?>_dashboard.php" class="btn btn-light border back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="stat-box bg-gradient-blue shadow">
                <small class="opacity-75">Total Workforce</small>
                <h1 class="fw-bold mb-0"><?= $totalUsers ?> Employees</h1>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-box bg-gradient-green shadow">
                <small class="opacity-75">Currently Active (On-Site Today)</small>
                <h1 class="fw-bold mb-0"><?= $activeNow ?> Active</h1>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Department Distribution</h5>
                    <ul class="list-group list-group-flush">
                        <?php if(empty($deptData)): ?>
                            <p class="text-muted small">No department data found.</p>
                        <?php endif; ?>
                        <?php foreach($deptData as $dept): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <?= htmlspecialchars($dept['department'] ?? 'Other') ?>
                            <span class="badge bg-primary rounded-pill"><?= $dept['count'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card report-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Latest Attendance Activity</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Check In Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentLogs)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">No logs found for today.</td></tr>
                                <?php endif; ?>
                                <?php foreach($recentLogs as $log): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <i class="far fa-user-circle me-2"></i><?= htmlspecialchars($log['full_name']) ?>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?= date('h:i A', strtotime($log['check_in'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if(is_null($log['check_out'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success px-3">
                                                <i class="fas fa-sign-in-alt me-1"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-3">
                                                <i class="fas fa-sign-out-alt me-1"></i> Logged Out
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>