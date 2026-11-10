<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once __DIR__ . '/book_image.php';
require_once '../../models/RepoBook.php';
require_once '../../models/RepoBookCategory.php';

$data = getAdminBookRequestData();

$bookId = $data['bookId'];
$title = $data['title'];
$author = $data['author'];
$isbn = $data['isbn'];
$description = $data['description'];
$categoryId = $data['categoryId'];
$bonus = $data['bonus'];
$price = $data['price'];

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid book'
    ]);
    exit;
}

if (
    $title === '' ||
    $author === '' ||
    $description === '' ||
    $categoryId <= 0
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Title, author, description and category are required'
    ]);
    exit;
}

if ($bonus < 0 || $price < 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Price and bonus cannot be negative'
    ]);
    exit;
}

try {
    $image = resolveBookImagePath($data['image'], $data['file']);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

$bookRepo = new RepoBook();
$categoryRepo = new RepoBookCategory();
$book = $bookRepo->findById($bookId);

if (!$book) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Book not found'
    ]);
    exit;
}

if (!$categoryRepo->exists($categoryId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category'
    ]);
    exit;
}

$book->title = $title;
$book->author = $author;
$book->isbn = $isbn === '' ? null : $isbn;
$book->description = $description;
$book->category_id = $categoryId;
$book->image = $image;
$book->bonus = $bonus;
$book->price = $price;

try {
    $bookRepo->update($book);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to update book (ISBN may already exist)'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'image' => $book->image,
    'message' => 'Book updated'
]);
