<?php
session_start();
include 'db.php';

// Security: Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Note: Assuming your login script sets $_SESSION['user_id']. 
    // If your login script sets $_SESSION['id'], change the line above too!
    die("Access Denied: Admin only.");
}

$message = "";

// 1. HANDLE FORM SUBMISSIONS (CREATE & UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    if ($action == 'create') {
        $password = $_POST['password'];
        // ALWAYS hash passwords before storing them
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $hashed_password, $role, $is_active);

        if ($stmt->execute()) {
            $message = "<span style='color: green;'>User created successfully!</span>";
        } else {
            $message = "<span style='color: red;'>Error: " . $stmt->error . "</span>";
        }
        $stmt->close();
    } 
    elseif ($action == 'edit') {
        $id = (int)$_POST['id']; // Changed from user_id to id
        
        // If the admin typed a new password, update it. Otherwise, keep the old one.
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=?, is_active=? WHERE id=?"); // Changed here
            $stmt->bind_param("sssii", $username, $hashed_password, $role, $is_active, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, is_active=? WHERE id=?"); // Changed here
            $stmt->bind_param("ssii", $username, $role, $is_active, $id);
        }

        if ($stmt->execute()) {
            $message = "<span style='color: green;'>User updated successfully!</span>";
        } else {
            $message = "<span style='color: red;'>Error: " . $stmt->error . "</span>";
        }
        $stmt->close();
    }
}

// 2. FETCH USER FOR EDITING (If requested via URL like ?edit=2)
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?"); // Changed here
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

// 3. FETCH ALL USERS FOR THE LIST
// Changed user_id to id in the SELECT statement
$users_result = $conn->query("SELECT id, username, role, is_active FROM users ORDER BY is_active DESC, role ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users | Halcon Admin</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .inactive { color: red; font-style: italic; }
    </style>
</head>
<body>
    <h2><?php echo $edit_user ? "Edit Employee" : "Register New Employee"; ?></h2>
    <p><b><?php echo $message; ?></b></p>
    
    <form method="POST" action="register_user.php">
        <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'create'; ?>">
        <?php if ($edit_user): ?>
            <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
        <?php endif; ?>

        Username: 
        <input type="text" name="username" value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" required><br><br>
        
        Password: 
        <input type="text" name="password" <?php echo $edit_user ? '' : 'required'; ?>> 
        <?php if ($edit_user): ?>
            <small>(Leave blank to keep current password)</small>
        <?php endif; ?><br><br>
        
        Role: 
        <select name="role" required>
            <option value="sales" <?php echo ($edit_user && $edit_user['role'] == 'sales') ? 'selected' : ''; ?>>Sales</option>
            <option value="purchasing" <?php echo ($edit_user && $edit_user['role'] == 'purchasing') ? 'selected' : ''; ?>>Purchasing</option>
            <option value="warehouse" <?php echo ($edit_user && $edit_user['role'] == 'warehouse') ? 'selected' : ''; ?>>Warehouse</option>
            <option value="route" <?php echo ($edit_user && $edit_user['role'] == 'route') ? 'selected' : ''; ?>>Route</option>
            <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select><br><br>

        Status:
        <select name="is_active">
            <option value="1" <?php echo ($edit_user && $edit_user['is_active'] == 1) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ($edit_user && $edit_user['is_active'] == 0) ? 'selected' : ''; ?>>Inactive</option>
        </select><br><br>

        <button type="submit"><?php echo $edit_user ? "Update User" : "Create User"; ?></button>
        <?php if ($edit_user): ?>
            <a href="register_user.php"><button type="button">Cancel Edit</button></a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Employee List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Department/Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($row['role']); ?></td>
                    <td class="<?php echo $row['is_active'] ? '' : 'inactive'; ?>">
                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                    </td>
                    <td>
                        <a href="register_user.php?edit=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>