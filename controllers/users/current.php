<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(null);
    exit;
}

require_once '../../models/RepoUser.php';
require_once '../../models/RepoUserBook.php';

$userId = (int) $_SESSION['user_id'];
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
$books = $userBookRepo->listOwnedByUser($userId);

echo json_encode([
    'id' => $user->id,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'email' => $user->email,
    'login_count' => $user->login_count,
    'bonus' => $user->bonus,
    'role' => $user->role,
    'books_count' => count($books),
    'books' => $books
]);
