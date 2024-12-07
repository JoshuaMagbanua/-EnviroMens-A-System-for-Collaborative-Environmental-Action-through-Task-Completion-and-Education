<?php
session_start();
require_once '../database/dbConnection.php';
require_once '../classes/PostManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$postManager = new PostManager($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postData = [
        'content' => trim($_POST['content']),
        'references' => trim($_POST['references']),
        'user_id' => $_SESSION['user_id']
    ];

    $result = $postManager->createPost($postData);
    if ($result['success']) {
        header("Location: posts.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - EnviroMens</title>
    <link rel="stylesheet" href="../css/create_post.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'components/user_navbar.php'; ?>

    <div class="container">
        <h1>Create Post</h1>
        <form class="post-form" method="POST" action="">
            <div class="form-group">
                <textarea name="content" placeholder="Write your post here..." required></textarea>
            </div>
            <div class="form-group">
                <input type="url" name="references" placeholder="Add references (URL)">
            </div>
            <div class="button-group">
                <button type="submit" class="post-btn">Post</button>
            </div>
        </form>
    </div>
</body>
</html> 