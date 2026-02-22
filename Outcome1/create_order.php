<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'sales' && $_SESSION['role'] !== 'admin')) {
    die("Access Denied: Sales only.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice_no = $conn->real_escape_string($_POST['invoice_no']);
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $fiscal_data = $conn->real_escape_string($_POST['fiscal_data']);
    $delivery_address = $conn->real_escape_string($_POST['delivery_address']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Generate an arbitrary unique customer number (e.g., CUST-83742)
    $customer_no = "CUST-" . rand(10000, 99999);

    $sql = "INSERT INTO orders (invoice_no, customer_no, customer_name, fiscal_data, delivery_address, notes) 
            VALUES ('$invoice_no', '$customer_no', '$customer_name', '$fiscal_data', '$delivery_address', '$notes')";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Order created! Customer No: <strong>$customer_no</strong> (Give this to the customer).";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Create Order</title></head>
<body>
    <h2>Create New Order</h2>
    <p><?php echo $message; ?></p>
    <form method="POST">
        Invoice Number: <input type="text" name="invoice_no" required><br><br>
        Customer Name: <input type="text" name="customer_name" required><br><br>
        Fiscal Data (RFC/Tax Info): <textarea name="fiscal_data" required></textarea><br><br>
        Delivery Address: <textarea name="delivery_address" required></textarea><br><br>
        Notes: <textarea name="notes"></textarea><br><br>
        <button type="submit">Submit Order</button>
    </form>
    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>