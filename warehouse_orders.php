<?php
session_start();
include 'db.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    die("Access Denied: Please log in first.");
}

$message = "";

// 1. Handle Logical Deletion
if (isset($_GET['delete_invoice'])) {
    $invoice_to_delete = $conn->real_escape_string($_GET['delete_invoice']);
    $sql_delete = "UPDATE orders SET is_deleted = 1 WHERE invoice_no = '$invoice_to_delete'";
    
    if ($conn->query($sql_delete) === TRUE) {
        $message = "<span style='color: green;'>Order $invoice_to_delete logically deleted.</span>";
    } else {
        $message = "<span style='color: red;'>Error deleting order: " . $conn->error . "</span>";
    }
}

// 2. Handle Status Update (New Feature)
if (isset($_GET['update_status']) && isset($_GET['invoice_no'])) {
    $new_status = $conn->real_escape_string($_GET['update_status']);
    $invoice_no = $conn->real_escape_string($_GET['invoice_no']);
    
    $sql_update = "UPDATE orders SET status = '$new_status' WHERE invoice_no = '$invoice_no'";
    
    if ($conn->query($sql_update) === TRUE) {
        $message = "<span style='color: blue;'>Order $invoice_no status changed to '$new_status'.</span>";
    } else {
        $message = "<span style='color: red;'>Error updating status: " . $conn->error . "</span>";
    }
}

// Fetch all active orders
$sql_orders = "SELECT invoice_no, customer_no, status FROM orders WHERE is_deleted = 0 ORDER BY invoice_no DESC";
$result_orders = $conn->query($sql_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Halcon Internal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        
        /* Button Styles */
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; color: white; display: inline-block; margin-right: 5px; font-size: 14px;}
        .btn-delete { background-color: red; }
        .btn-delete:hover { background-color: darkred; }
        .btn-process { background-color: #f39c12; } /* Naranja */
        .btn-process:hover { background-color: #e67e22; }
        .btn-route { background-color: #3498db; } /* Azul */
        .btn-route:hover { background-color: #2980b9; }
    </style>
</head>
<body>
    <h2>Internal Order Management (Warehouse / Route)</h2>
    <p><b><?php echo $message; ?></b></p>

    <table>
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Customer Number</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_orders->num_rows > 0) {
                while ($row = $result_orders->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['invoice_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['customer_no']) . "</td>";
                    
                    // Resaltamos el estatus un poco visualmente
                    echo "<td><strong>" . htmlspecialchars($row['status']) . "</strong></td>";
                    
                    echo "<td>";
                    
                    // Lógica de botones dinámicos según el estatus actual
                    if ($row['status'] == 'Ordered') {
                        echo "<a href='warehouse_orders.php?update_status=In process&invoice_no=" . urlencode($row['invoice_no']) . "' class='btn btn-process'>Mark as 'In Process'</a>";
                    } elseif ($row['status'] == 'In process') {
                        echo "<a href='warehouse_orders.php?update_status=In route&invoice_no=" . urlencode($row['invoice_no']) . "' class='btn btn-route'>Mark as 'In Route'</a>";
                    }
                    
                    // El botón de borrar siempre se muestra
                    echo "<a href='warehouse_orders.php?delete_invoice=" . urlencode($row['invoice_no']) . "' class='btn btn-delete' onclick=\"return confirm('Are you sure you want to delete order " . $row['invoice_no'] . "?');\">Delete</a>";
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No active orders found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <br><br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>