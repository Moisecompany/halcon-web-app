<?php
session_start();
include 'db.php';

// Security: Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Admin only.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']);

    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        $message = "User created successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register User</title></head>
<body>
    <h2>Register New Employee</h2>
    <p><b><?php echo $message; ?></b></p>
    <form method="POST">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="text" name="password" required><br><br>
        Role: 
        <select name="role" required>
            <option value="sales">Sales</option>
            <option value="purchasing">Purchasing</option>
            <option value="warehouse">Warehouse</option>
            <option value="route">Route</option>
            <option value="admin">Admin</option>
        </select><br><br>
        <button type="submit">Create User</button>
    </form>
    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>