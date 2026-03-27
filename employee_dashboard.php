<?php
session_start();

// ✅ Security Check: Only allow logged-in employees
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url('emplyee.jpg'); 
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            background-repeat: no-repeat;
            background-color: #1a1a1a; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            min-height: 100vh;
            margin: 0;
        }

        .navbar { 
            background: rgba(33, 37, 41, 0.9) !important; 
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-card {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.85) 0%, rgba(13, 71, 161, 0.85) 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            backdrop-filter: blur(8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .action-card {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            padding: 30px !important; /* Adjusted for 3 cards */
        }

        .action-card:hover { 
            transform: translateY(-10px); 
            background: #ffffff;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        }

        .clock { font-size: 1.3rem; font-weight: 600; color: #f8f9fa; letter-spacing: 1px; }

        .icon-circle {
            width: 70px;
            height: 70px;
            background: #f1f3f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            transition: 0.3s;
        }
        
        .action-card:hover .icon-circle {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark p-3 sticky-top">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="fas fa-id-badge me-2 text-primary"></i>EMS Portal</span>
        <div id="liveClock" class="clock d-none d-md-block"></div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 fw-bold">Logout</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card welcome-card p-5 mb-5 text-center">
                <h1 class="display-4 fw-bold">Hello, <?php echo htmlspecialchars($user['full_name'] ?? 'Employee'); ?>!</h1>
                <p class="lead mb-4">You are currently assigned to the <strong><?php echo htmlspecialchars($user['department'] ?? 'General'); ?></strong> department.</p>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">Role: Employee</span>
                    <span class="badge bg-success px-3 py-2">Status: Active</span>
                </div>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="card action-card text-center h-100">
                        <div class="icon-circle text-primary"><i class="fas fa-calendar-check"></i></div>
                        <h4 class="text-dark fw-bold mb-3">Attendance</h4>
                        <p class="text-muted mb-4">Register your daily clock-in/out and keep your records accurate.</p>
                        <a href="mark_attendance.php" class="btn btn-primary btn-lg w-100 mt-auto shadow-sm">Go to Attendance</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card action-card text-center h-100">
                        <div class="icon-circle text-warning"><i class="fas fa-paper-plane"></i></div>
                        <h4 class="text-dark fw-bold mb-3">Request Leave</h4>
                        <p class="text-muted mb-4">Planning time off? Submit your leave applications here.</p>
                        <a href="request_leave.php" class="btn btn-warning btn-lg w-100 mt-auto text-white shadow-sm">Request Leave</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card action-card text-center h-100">
                        <div class="icon-circle text-info"><i class="fas fa-list-check"></i></div>
                        <h4 class="text-dark fw-bold mb-3">My Leaves</h4>
                        <p class="text-muted mb-4">View your leave history and check the approval status of your requests.</p>
                        <a href="my_leaves.php" class="btn btn-info btn-lg w-100 mt-auto text-white shadow-sm">View Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        document.getElementById('liveClock').innerText = now.toLocaleTimeString(undefined, options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

</body>
</html>