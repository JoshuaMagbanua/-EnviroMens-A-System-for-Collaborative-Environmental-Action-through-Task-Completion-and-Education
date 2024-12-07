<?php
session_start();
header('Content-Type: application/json');

require_once '../database/dbConnection.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

if (!isset($_FILES['evidence']) || !isset($_POST['task_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required data'
    ]);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $taskManager = new TaskManager($conn);

    $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $userId = $_SESSION['user_id'];

    // Validate file
    $file = $_FILES['evidence'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    
    // Debug logging
    error_log("File upload attempt - Type: " . $file['type'] . ", Size: " . $file['size']);

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Please upload an image (JPG, JPEG, PNG, or GIF)'
        ]);
        exit();
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode([
            'success' => false,
            'message' => 'File size must be less than 5MB'
        ]);
        exit();
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../task_evidence/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'evidence_' . $taskId . '_' . $userId . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    // Debug logging
    error_log("Attempting to move file to: " . $targetPath);

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Update task status in database
        if ($taskManager->submitTaskEvidence($taskId, $userId, $filename)) {
            echo json_encode([
                'success' => true,
                'message' => 'Evidence uploaded successfully'
            ]);
        } else {
            // If database update fails, remove the uploaded file
            unlink($targetPath);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update task status in database'
            ]);
        }
    } else {
        $uploadError = error_get_last();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload file: ' . ($uploadError ? $uploadError['message'] : 'Unknown error')
        ]);
    }

} catch (Exception $e) {
    error_log("Upload Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 