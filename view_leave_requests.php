<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role_name'] !== 'admin' && $_SESSION['user']['role_name'] !== 'manager')) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role_name'];
$user_dept = $user['department'];

// ✅ Logic to Approve/Reject
if (isset($_POST['update_leave']) && $role == 'manager') {
    $leave_id = $_POST['leave_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $leave_id]);
    $msg = "Request has been " . strtoupper($status) . "!";
}

// ✅ Data Retrieval
if ($role == 'admin') {
    $query = "SELECT leave_requests.*, users.full_name, users.department FROM leave_requests JOIN users ON leave_requests.user_id = users.id ORDER BY created_at DESC";
    $stmt = $conn->query($query);
} else {
    $query = "SELECT leave_requests.*, users.full_name, users.department FROM leave_requests JOIN users ON leave_requests.user_id = users.id WHERE LOWER(users.department) = LOWER(?) ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_dept]);
}
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getLeaveDuration($start_d, $end_d, $start_t, $end_t) {
    if ($start_d == $end_d && !empty($start_t) && !empty($end_t)) {
        $hours = round(abs(strtotime($end_t) - strtotime($start_t)) / 3600, 1);
        return ["type" => "Short", "val" => "$hours Hrs", "class" => "bg-warning-subtle text-warning-emphasis"];
    } else {
        $days = abs(round((strtotime($end_d) - strtotime($start_d)) / 86400)) + 1;
        return ["type" => "Long", "val" => "$days Days", "class" => "bg-primary-subtle text-primary-emphasis"];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --glass: rgba(255, 255, 255, 0.9); --bg: #f4f7fe; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3748; }
        .main-card { border: none; border-radius: 20px; box-shadow: 0 20px 27px 0 rgba(0,0,0,0.05); background: white; }
        .table thead th { background: transparent; color: #a0aec0; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding: 1.5rem 1rem; }
        .avatar-circle { width: 38px; height: 38px; background: #4a5568; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .leave-type-badge { padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; }
        .status-pill { padding: 5px 12px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; }
        .btn-action { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: 0.2s; border: none; }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #166534; color: white; }
        .btn-reject { background: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background: #991b1b; color: white; }
        .nav-btn { background: white; border-radius: 12px; color: #4a5568; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-1">Leave Requests</h3>
            <p class="text-muted small">Manage employee absences and time-off logs.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?= $role ?>_dashboard.php" class="btn nav-btn px-4 py-2 border">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-white border-0 shadow-sm alert-dismissible fade show mb-4 rounded-4" role="alert">
            <i class="fas fa-check-circle text-success me-2"></i> <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="main-card p-2">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Duration & Type</th>
                        <th>Schedule</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $req): 
                        $duration = getLeaveDuration($req['start_date'], $req['end_date'], $req['start_time'] ?? null, $req['end_time'] ?? null);
                    ?>
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <td class="ps-4 py-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <?= strtoupper(substr($req['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-sm fw-bold"><?= htmlspecialchars($req['full_name']) ?></h6>
                                    <p class="text-xs text-muted mb-0 small"><?= htmlspecialchars($req['department']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="leave-type-badge <?= $duration['class'] ?>">
                                <?= $duration['val'] ?> (<?= $duration['type'] ?>)
                            </span>
                        </td>
                        <td>
                            <div class="text-sm fw-bold text-dark" style="font-size: 0.85rem;">
                                <?= date('d M, Y', strtotime($req['start_date'])) ?>
                            </div>
                            <?php if($req['start_date'] == $req['end_date'] && !empty($req['start_time'])): ?>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1 text-warning"></i> <?= date('h:i A', strtotime($req['start_time'])) ?>
                                </div>
                            <?php elseif($req['start_date'] != $req['end_date']): ?>
                                <div class="text-muted small">Until <?= date('d M, Y', strtotime($req['end_date'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 200px;">
                            <div class="small text-muted" style="word-wrap: break-word;">
                                <?= !empty($req['reason']) ? htmlspecialchars($req['reason']) : '<i class="text-muted opacity-50">No reason provided</i>'; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $s_class = ($req['status'] == 'approved') ? 'bg-success-subtle text-success' : (($req['status'] == 'rejected') ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary');
                            ?>
                            <span class="status-pill <?= $s_class ?> text-uppercase">
                                <?= $req['status'] ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <?php if($role == 'manager' && $req['status'] == 'pending'): ?>
                                <form method="POST" class="d-flex gap-2 justify-content-end">
                                    <input type="hidden" name="leave_id" value="<?= $req['id'] ?>">
                                    <input type="hidden" name="update_leave" value="1">
                                    <button type="submit" name="status" value="approved" class="btn-action btn-approve" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="submit" name="status" value="rejected" class="btn-action btn-reject" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <i class="fas fa-lock text-muted small" title="Closed"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>