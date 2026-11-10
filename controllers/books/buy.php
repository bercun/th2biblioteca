<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../models/RepoUser.php';
require_once '../../models/RepoBook.php';
require_once '../../models/RepoUserBook.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'You must log in first'
    ]);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$data = json_decode(
    file_get_contents('php://input'),
    true
);

$bookId = (int) ($data['bookId'] ?? 0);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid book'
    ]);
    exit;
}

$userRepo = new RepoUser();
$bookRepo = new RepoBook();
$userBookRepo = new RepoUserBook();

$user = $userRepo->findById($userId);

if (!$user) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

$book = $bookRepo->findForPurchase($bookId);

if (!$book) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Book not found'
    ]);
    exit;
}

if ($userBookRepo->userOwnsBook($userId, $bookId)) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'You already own this book'
    ]);
    exit;
}

$purchasedAt = $userBookRepo->purchase($userId, $bookId);
$userRepo->addBonus($book->bonus, $userId);

echo json_encode([
    'success' => true,
    'message' => 'Book added successfully',
    'book' => [
        'id' => $book->id,
        'title' => $book->title,
        'author' => $book->author,
        'bonus' => $book->bonus,
        'price' => $book->price,
        'purchased_at' => $purchasedAt
    ],
    'booksCount' => $userBookRepo->countByUser($userId),
    'bonus' => $userRepo->getBonus($userId)
]);
