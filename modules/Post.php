<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/Auth.php';

// ADD NEW STUDENT
function addStudent($pdo, $name, $email, $address) { 
    try {
        $auth = new Auth($pdo);

        // Encrypt data before storing them in the database
        $encryptedName = $auth->encryptData($name);
        error_log("Encrypted Name: $encryptedName");
        $encryptedEmail = $auth->encryptData($email);
        error_log("Encrypted Email: $encryptedEmail");
        $encryptedAddress = $auth->encryptData($address);
        error_log("Encrypted Address: $encryptedAddress");

        $sql = "INSERT INTO students (name, email, address) VALUES (:name, :email, :address)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $encryptedName,
            ':email' => $encryptedEmail,
            ':address' => $encryptedAddress
        ]);

        $lastId = $pdo->lastInsertId();

        // Fetch the newly added student
        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $lastId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decrypt the address before returning the data
        if ($student) {
            $student['name'] = $auth->decryptData($student['name']);
            $student['email'] = $auth->decryptData($student['email']);
            $student['address'] = $auth->decryptData($student['address']);
        }

        return ['error' => false, 'message' => "Student added successfully", 'student' => $student];
    } catch (Exception $e) {
        return ['error' => true, 'message' => $e->getMessage()];
    }
}



// UPDATE STUDENT INFORMATION
function updateStudent($pdo, $id, $name, $email, $address) {
    try {
        $auth = new Auth($pdo);

        // Encrypt only the address field
        $encryptedName = $auth->encryptData($name);
        $encryptedEmail = $auth->encryptData($email);
        $encryptedAddress = $auth->encryptData($address);

        $sql = "UPDATE students SET name = :name, email = :email, address = :address WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $encryptedName,          
            ':email' => $encryptedEmail,                  
            ':address' => $encryptedAddress, 
            ':id' => $id
        ]);

        if ($stmt->rowCount() === 0) {
            return ['error' => true, 'message' => "No changes made or student with ID $id is not found"];
        }

        $student = getStudentById($pdo, $id);

        return ['error' => false, 'message' => "Student updated successfully", 'student' => $student];
    } catch (PDOException $e) {
        return ['error' => true, 'message' => "Failed to update student: " . $e->getMessage()];
    }
}


// DELETE STUDENT FROM THE TABLE
function deleteStudent($pdo, $id) {
    try {
        $sql = "DELETE FROM students WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['error' => true, 'message' => "Student with ID $id not found"];
        }

        return ['error' => false, 'message' => "Student deleted successfully"];
    } catch (PDOException $e) {
        return ['error' => true, 'message' => "Failed to delete student: " . $e->getMessage()];
    }
}
?>
