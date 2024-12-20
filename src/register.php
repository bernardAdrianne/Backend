<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Auth.php';

$errorMessage = "";
$successMessage = "";

// Instantiate Auth
$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST["email"] ?? "";
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($username) || empty($password)) {
        $errorMessage = "Username, email, and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin) {
                $errorMessage = "Admin already exists.";
            } else {
                $passwordHash = $auth->hashPassword($password);
                $sql = "INSERT INTO admin (email, username, password) VALUES (:email, :username, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':email' => $email, ':username' => $username, ':password' => $passwordHash]);

                $successMessage = "Registration successful. You can now log in.";
                header("Location: login.php");
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Register</h2>
        
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-warning"><?= $errorMessage ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <!-- CSRF Token Input -->
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-outline-primary">Login</a>
        </form>
    </div>
</body>
</html>
