<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/modules/Get.php';
require_once __DIR__ . '/modules/Post.php';
require_once __DIR__ . '/modules/Auth.php';


// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

// Routing logic
switch ($method) {

    case 'GET':
        if ($path === '/students') {
            echo json_encode(getAllStudents($pdo));
        } elseif (preg_match('#^/students/(\d+)$#', $path, $matches)) {
            $id = (int)$matches[1];
            echo json_encode(getStudentById($pdo, $id));
        } else {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Endpoint not found']);
        }
        break;

    case 'POST':
        if ($path === '/addStudent') {
            if (!isset($input['name'], $input['email'], $input['address'])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid input']);
            } else {
                $result = addStudent($pdo, $input['name'], $input['email'], $input['address']);
                echo json_encode($result);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Endpoint not found']);
        }
        break;

    case 'PUT':
        if (preg_match('#^/updateStudent/(\d+)$#', $path, $matches)) {
            $id = (int)$matches[1];
            if (!isset($input['name'], $input['email'], $input['address'])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Invalid input']);
            } else {
                $result = updateStudent($pdo, $id, $input['name'], $input['email'], $input['address']);
                echo json_encode($result);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Endpoint not found']);
        }
        break;

    case 'DELETE':
        if (preg_match('#^/delStudent/(\d+)$#', $path, $matches)) {
            $id = (int)$matches[1];
            echo json_encode(deleteStudent($pdo, $id));
        } else {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Endpoint not found']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Method not allowed']);
        break;
}
?>
