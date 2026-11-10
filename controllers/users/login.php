<?php

session_start();
header('Content-Type: application/json');
require_once '../../models/RepoUser.php';

$data = json_decode(
    file_get_contents('php://input'),
    true
);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$userRepo = new RepoUser();
$user = $userRepo->findByEmail($email);

if (
    !$user ||
    !password_verify($password, (string) $user->password)
) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid credentials'
    ]);
    exit;
}

$_SESSION['user_id'] = $user->id;
$userRepo->incrementLoginCount((int) $user->id);

echo json_encode([
    'success' => true
]);
