<?php
require_once __DIR__ . '/../modules/Get.php';
require_once __DIR__ . '/../modules/Post.php';
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../modules/Auth.php';

session_start();

// Instantiate Auth
$auth = new Auth($pdo);

// Redirect to login if not authenticated
if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit;
}

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all students
$students = getAllStudents($pdo);

// Handle deletion
if (isset($_GET['id'], $_GET['csrf_token'])) {
    if ($_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: /backend/src/index.php?error=Invalid CSRF token");
        exit;
    }

    $id = $_GET['id'];
    try {
        $rowsDeleted = deleteStudent($pdo, $id);
        if ($rowsDeleted['error']) {
            header("Location: /backend/src/index.php?error=" . urlencode($rowsDeleted['message']));
        } else {
            header("Location: /backend/src/index.php?message=" . urlencode($rowsDeleted['message']));
        }
    } catch (Exception $e) {
        header("Location: /backend/src/index.php?error=" . urlencode($e->getMessage()));
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Student List</h2>
        <a class="btn btn-primary" href="/backend/src/create.php" role="button">+ New Student</a>
        <br>
        <?php if (isset($_GET['message'])) : ?>
            <div class="alert alert-success mt-3">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php elseif (isset($_GET['error'])) : ?>
            <div class="alert alert-danger mt-3">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="/backend/src/edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="btn btn-danger btn-sm" href="/backend/src/index.php?id=<?= $row['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
