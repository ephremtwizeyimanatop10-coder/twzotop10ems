<?php
session_start();
include "db.php";

// ✅ 1. Security Check: Admin Only
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role_name']) != "admin") {
    header("Location: login.php");
    exit();
}

$msg = "";
$search = isset($_GET['search']) ? $_GET['search'] : '';

// ✅ 2. Handle Add New Employee
if (isset($_POST['save_employee'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];
    $dept = $_POST['department'];

    try {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role_id, department, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$name, $email, $password, $role_id, $dept]);
        $msg = "Employee added successfully!";
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// ✅ 3. Handle Update Employee (New Logic)
if (isset($_POST['update_employee'])) {
    $id = $_POST['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role_id = $_POST['role_id'];
    $dept = $_POST['department'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role_id = ?, department = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role_id, $dept, $status, $id]);
        $msg = "Employee updated successfully!";
    } catch (PDOException $e) {
        $msg = "Update Error: " . $e->getMessage();
    }
}

// ✅ 4. Handle Deactivation / Activation
if (isset($_POST['toggle_status'])) {
    $target_id = $_POST['user_id'];
    $new_status = ($_POST['current_status'] == 'active') ? 'inactive' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $target_id]);
    $msg = "User status updated to " . strtoupper($new_status);
}

// ✅ 5. Handle Deletion
if (isset($_POST['delete_user'])) {
    $target_id = $_POST['user_id'];
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM attendance WHERE user_id = ?")->execute([$target_id]);
        $conn->prepare("DELETE FROM leave_requests WHERE user_id = ?")->execute([$target_id]);
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$target_id]);
        $conn->commit();
        $msg = "Employee permanently deleted.";
    } catch (Exception $e) {
        $conn->rollBack();
        $msg = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch roles for modals
$roles = $conn->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// ✅ 6. Fetch Employees
$query = "SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id 
          WHERE users.full_name LIKE ? OR users.department LIKE ? ORDER BY users.full_name ASC";
$result = $conn->prepare($query);
$result->execute(["%$search%", "%$search%"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Master List | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: radial-gradient(circle at top right, #1e293b, #0f172a); font-family: 'Inter', sans-serif; min-height: 100vh; color: #f8fafc; }
        .main-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); overflow: hidden; }
        .table { color: #e2e8f0; margin-bottom: 0; }
        .table-light { background: rgba(255, 255, 255, 0.05) !important; color: #94a3b8 !important; }
        .status-badge { font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; }
        .bg-active { background: #064e3b; color: #6ee7b7; }
        .bg-inactive { background: #7f1d1d; color: #fca5a5; }
        .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.3s; }
        .modal-content { background: #1e293b; color: white; border: 1px solid rgba(255, 255, 255, 0.1); }
        .form-control, .form-select { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <?php if ($msg): ?>
        <div class="alert alert-info bg-dark text-info border-info alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="fw-bold mb-1">Employee Management</h2><p class="text-muted small">Update or manage all staff members.</p></div>
        <a href="admin_dashboard.php" class="btn btn-outline-light px-4">Dashboard</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-8"><form method="GET" class="d-flex gap-2"><input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"><button class="btn btn-primary">Search</button></form></div>
        <div class="col-md-4 text-end"><button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">Add Employee</button></div>
    </div>

    <div class="card main-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light text-uppercase small">
                    <tr><th class="ps-4">Information</th><th>Department</th><th>Role</th><th>Status</th><th class="text-end pe-4">Manage</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></div>
                            <div class="text-muted small"><?= $row['email'] ?></div>
                        </td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><span class="badge bg-dark border border-info text-info"><?= ucfirst($row['role_name']) ?></span></td>
                        <td><span class="status-badge bg-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-action btn-outline-primary" 
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>"><input type="hidden" name="current_status" value="<?= $row['status'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-action btn-outline-warning"><i class="fas fa-power-off"></i></button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user?');">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-action btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add New Employee</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3"><label class="small">Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="small">Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="small">Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="small">Role</label><select name="role_id" class="form-select"><?php foreach($roles as $role): ?><option value="<?= $role['id'] ?>"><?= ucfirst($role['role_name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6 mb-3"><label class="small">Department</label><select name="department" class="form-select"><option value="IT">IT</option><option value="Finance">Finance</option><option value="HR">HR</option><option value="Nursing">Nursing</option></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="save_employee" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Employee Data</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="small">Full Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                    <div class="mb-3"><label class="small">Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="small">Role</label>
                            <select name="role_id" id="edit_role_id" class="form-select">
                                <?php foreach($roles as $role): ?><option value="<?= $role['id'] ?>"><?= ucfirst($role['role_name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="small">Department</label>
                            <select name="department" id="edit_dept" class="form-select">
                                <option value="IT">IT</option><option value="Finance">Finance</option><option value="HR">HR</option><option value="Nursing">Nursing</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="small">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Active</option><option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="update_employee" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Function to fill and open the Edit Modal
function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_name').value = user.full_name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role_id').value = user.role_id;
    document.getElementById('edit_dept').value = user.department;
    document.getElementById('edit_status').value = user.status;
    
    var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    myModal.show();
}
</script>
</body>
</html>