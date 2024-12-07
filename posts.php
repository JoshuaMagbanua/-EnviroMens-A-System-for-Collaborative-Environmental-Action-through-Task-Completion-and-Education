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
$posts = $postManager->getAllPosts();

foreach($posts as &$post) {
    $reactions = $postManager->getReactionCounts($post['post_id']);
    $userReaction = $postManager->getUserReaction($post['post_id'], $_SESSION['user_id']);
    $post['reactions'] = $reactions;
    $post['user_reaction'] = $userReaction;
}
unset($post); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - EnviroMens</title>
    <link rel="stylesheet" href="../css/posts.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'components/user_navbar.php'; ?>

    <div class="container">
        <div class="posts-container">
            <?php foreach($posts as $post): ?>
                <div class="post-card" onclick='showPostDetail(<?php echo json_encode([
                    "post_id" => $post['post_id'],
                    "username" => $post['username'],
                    "profile_picture" => $post['profile_picture'],
                    "content" => $post['content'],
                    "reference_link" => $post['reference_link'] ?? null,
                    "reactions" => $post['reactions'],
                    "user_reaction" => $post['user_reaction']
                ]); ?>)'>
                    <div class="post-header">
                        <img src="<?php echo isset($post['profile_picture']) && !empty($post['profile_picture']) 
                            ? '../profile_pictures/' . htmlspecialchars($post['profile_picture']) 
                            : '../assets/profile-placeholder.png'; ?>" 
                             alt="Profile" class="profile-pic">
                        <div class="post-info">
                            <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                            <span class="post-date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="post-content">
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>
                    <div class="post-reactions">
                        <span class="likes-count"><?php echo $post['reactions']['likes']; ?> 👍</span>
                        <span class="dislikes-count"><?php echo $post['reactions']['dislikes']; ?> 👎</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="create_post.php" class="create-post-btn">Create Post</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function showPostDetail(post) {
        const sanitizeHTML = (str) => {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        };

        Swal.fire({
            title: `<div class="post-header-popup">
                        <div class="profile-container">
                            <img src="${post.profile_picture ? '../profile_pictures/' + sanitizeHTML(post.profile_picture) : '../assets/profile-placeholder.png'}" />
                            <span class="username">${sanitizeHTML(post.username)}</span>
                        </div>
                    </div>`,
            html: `
                <div class="post-content-popup">
                    <p>${sanitizeHTML(post.content)}</p>
                    ${post.reference_link ? `<a href="${sanitizeHTML(post.reference_link)}" target="_blank" class="reference-link">Reference Link</a>` : ''}
                    <div class="reaction-buttons">
                        <button onclick="reactToPost(${post.post_id}, 'like')" 
                                class="reaction-btn ${post.user_reaction === 'like' ? 'active' : ''}">
                            👍 <span>${post.reactions.likes}</span>
                        </button>
                        <button onclick="reactToPost(${post.post_id}, 'dislike')" 
                                class="reaction-btn ${post.user_reaction === 'dislike' ? 'active' : ''}">
                            👎 <span>${post.reactions.dislikes}</span>
                        </button>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'post-detail-popup',
                closeButton: 'swal2-close-button'
            },
            width: 600
        });
    }

    function reactToPost(postId, reactionType) {
        fetch(`react_to_post.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                reaction: reactionType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>

    <style>
    .post-card {
        cursor: pointer;
    }

    .post-reactions {
        margin-top: 10px;
        display: flex;
        gap: 15px;
    }

    .reaction-buttons {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .reaction-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 20px;
        background: #505823;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .reaction-btn.active {
        background: #85873C;
    }

    .post-header-popup {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-pic-popup {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .post-detail-popup {
        max-width: 600px;
    }
    </style>
</body>
</html> 