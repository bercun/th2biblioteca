<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once '../../models/RepoUser.php';
require_once '../../models/RepoUserBook.php';

$userId = (int) ($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user'
    ]);
    exit;
}

$userRepo = new RepoUser();
$user = $userRepo->findById($userId);

if (!$user) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

$userBookRepo = new RepoUserBook();

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user->id,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email
    ],
    'purchases' => $userBookRepo->listPurchasesForAdmin($userId)
]);
