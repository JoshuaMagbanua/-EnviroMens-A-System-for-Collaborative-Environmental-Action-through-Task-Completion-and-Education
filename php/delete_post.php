<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/PostManager.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$postManager = new PostManager($conn);

$success = $postManager->deletePost($postId, $_SESSION['user_id']);

header('Content-Type: application/json');
echo json_encode(['success' => $success]); 