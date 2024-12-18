<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/Auth.php';

// GET ALL STUDENTS
function getAllStudents($pdo) {
    try {
        $auth = new Auth($pdo);
    
        $sql = "SELECT * FROM students";
        $stmt = $pdo->query($sql);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Decrypt the data before returning it
        foreach ($students as &$student) {
            $student['name'] = $auth->decryptData($student['name']);
            $student['email'] = $auth->decryptData($student['email']);
            $student['address'] = $auth->decryptData($student['address']);
        }
    
        return $students;
    } catch (PDOException $e) {
        return ['error' => true, 'message' => "Failed to fetch students: " . $e->getMessage()];
    }
}

// GET A STUDENT BY ID
function getStudentById($pdo, $id) {
    try {
        $auth = new Auth($pdo);

        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return ['error' => true, 'message' => "Student with ID $id is not found"];
        }

        $student['name'] = $auth->decryptData($student['name']);
        $student['email'] = $auth->decryptData($student['email']);
        $student['address'] = $auth->decryptData($student['address']);

        return $student;
    } catch (PDOException $e) {
        return ['error' => true, 'message' => $e->getMessage()];
    }
}

?>