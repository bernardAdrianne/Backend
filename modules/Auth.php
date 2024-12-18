<?php
require_once __DIR__ . '/../config/connection.php';

class Auth {
    private $encryptionKey;

    public function __construct($pdo) {
        $this->encryptionKey = 'sampleKey01'; 

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function encryptData($data) {
        return openssl_encrypt(
            $data, 
            'AES-256-CBC', 
            $this->encryptionKey, 
            0, 
            substr(hash('sha256', $this->encryptionKey), 0, 16)
        );
    }

    public function decryptData($data) {
        return openssl_decrypt(
            $data, 
            'AES-256-CBC', 
            $this->encryptionKey, 
            0, 
            substr(hash('sha256', $this->encryptionKey), 0, 16)
        );
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

     // Check if the user is authenticated
    public function isAuthenticated() {
        return isset($_SESSION['admin_id']); 
    }

    public function generateCsrfToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            throw new Exception('Invalid CSRF token');
        }
    }
}
?>
