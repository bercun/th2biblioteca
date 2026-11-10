<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once '../../models/RepoBook.php';

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

$bookRepo = new RepoBook();

if (!$bookRepo->findById($bookId)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Book not found'
    ]);
    exit;
}

$bookRepo->delete($bookId);

echo json_encode([
    'success' => true,
    'message' => 'Book deleted'
]);
