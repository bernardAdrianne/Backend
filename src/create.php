<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Post.php';
require_once __DIR__ . '/../modules/Get.php';
require_once __DIR__ . '/../modules/Auth.php';

// Instantiate Auth
$auth = new Auth($pdo);

// Initialize variables
$name = $email = $address = "";
$errorMessage = $successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $address = $_POST["address"] ?? "";
    $csrfToken = $_POST["csrf_token"] ?? "";

    // Validate CSRF token
    try {
        $auth->validateCsrfToken($csrfToken); 
    } catch (Exception $e) {
        $errorMessage = "Invalid CSRF token.";
    }

    if (empty($name) || empty($email) || empty($address)) {
        $errorMessage = "All the fields are required.";
    } else {
        try {
            $result = addStudent($pdo, $name, $email, $address);

            if (!$result['error']) {
                $successMessage = "Student added successfully.";
                header("Location: /backend/src/index.php");
                exit;
            } else {
                $errorMessage = $result['message'];
            }
        } catch (PDOException $e) {
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
    <title>Add Student</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2>Add New Student</h2>

        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong><?= $errorMessage ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?= $name ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="email" value="<?= $email ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="address" value="<?= $address ?>">
                </div>
            </div>

            <?php if (!empty($successMessage)) : ?>
                <div class="row mb-3">
                    <div class="offset-sm-3 col-sm-6">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><?= $successMessage ?></strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CSRF Token Field -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->generateCsrfToken()); ?>">

            <div class="row mb-3">
                <div class="offset-sm-3 col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-outline-primary" href="/backend/src/index.php" role="button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
