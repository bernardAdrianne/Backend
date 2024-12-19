<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/modules/Get.php';
require_once __DIR__ . '/modules/Post.php';
require_once __DIR__ . '/modules/Auth.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

// Extract CSRF token from Authorization header
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Instantiate Auth
$auth = new Auth($pdo);

try {
    // Skip CSRF validation for login and register
    if ($method !== 'GET' && $path !== '/login' && $path !== '/register') {
        $csrfToken = getBearerToken();
        if (!$csrfToken) {
            throw new Exception("No CSRF token provided");
        }
        $auth->validateCsrfToken($csrfToken);
    }

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
            if ($path === '/login') {
                $email = $input['email'] ?? null;
                $password = $input['password'] ?? null;

                if (!$email || !$password) {
                    http_response_code(400);
                    echo json_encode(['error' => true, 'message' => 'Email and password are required.']);
                } else {
                    try {
                        $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE email = :email");
                        $stmt->execute([':email' => $email]);
                        $admin = $stmt->fetch();

                        if (!$admin || !$auth->verifyPassword($password, $admin['password'])) {
                            http_response_code(401);
                            echo json_encode(['error' => true, 'message' => 'Invalid credentials']);
                        } else {
                            $_SESSION['admin_id'] = $admin['id'];
                            $csrfToken = $auth->generateCsrfToken();

                            http_response_code(200);
                            echo json_encode(['message' => 'Login successful', 'csrf_token' => $csrfToken]);
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['error' => true, 'message' => 'Server error: ' . $e->getMessage()]);
                    }
                }
            } elseif ($path === '/register') {
                $email = $input['email'] ?? null;
                $username = $input['username'] ?? null;
                $password = $input['password'] ?? null;

                if (!$email || !$username || !$password) {
                    http_response_code(400);
                    echo json_encode(['error' => true, 'message' => 'Email, username, and password are required.']);
                } else {
                    try {
                        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = :email");
                        $stmt->execute([':email' => $email]);
                        $admin = $stmt->fetch();

                        if ($admin) {
                            http_response_code(409);
                            echo json_encode(['error' => true, 'message' => 'Admin already exists.']);
                        } else {
                            $passwordHash = $auth->hashPassword($password);
                            $stmt = $pdo->prepare("INSERT INTO admin (email, username, password) VALUES (:email, :username, :password)");
                            $stmt->execute([':email' => $email, ':username' => $username, ':password' => $passwordHash]);

                            http_response_code(201);
                            echo json_encode(['message' => 'Registration successful']);
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['error' => true, 'message' => 'Server error: ' . $e->getMessage()]);
                    }
                }
            } elseif ($path === '/addStudent') {
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
                $csrfToken = getBearerToken();

                if (!$auth->validateCsrfToken($csrfToken)) {
                    http_response_code(403);
                    echo json_encode(['error' => true, 'message' => 'Invalid CSRF token']);
                } else {
                    echo json_encode(deleteStudent($pdo, $id));
                }
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
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
