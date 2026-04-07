<?php
session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head><title>Halcon Dashboard</title></head>
<body>
    <h1>Welcome to the Halcon Dashboard</h1>
    <p>Logged in as: <?php echo $_SESSION['username']; ?> (Department: <?php echo ucfirst($role); ?>)</p>

    <hr>

    <?php if ($role == 'admin'): ?>
        <h3>Admin Actions</h3>
        <ul>
            <li><a href="register_user.php">Register New User / Assign Roles</a></li>
        </ul>
    <?php endif; ?>

    <?php if ($role == 'sales' || $role == 'admin'): ?>
        <h3>Sales Actions</h3>
        <ul>
            <li><a href="create_order.php">Create New Order</a></li>
        </ul>
    <?php endif; ?>

    <?php if ($role == 'warehouse' || $role == 'admin'): ?>
        <h3>Warehouse Actions</h3>
        <ul>
            <li><a href="warehouse_orders.php">Manage Orders (Change to 'In process' / 'In route')</a></li>
        </ul>
    <?php endif; ?>

    <?php if ($role == 'route' || $role == 'admin'): ?>
        <h3>Route Actions</h3>
        <ul>
            <li><a href="update_delivery.php">Upload Loaded/Delivery Photos & Mark Delivered</a></li>
        </ul>
    <?php endif; ?>

    <h3>All Orders Overview</h3>
    </body>
</html>