<?php
session_start();
header('Content-Type: application/json');

require_once '../database/dbConnection.php';
require_once '../classes/DonationManager.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['amount']) || !is_numeric($data['amount'])) {
        throw new Exception('Invalid donation amount');
    }

    $database = new Database();
    $conn = $database->getConnection();
    $donationManager = new DonationManager($conn);

    $donationManager->processDonation(
        $_SESSION['user_id'],
        1, 
        $data['amount']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Donation processed successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 