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

    if (empty($name) || empty($email) || empty($address)) {
        $errorMessage = "All the fields are required.";
    } else {
        try {
            // Decrypt data from client-side encryption
            $decryptedName = $auth->decryptData($name);
            $decryptedEmail = $auth->decryptData($email);
            $decryptedAddress = $auth->decryptData($address);

            error_log("Decrypted Name: $decryptedName");
            error_log("Decrypted Email: $decryptedEmail");
            error_log("Decrypted Address: $decryptedAddress");

            $result = addStudent($pdo, $decryptedName, $decryptedEmail, $decryptedAddress);

            if (!$result['error']) {
                $successMessage = "Student added successfully.";
                header("Location: /backend/src/index.php?message=" . urlencode($result['message']));
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script>
        const key = CryptoJS.enc.Utf8.parse('sampleKey01'); 
        const iv = CryptoJS.enc.Utf8.parse(CryptoJS.MD5('sampleKey01').toString()); 

        function encryptData(data) {
            const encrypted = CryptoJS.AES.encrypt(data, key, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            });
            return encrypted.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('form').addEventListener('submit', function (e) {
                e.preventDefault();

                const nameField = document.querySelector('[name="name"]');
                const emailField = document.querySelector('[name="email"]');
                const addressField = document.querySelector('[name="address"]');

                // Encrypt data before submission
                nameField.value = encryptData(nameField.value);
                emailField.value = encryptData(emailField.value);
                addressField.value = encryptData(addressField.value);

                e.target.submit(); // Submit the form
            });
        });
    </script>
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
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>">
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
