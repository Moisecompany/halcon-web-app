<?php
session_start();
include 'db.php'; // Connects to your halcon_db

$error = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Look for the user in the database AND make sure they are active (is_active = 1)
    // Using prepared statements for security against SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificamos la contraseña encriptada usando password_verify
        // IMPORTANTE: Si tienes usuarios viejos con contraseñas en texto plano (como 'admin123'),
        // vas a tener que editarlos en la pantalla de Admin y volver a guardar su contraseña para que se encripte.
        if (password_verify($password, $user['password'])) {
            // Password is correct! Set the session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Send them to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado o la cuenta está inactiva.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Halcon - Employee Login</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-container { background: white; padding: 20px 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .error { color: red; margin-bottom: 10px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #004494; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Halcon Employee Login</h2>
        
        <?php if ($error != ''): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Log In</button>
        </form>
    </div>

</body>
</html>