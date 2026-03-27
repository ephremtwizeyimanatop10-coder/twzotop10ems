<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$query = "SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$my_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusStyles($status) {
    switch (strtolower($status)) {
        case 'approved': return ['bg' => '#ecfdf5', 'text' => '#065f46', 'dot' => '#10b981'];
        case 'rejected': return ['bg' => '#fef2f2', 'text' => '#991b1b', 'dot' => '#ef4444'];
        default: return ['bg' => '#fffbeb', 'text' => '#92400e', 'dot' => '#f59e0b'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-dark: #0f172a;
            --accent-blue: #3b82f6;
            --slate-50: #f8fafc;
            --slate-200: #e2e8f0;
            --slate-600: #475569;
        }

        body { 
            background-color: #f1f5f9; 
            font-family: 'Inter', sans-serif; 
            color: var(--primary-dark);
            -webkit-font-smoothing: antialiased;
        }

        /* Professional Header */
        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--slate-200);
            padding: 1.5rem 0;
            margin-bottom: 2.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Container & Cards */
        .content-wrapper { max-width: 1000px; margin: 0 auto; padding: 0 15px; }
        
        .main-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: white; 
            overflow: hidden;
        }

        /* Table Design */
        .table { margin-bottom: 0; }
        .table thead th {
            background-color: var(--slate-50);
            color: var(--slate-600);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--slate-200);
        }
        
        .table tbody tr { transition: background-color 0.2s; }
        .table tbody tr:hover { background-color: #f8fafc; }
        
        .table tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--slate-200);
            vertical-align: middle;
        }

        /* Modern Status Pills */
        .status-pill { 
            padding: 6px 12px; 
            border-radius: 9999px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* Buttons */
        .btn-dashboard {
            background: var(--primary-dark);
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-dashboard:hover {
            background: #1e293b;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .date-subtext {
            color: var(--slate-600);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<header class="glass-header shadow-sm">
    <div class="content-wrapper d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Leave History</h4>
        </div>
        <a href="employee_dashboard.php" class="btn-dashboard">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
    </div>
</header>

<div class="content-wrapper">
    <div class="main-card mb-5">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Application Details</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_requests)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/slate/calendar.svg" style="width: 120px;" class="mb-3">
                                <h5 class="text-muted fw-normal">No requests found</h5>
                                <a href="request_leave.php" class="btn btn-primary btn-sm mt-2 px-4 rounded-pill">Create New Request</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($my_requests as $req): 
                            $styles = getStatusStyles($req['status']);
                            $start = strtotime($req['start_date']);
                            $end = strtotime($req['end_date']);
                            $days = round(($end - $start) / (60 * 60 * 24)) + 1;
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= date('M d, Y', strtotime($req['created_at'])) ?></div>
                                <div class="date-subtext small">Ref: #LR-<?= $req['id'] ?></div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">
                                    <?= date('d M', $start) ?> — <?= date('d M, Y', $end) ?>
                                </div>
                                <span class="text-muted small"><?= $days ?> Day<?= $days > 1 ? 's' : '' ?></span>
                            </td>
                            <td>
                                <div class="text-muted text-wrap small" style="max-width: 280px; line-height: 1.4;">
                                    <?= htmlspecialchars($req['reason']) ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="status-pill" style="background-color: <?= $styles['bg'] ?>; color: <?= $styles['text'] ?>;">
                                    <span class="status-dot" style="background-color: <?= $styles['dot'] ?>;"></span>
                                    <?= ucfirst($req['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>