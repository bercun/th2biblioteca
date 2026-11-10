<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../models/RepoUserBook.php';
require_once '../../models/RepoReview.php';

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
$rating = (int) ($data['rating'] ?? 0);

if ($bookId <= 0 || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid book or rating'
    ]);
    exit;
}

$userBookRepo = new RepoUserBook();
$reviewRepo = new RepoReview();

if (!$userBookRepo->userOwnsBook($userId, $bookId)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You can review only purchased books'
    ]);
    exit;
}

$review = new Review();
$review->user_id = $userId;
$review->book_id = $bookId;
$review->rating = $rating;
$reviewRepo->save($review);

$stats = $reviewRepo->getBookStats($bookId);

echo json_encode([
    'success' => true,
    'bookId' => $bookId,
    'rating' => $rating,
    'averageRating' => $stats->average_rating,
    'reviewsCount' => $stats->reviews_count
]);
