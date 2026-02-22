<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Halcon - Track Your Order</title>
    <style>
        /* Add your basic CSS here */
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result-box { margin-top: 20px; padding: 15px; border: 1px solid #ccc; }
        img { max-width: 300px; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Track Your Halcon Order</h2>
    <form method="POST" action="">
        <label>Customer Number:</label><br>
        <input type="text" name="customer_no" required><br><br>
        <label>Invoice Number:</label><br>
        <input type="text" name="invoice_no" required><br><br>
        <button type="submit" name="track">Check Status</button>
    </form>

    <?php
    if (isset($_POST['track'])) {
        $customer_no = $conn->real_escape_string($_POST['customer_no']);
        $invoice_no = $conn->real_escape_string($_POST['invoice_no']);

        $sql = "SELECT status, photo_delivered FROM orders WHERE customer_no = '$customer_no' AND invoice_no = '$invoice_no'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<div class='result-box'>";
            echo "<h3>Current Status: <strong>" . $row['status'] . "</strong></h3>";
            
            // If delivered, show the evidence photo
            if ($row['status'] == 'Delivered' && !empty($row['photo_delivered'])) {
                echo "<p>Delivery Evidence:</p>";
                echo "<img src='uploads/" . $row['photo_delivered'] . "' alt='Delivery Photo'>";
            }
            echo "</div>";
        } else {
            echo "<p style='color:red;'>Order not found. Please check your details.</p>";
        }
    }
    ?>
</body>
</html>