<?php

// Guard for admin API endpoints in controllers/admin/*.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../models/RepoUser.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'You must log in first'
    ]);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userRepo = new RepoUser();
$adminUser = $userRepo->findById($userId);

if (!$adminUser || $adminUser->role !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Admin access required'
    ]);
    exit;
}
