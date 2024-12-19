<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Auth.php';

$errorMessage = "";

// Instantiate Auth
$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        $errorMessage = "Email and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if (!$admin || !$auth->verifyPassword($password, $admin['password'])) {
                http_response_code(401);
                echo json_encode(['error' => true, 'message' => 'Invalid credentials']);
                exit;
            } else {
                // Save session and generate token
                $_SESSION['admin_id'] = $admin['id'];
                $csrfToken = $auth->generateCsrfToken();

                // Return success response with token
                http_response_code(200);
                echo json_encode(['message' => 'Login successful', 'csrf_token' => $csrfToken]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error: ' . $e->getMessage()]);
            exit;
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

        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault(); 

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/routes.php/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    // Save CSRF token if needed
                    localStorage.setItem('csrf_token', result.csrf_token);

                    // Redirect to index.php
                    window.location.href = '/index.php';
                } else {
                    alert(result.message || 'Login failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    </script>
</body>
</html>

