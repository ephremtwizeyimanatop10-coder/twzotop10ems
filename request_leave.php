<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); 
    exit(); 
}

$msg = "";
$error = "";

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user']['id'];
    $reason = trim($_POST['reason']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    
    // Accept hours (only if provided for single-day leave)
    $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;

    try {
        $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, reason, start_date, end_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $reason, $start, $end, $start_time, $end_time]);
        $msg = "Your leave request has been submitted successfully!";
    } catch (PDOException $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave | EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Modern Design System */
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --bg: #f8fafc;
            --text: #1e293b;
            --border: #e2e8f0;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container { width: 100%; padding: 20px; }

        .leave-card {
            background: #ffffff;
            max-width: 450px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header i { color: var(--primary); font-size: 3rem; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; }
        .header p { color: #64748b; font-size: 14px; margin-top: 5px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #334155; }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
            transition: 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        }

        .row { display: flex; gap: 15px; }
        .col { flex: 1; }

        #timeFields {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 12px;
            display: none; /* Only shows when dates match */
            margin-top: 10px;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            transition: 0.2s;
        }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="container">
    <div class="leave-card">
        <div class="header">
            <i class="fas fa-calendar-plus"></i>
            <h2>Request Leave</h2>
            <p>Submit your time-off request for approval</p>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i> <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Reason for Leave</label>
                <textarea name="reason" rows="3" placeholder="Describe why you need leave..." required></textarea>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="startDate" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="endDate" required min="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div id="timeFields">
                <p style="font-size: 12px; color: var(--primary); font-weight: 700; margin-bottom: 10px;">
                    <i class="fas fa-clock"></i> SPECIFY HOURS (OPTIONAL)
                </p>
                <div class="row">
                    <div class="col">
                        <label style="font-size: 11px;">From</label>
                        <input type="time" name="start_time">
                    </div>
                    <div class="col">
                        <label style="font-size: 11px;">To</label>
                        <input type="time" name="end_time">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">Submit Request</button>
            
            <a href="employee_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Return to Dashboard
            </a>
        </form>
    </div>
</div>

<script>
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const timeFields = document.getElementById('timeFields');

    function toggleTimeFields() {
        // Show time inputs ONLY if leave is for the same day
        if (startDate.value && endDate.value && startDate.value === endDate.value) {
            timeFields.style.display = 'block';
        } else {
            timeFields.style.display = 'none';
        }
    }

    startDate.addEventListener('change', toggleTimeFields);
    endDate.addEventListener('change', toggleTimeFields);
</script>

</body>
</html>