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
    
    // public function hashPassword($password) {
    //     return password_hash($password, PASSWORD_BCRYPT);
    // }

    // public function verifyPassword($password, $hash) {
    //     return password_verify($password, $hash);
    // }

    //  // Check if the user is authenticated
    // public function isAuthenticated() {
    //     return isset($_SESSION['admin_id']); 
    // }

    // Hash a password with a 22-character salt
    public function hashPassword($password) {
        $salt = $this->generateSalt(22); // Generate a 22-character salt
        $options = [
            'cost' => 10, // Adjust cost for performance (default is 10)
            'salt' => $salt, // Add the custom salt
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    // Generate a random 22-character salt
    private function generateSalt($length = 22) {
        return substr(str_replace('+', '.', base64_encode(random_bytes($length))), 0, $length);
    }

    // Verify a password
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Check if the user is authenticated
    public function isAuthenticated() {
        return isset($_SESSION['admin_id']); 
    }
}
?>
