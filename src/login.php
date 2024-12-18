<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Auth.php';

$errorMessage = "";
$successMessage = "";

// Instantiate Auth
$auth = new Auth($pdo);

// Generate CSRF Token
$csrfToken = $auth->generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $csrfTokenInput = $_POST["csrf_token"] ?? "";

    // Validate CSRF Token
    try {
        $auth->validateCsrfToken($csrfTokenInput);
    } catch (Exception $e) {
        $errorMessage = "Invalid CSRF token.";
    }

    if (empty($email) || empty($password)) {
        $errorMessage = "Email and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();
            
            if (!$admin || !$auth->verifyPassword($password, $admin['password'])) {
                $errorMessage = "Invalid credentials. Please try again.";
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: index.php");
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
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Login</h2>
        
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-warning"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <!-- CSRF Token Input -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="register.php" class="btn btn-outline-primary">Register</a>
        </form>
    </div>
</body>
</html>
