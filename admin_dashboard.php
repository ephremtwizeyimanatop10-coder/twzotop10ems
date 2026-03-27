<?php
session_start();
include "db.php";

// 1. Security Check: Only allow logged-in admins
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// 2. Data Retrieval for Stats & Notifications
// Count Pending Leave Requests
$stmt = $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'");
$pending_count = $stmt->fetchColumn();

// Count Total Employees
$emp_stmt = $conn->query("SELECT COUNT(*) FROM users");
$total_employees = $emp_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global Styles */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            display: flex; 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('logo.jpeg'); 
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
        }
        
        /* Sidebar Navigation */
        .sidebar { 
            width: 250px; 
            height: 100vh; 
            background: rgba(44, 62, 80, 0.95); 
            color: white; 
            padding: 20px; 
            position: fixed; 
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar h2 { font-size: 22px; border-bottom: 1px solid #555; padding-bottom: 15px; margin-bottom: 20px; color: #ecf0f1; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px 15px; transition: 0.3s; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover { color: white; background: rgba(52, 73, 94, 0.8); padding-left: 20px; }

        /* Notification Badge */
        .badge { background: #e74c3c; color: white; border-radius: 50px; padding: 3px 8px; font-size: 11px; margin-left: 5px; font-weight: bold; }

        /* Main Area */
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        
        /* Header Bar */
        .header { 
            background: rgba(255, 255, 255, 0.9); 
            padding: 20px 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            backdrop-filter: blur(5px);
        }
        
        /* Stats Grid */
        .stats-container { display: flex; gap: 20px; margin-top: 30px; }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 12px;
            flex: 1;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border-left: 6px solid #1a73e8;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: #2c3e50;
            backdrop-filter: blur(5px);
        }
        .stat-card:hover { transform: translateY(-8px); background: #ffffff; }
        
        /* Card Colors */
        .employee-card { border-left-color: #1a73e8; }
        .leave-card { border-left-color: #9b59b6; }
        .attendance-card { border-left-color: #27ae60; }

        .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
        
        /* Action Buttons */
        .logout-btn { background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .logout-btn:hover { background: #c0392b; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-user-shield me-2"></i> EMS ADMIN</h2>
    <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a href="manage_employees.php"><i class="fas fa-users me-2"></i> Manage Employees</a>
    <a href="view_attendance.php"><i class="fas fa-calendar-alt me-2"></i> View Attendance</a>
    <a href="view_leave_requests.php"><i class="fas fa-envelope me-2"></i> Leave Requests
        <?php if($pending_count > 0): ?>
            <span class="badge"><?= $pending_count ?></span>
        <?php endif; ?>
    </a>
    <a href="reports.php"><i class="fas fa-chart-bar me-2"></i> Reports</a>
</div>

<div class="main-content">
    
    <div class="header">
        <h1 style="margin: 0; color: #2c3e50; font-size: 24px;">
            Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
        </h1>
        <div style="display: flex; align-items: center; gap: 20px;">
            <span class="text-muted">Role: <strong>Admin</strong></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="stats-container">
        
        <a href="manage_employees.php" class="stat-card employee-card">
            <h3 style="margin: 0; color: #7f8c8d; font-size: 16px; text-transform: uppercase;">Total Employees</h3>
            <p class="stat-number" style="color: #1a73e8;"><?= $total_employees ?></p>
            <small>Click to manage staff members</small>
        </a>

        <a href="view_leave_requests.php" class="stat-card leave-card">
            <h3 style="margin: 0; color: #7f8c8d; font-size: 16px; text-transform: uppercase;">Leave Requests</h3>
            <p class="stat-number" style="color: #9b59b6;"><?= $pending_count ?> Pending</p>
            <small>Review and approve requests</small>
        </a>

        <a href="view_attendance.php" class="stat-card attendance-card">
            <h3 style="margin: 0; color: #7f8c8d; font-size: 16px; text-transform: uppercase;">Attendance</h3>
            <p class="stat-number" style="color: #27ae60;">Logs</p>
            <small>Monitor daily clock-in/out</small>
        </a>

    </div>
</div>

</body>
</html>