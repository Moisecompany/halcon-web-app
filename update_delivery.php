<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'route' && $_SESSION['role'] !== 'admin')) {
    die("Access Denied: Route only.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $order_id = (int)$_POST['order_id'];
    $upload_type = $_POST['upload_type']; // 'photo_loaded' or 'photo_delivered'
    
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        if ($upload_type == 'photo_loaded') {
            $conn->query("UPDATE orders SET photo_loaded = '$file_name' WHERE id = $order_id");
            $message = "Loaded photo uploaded successfully!";
        } elseif ($upload_type == 'photo_delivered') {
            $conn->query("UPDATE orders SET photo_delivered = '$file_name', status = 'Delivered' WHERE id = $order_id");
            $message = "Delivery photo uploaded! Order marked as Delivered.";
        }
    } else {
        $message = "Sorry, there was an error uploading your file.";
    }
}

// Show orders that are "In route"
$sql = "SELECT * FROM orders WHERE status = 'In route'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head><title>Route Deliveries</title></head>
<body>
    <h2>Route Delivery Management</h2>
    <p><b><?php echo $message; ?></b></p>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th><th>Address</th><th>Loaded Photo</th><th>Delivery Photo</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['delivery_address']; ?></td>
            
            <td>
                <?php if (empty($row['photo_loaded'])): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="upload_type" value="photo_loaded">
                        <input type="file" name="photo" required accept="image/*">
                        <button type="submit">Upload Loaded</button>
                    </form>
                <?php else: ?>
                    <span style="color:green;">Loaded Photo OK</span>
                <?php endif; ?>
            </td>

            <td>
                <?php if (!empty($row['photo_loaded']) && empty($row['photo_delivered'])): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="upload_type" value="photo_delivered">
                        <input type="file" name="photo" required accept="image/*">
                        <button type="submit">Upload Delivered</button>
                    </form>
                <?php elseif (!empty($row['photo_delivered'])): ?>
                    <span style="color:green;">Delivered!</span>
                <?php else: ?>
                    <span style="color:gray;">Upload Loaded photo first</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>