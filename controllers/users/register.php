<?php

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../models/RepoUser.php';

$data = json_decode(
    file_get_contents('php://input'),
    true
);

$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (
    $firstName === '' ||
    $lastName === '' ||
    $email === '' ||
    $password === ''
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email'
    ]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least 8 characters'
    ]);
    exit;
}

$userRepo = new RepoUser();

if ($userRepo->emailExists($email)) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'User with such email already exists'
    ]);
    exit;
}

$user = new User();
$user->first_name = $firstName;
$user->last_name = $lastName;
$user->email = $email;
$user->password = password_hash($password, PASSWORD_DEFAULT);

$userId = $userRepo->create($user);
$_SESSION['user_id'] = $userId;

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user->id,
        'firstName' => $user->first_name,
        'lastName' => $user->last_name,
        'email' => $user->email,
        'cardNumber' => null,
        'loginCount' => $user->login_count,
        'role' => $user->role,
        'books' => []
    ]
]);
