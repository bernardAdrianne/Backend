<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Auth.php';

session_start();

$errorMessage = "";

// Instantiate Auth
$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST["email"] ?? "";
    $encryptedPassword = $_POST["password"] ?? "";

    if (empty($email) || empty($encryptedPassword)) {
        $errorMessage = "Email and password are required.";
    } else {
        try {
            // Decrypt the password
            $decryptedPassword = $auth->decryptData($encryptedPassword);

            $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();
            
            if (!$admin || !$auth->verifyPassword($decryptedPassword, $admin['password'])) {
                http_response_code(401);
                $errorMessage = "Invalid credentials.";
            } else {
                // Save session and redirect to index
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: index.php");
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            $errorMessage = "Server error: " . $e->getMessage();
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2>Login</h2>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="register.php" class="btn btn-outline-primary">Register</a>
        </form>
    </div>

    <script>
        const key = CryptoJS.enc.Utf8.parse('sampleKey01'); // Same key as in PHP
        const iv = CryptoJS.enc.Utf8.parse(CryptoJS.MD5('sampleKey01').toString()); // IV derived from the key

        function encryptPassword(password) {
            const encrypted = CryptoJS.AES.encrypt(password, key, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            });
            return encrypted.toString();
        }

        document.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault();

            const passwordField = document.querySelector('#password');
            const encryptedPassword = encryptPassword(passwordField.value);

            passwordField.value = encryptedPassword; // Replace the plaintext password with encrypted

            e.target.submit(); // Submit the form
        });
    </script>
</body>
</html>
