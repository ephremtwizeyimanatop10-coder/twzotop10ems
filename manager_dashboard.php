<?php
session_start();
include "db.php";

// 1. Security Check: Only allow logged-in managers
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role_name']) !== 'manager') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$dept = $user['department'];
$msg = "";

// 2. Handle Attendance Action (Clock In / Clock Out)
if (isset($_POST['attendance_action'])) {
    $today = date('Y-m-d');
    
    if ($_POST['attendance_action'] == 'check_in') {
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
        $check_stmt->execute([$user_id, $today]);
        if (!$check_stmt->fetch()) {
            $ins = $conn->prepare("INSERT INTO attendance (user_id, check_in) VALUES (?, NOW())");
            $ins->execute([$user_id]);
            $msg = "Clocked in successfully!";
        }
    } elseif ($_POST['attendance_action'] == 'check_out') {
        $upd = $conn->prepare("UPDATE attendance SET check_out = NOW() WHERE user_id = ? AND DATE(check_in) = ? AND check_out IS NULL");
        $upd->execute([$user_id, $today]);
        $msg = "Clocked out successfully!";
    }
}

// 3. Status checks for the Dashboard Buttons
$status_stmt = $conn->prepare("SELECT check_in, check_out FROM attendance WHERE user_id = ? AND DATE(check_in) = CURDATE()");
$status_stmt->execute([$user_id]);
$attendance_record = $status_stmt->fetch(PDO::FETCH_ASSOC);

// 4. Dynamic Stats for Cards
$leave_stmt = $conn->prepare("SELECT COUNT(*) FROM leave_requests JOIN users ON leave_requests.user_id = users.id WHERE users.department = ? AND leave_requests.status = 'pending'");
$leave_stmt->execute([$dept]);
$pending_leaves = $leave_stmt->fetchColumn();

$team_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE department = ?");
$team_stmt->execute([$dept]);
$team_count = $team_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #f59e0b;
            --sidebar: rgba(30, 41, 59, 0.95);
            --bg: #f1f5f9;
            --white: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            margin: 0; 
            display: flex; 
            color: var(--text-dark);
            /* ✅ Logo Background with Dark Overlay */
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), 
                        url('logo.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Sidebar - Transparent Glass Style */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: var(--sidebar); 
            backdrop-filter: blur(10px);
            color: white; 
            position: fixed; 
            padding: 30px 20px; 
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar h2 { color: var(--primary); font-size: 20px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }
        .badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; float: right; }

        /* Main Content */
        .main-content { margin-left: 260px; width: 100%; padding: 40px; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            background: rgba(255, 255, 255, 0.9); 
            padding: 20px; 
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }
        
        .attendance-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 6px solid var(--primary);
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-in { background: var(--success); color: white; }
        .btn-in:hover { background: #16a34a; transform: scale(1.05); }
        .btn-out { background: var(--danger); color: white; }
        .btn-out:hover { background: #dc2626; transform: scale(1.05); }
        .btn-disabled { background: #cbd5e1; color: #64748b; cursor: not-allowed; }

        /* Summary Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
        
        .icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 18px; }
        
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-blue { background: #e0f2fe; color: #0369a1; }
        .bg-orange { background: #fff7ed; color: #9a3412; }

        .alert { background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-user-tie"></i> Manager Hub</h2>
    <a href="manager_dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="view_team.php"><i class="fas fa-users"></i> Team Members</a>
    <a href="view_attendance.php"><i class="fas fa-calendar-alt"></i> Attendance Logs</a>
    <a href="view_leave_requests.php">
        <i class="fas fa-calendar-check"></i> Leave Requests
        <?php if($pending_leaves > 0): ?> <span class="badge"><?= $pending_leaves ?></span> <?php endif; ?>
    </a>
    <a href="logout.php" style="margin-top: 20px; color: var(--danger);"><i class="fas fa-power-off"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <div>
            <h1 style="margin:0; font-size: 28px; color: var(--text-dark);">Hello, <?= htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: var(--text-muted); margin: 5px 0 0 0;">Department: <strong><?= htmlspecialchars($dept) ?></strong></p>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: bold; color: var(--text-dark);"><?= date('l, F j') ?></div>
            <div style="font-size: 12px; color: var(--text-muted);"><?= date('Y') ?></div>
        </div>
    </div>

    <?php if($msg): ?> <div class="alert"><i class="fas fa-check-circle"></i> <?= $msg ?></div> <?php endif; ?>

    <div class="attendance-box">
        <div>
            <h3 style="margin: 0 0 5px 0; color: var(--text-dark);">Work Status</h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Toggle your shift availability below.</p>
        </div>
        <form method="POST">
            <?php if (!$attendance_record): ?>
                <button type="submit" name="attendance_action" value="check_in" class="btn btn-in">
                    <i class="fas fa-door-open"></i> Start Shift
                </button>
            <?php elseif ($attendance_record && !$attendance_record['check_out']): ?>
                <button type="submit" name="attendance_action" value="check_out" class="btn btn-out">
                    <i class="fas fa-door-closed"></i> End Shift
                </button>
            <?php else: ?>
                <span class="btn btn-disabled"><i class="fas fa-check"></i> Shift Completed</span>
            <?php endif; ?>
        </form>
    </div>

    <div class="grid">
        <a href="view_team.php" class="card">
            <div class="icon bg-green"><i class="fas fa-users"></i></div>
            <div style="color: var(--text-muted); font-size: 13px; font-weight: bold; text-transform: uppercase;">Total Team</div>
            <div style="font-size: 32px; font-weight: 800; margin: 10px 0; color: var(--text-dark);"><?= $team_count ?></div>
            <div style="color: var(--success); font-size: 12px;"><i class="fas fa-circle" style="font-size: 8px;"></i> View active roster</div>
        </a>

        <a href="view_attendance.php" class="card">
            <div class="icon bg-orange"><i class="fas fa-clipboard-list"></i></div>
            <div style="color: var(--text-muted); font-size: 13px; font-weight: bold; text-transform: uppercase;">Attendance Logs</div>
            <div style="font-size: 20px; font-weight: 800; margin: 15px 0; color: var(--text-dark);">Track Hours</div>
            <div style="color: var(--primary); font-size: 12px;">Review team timing →</div>
        </a>

        <a href="view_leave_requests.php" class="card">
            <div class="icon bg-red"><i class="fas fa-clock"></i></div>
            <div style="color: var(--text-muted); font-size: 13px; font-weight: bold; text-transform: uppercase;">Pending Leaves</div>
            <div style="font-size: 32px; font-weight: 800; margin: 10px 0; color: var(--danger);"><?= $pending_leaves ?></div>
            <div style="color: var(--danger); font-size: 12px;"><i class="fas fa-exclamation-triangle"></i> Needs review</div>
        </a>

        <a href="reports.php" class="card">
            <div class="icon bg-blue"><i class="fas fa-file-invoice"></i></div>
            <div style="color: var(--text-muted); font-size: 13px; font-weight: bold; text-transform: uppercase;">Department Reports</div>
            <div style="font-size: 20px; font-weight: 800; margin: 15px 0; color: var(--text-dark);">Analytics</div>
            <div style="color: #0369a1; font-size: 12px;">Open performance data <i class="fas fa-chevron-right" style="font-size: 10px;"></i></div>
        </a>
    </div>
</div>

</body>
</html>