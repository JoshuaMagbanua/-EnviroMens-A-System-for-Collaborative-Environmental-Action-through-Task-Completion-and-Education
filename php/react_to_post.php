<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/PostManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['post_id']) || !isset($data['reaction'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$postManager = new PostManager($conn);

$success = false;
if ($data['reaction'] === 'like') {
    $success = $postManager->likePost($data['post_id'], $_SESSION['user_id']);
} else if ($data['reaction'] === 'dislike') {
    $success = $postManager->dislikePost($data['post_id'], $_SESSION['user_id']);
}

echo json_encode(['success' => $success]); 