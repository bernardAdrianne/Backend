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

    // Encrypt data for storage
    public function encryptData($data) {
        $key = substr(hash('sha256', $this->encryptionKey, true), 0, 32); 
        $iv = openssl_random_pseudo_bytes(16);

        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new Exception("Encryption failed.");
        }

        return base64_encode($encrypted . '::' . $iv);
    }

    // Decrypt data
    public function decryptData($data) {
    $key = substr(hash('sha256', $this->encryptionKey, true), 0, 32);

    try {
        $decodedData = base64_decode($data);
        if ($decodedData === false) {
            throw new Exception("Base64 decoding failed.");
        }

        $parts = explode('::', $decodedData, 2); // Limit to 2 parts
        if (count($parts) !== 2) {
            throw new Exception("Invalid data format. Expected ciphertext and IV.");
        }

        list($encryptedData, $iv) = $parts;

        if (strlen($iv) !== 16) {
            throw new Exception("Invalid IV length. Must be 16 bytes.");
        }

        $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new Exception("Decryption failed.");
        }

        return $decrypted;
    } catch (Exception $e) {
        error_log("Decryption error: " . $e->getMessage());
        return null;
    }
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
