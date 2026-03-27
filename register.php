<?php
session_start();

// --- DATABASE CONNECTION ---
$host = "localhost";
$dbname = "ems"; 
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- REGISTRATION LOGIC ---
$message = "";
$message_type = ""; // To distinguish between success and error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'];
    $role_id = $_POST['role_id']; // Usually 3 for 'employee' by default

    // 1. Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $checkEmail->execute(['email' => $email]);
    
    if ($checkEmail->rowCount() > 0) {
        $message = "Email is already registered!";
        $message_type = "error";
    } else {
        // 2. Hash the password securely
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        // 3. Insert into database
        $sql = "INSERT INTO users (full_name, email, password, role_id) VALUES (:name, :email, :password, :role)";
        $stmt = $conn->prepare($sql);
        
        try {
            $stmt->execute([
                'name'     => $full_name,
                'email'    => $email,
                'password' => $hashed_password,
                'role'     => $role_id
            ]);
            $message = "Account created successfully! You can now login.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Registration failed: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMS | Create Account</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #1a73e8; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-reg { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .btn-reg:hover { background: #218838; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .error { background: #fce8e6; color: #d93025; border: 1px solid #f5c2c7; }
        .success { background: #e6ffed; color: #28a745; border: 1px solid #34d058; }
        .login-link { display: block; text-align: center; margin-top: 15px; color: #1a73e8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Register</h2>

    <?php if($message): ?>
        <div class="alert <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="email@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role_id">
                <option value="3">Employee</option>
                <option value="2">Manager</option>
                <option value="1">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn-reg">Create Account</button>
        <a href="login.php" class="login-link">Already have an account? Login here</a>
    </form>
</div>

</body>
</html>