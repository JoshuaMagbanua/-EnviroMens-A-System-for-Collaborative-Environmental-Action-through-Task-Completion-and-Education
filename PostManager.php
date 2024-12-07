<?php
class PostManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createPost($postData) {
        try {
            $query = "INSERT INTO posts (user_id, content, reference_link, created_at) 
                     VALUES (:user_id, :content, :reference_link, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $postData['user_id'],
                ':content' => $postData['content'],
                ':reference_link' => $postData['references']
            ]);
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllPosts() {
        try {
            $query = "SELECT p.*, u.username, u.profile_picture 
                     FROM posts p 
                     JOIN users u ON p.user_id = u.user_id 
                     ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function likePost($postId, $userId) {
        try {
            // Check if user already reacted
            $checkQuery = "SELECT * FROM post_reactions WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing reaction
                $query = "UPDATE post_reactions SET reaction_type = 'like' 
                         WHERE post_id = :post_id AND user_id = :user_id";
            } else {
                // Insert new reaction
                $query = "INSERT INTO post_reactions (post_id, user_id, reaction_type) 
                         VALUES (:post_id, :user_id, 'like')";
            }

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function dislikePost($postId, $userId) {
        try {
            // Similar to likePost but with 'dislike' type
            $checkQuery = "SELECT * FROM post_reactions WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $query = "UPDATE post_reactions SET reaction_type = 'dislike' 
                         WHERE post_id = :post_id AND user_id = :user_id";
            } else {
                $query = "INSERT INTO post_reactions (post_id, user_id, reaction_type) 
                         VALUES (:post_id, :user_id, 'dislike')";
            }

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getReactionCounts($postId) {
        try {
            $query = "SELECT reaction_type, COUNT(*) as count 
                     FROM post_reactions 
                     WHERE post_id = :post_id 
                     GROUP BY reaction_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':post_id' => $postId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $counts = ['likes' => 0, 'dislikes' => 0];
            foreach ($results as $result) {
                if ($result['reaction_type'] === 'like') {
                    $counts['likes'] = $result['count'];
                } else {
                    $counts['dislikes'] = $result['count'];
                }
            }
            return $counts;
        } catch(PDOException $e) {
            return ['likes' => 0, 'dislikes' => 0];
        }
    }

    public function getUserReaction($postId, $userId) {
        try {
            $query = "SELECT reaction_type FROM post_reactions 
                     WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['reaction_type'] : null;
        } catch(PDOException $e) {
            return null;
        }
    }

    public function getPostAnalytics($postId) {
        try {
            // Get reaction statistics
            $reactionQuery = "SELECT 
                SUM(CASE WHEN reaction_type = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) as dislikes,
                COUNT(DISTINCT user_id) as total_reactions
            FROM post_reactions 
            WHERE post_id = :post_id";
            
            $reactionStmt = $this->conn->prepare($reactionQuery);
            $reactionStmt->bindParam(':post_id', $postId);
            $reactionStmt->execute();
            $reactions = $reactionStmt->fetch(PDO::FETCH_ASSOC);

            // Get user details who reacted
            $usersQuery = "SELECT 
                u.username, u.profile_picture, pr.reaction_type, pr.created_at
            FROM post_reactions pr
            JOIN users u ON pr.user_id = u.user_id
            WHERE pr.post_id = :post_id
            ORDER BY pr.created_at DESC";

            $usersStmt = $this->conn->prepare($usersQuery);
            $usersStmt->bindParam(':post_id', $postId);
            $usersStmt->execute();
            $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'statistics' => $reactions,
                'users' => $users
            ];
        } catch (PDOException $e) {
            return [
                'statistics' => [
                    'likes' => 0,
                    'dislikes' => 0,
                    'total_reactions' => 0
                ],
                'users' => []
            ];
        }
    }

    public function getUserPosts($userId) {
        try {
            $query = "SELECT p.*, u.username, u.profile_picture,
                     (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as total_reactions
                     FROM posts p
                     JOIN users u ON p.user_id = u.user_id
                     WHERE p.user_id = :user_id
                     ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function deletePost($postId, $userId) {
        try {
            // First check if the user owns the post
            $checkQuery = "SELECT user_id FROM posts WHERE post_id = :post_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':post_id', $postId);
            $checkStmt->execute();
            $post = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($post && $post['user_id'] == $userId) {
                // Delete reactions first (foreign key constraint)
                $deleteReactionsQuery = "DELETE FROM post_reactions WHERE post_id = :post_id";
                $deleteReactionsStmt = $this->conn->prepare($deleteReactionsQuery);
                $deleteReactionsStmt->bindParam(':post_id', $postId);
                $deleteReactionsStmt->execute();

                // Then delete the post
                $deletePostQuery = "DELETE FROM posts WHERE post_id = :post_id";
                $deletePostStmt = $this->conn->prepare($deletePostQuery);
                $deletePostStmt->bindParam(':post_id', $postId);
                
                return $deletePostStmt->execute();
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
}
?> 