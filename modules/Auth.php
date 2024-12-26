<?php
require_once __DIR__ . '/../config/connection.php';

class Auth {
    private $encryptionKey;

    public function __construct($pdo) {
        $this->encryptionKey = 'sampleKey01'; // Secure key for AES encryption

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Encrypt data for storage
    public function encryptData($data) {
        $key = substr(hash('sha256', $this->encryptionKey, true), 0, 32); // 32-byte key for AES-256
        $iv = openssl_random_pseudo_bytes(16); // 16-byte IV
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    // Decrypt data
    public function decryptData($data) {
        $key = substr(hash('sha256', $this->encryptionKey, true), 0, 32);
        list($encryptedData, $iv) = explode('::', base64_decode($data), 2);
        $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    // Hash a password securely
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
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
