<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'warehouse' && $_SESSION['role'] !== 'admin')) {
    die("Access Denied: Warehouse only.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);
    $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
}

$sql = "SELECT * FROM orders WHERE status IN ('Ordered', 'In process')";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head><title>Warehouse Orders</title></head>
<body>
    <h2>Warehouse Order Management</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th><th>Invoice</th><th>Customer</th><th>Current Status</th><th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['invoice_no']; ?></td>
            <td><?php echo $row['customer_name']; ?></td>
            <td><b><?php echo $row['status']; ?></b></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                    <select name="new_status">
                        <option value="In process" <?php if($row['status'] == 'In process') echo 'selected'; ?>>In process (Gathering)</option>
                        <option value="In route">In route (Loaded & Ready)</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>