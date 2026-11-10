<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once __DIR__ . '/book_image.php';
require_once '../../models/RepoBook.php';
require_once '../../models/RepoBookCategory.php';

$data = getAdminBookRequestData();

$title = $data['title'];
$author = $data['author'];
$isbn = $data['isbn'];
$description = $data['description'];
$categoryId = $data['categoryId'];
$bonus = $data['bonus'];
$price = $data['price'];

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

$categoryRepo = new RepoBookCategory();

if (!$categoryRepo->exists($categoryId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category'
    ]);
    exit;
}

$book = new Book();
$book->title = $title;
$book->author = $author;
$book->isbn = $isbn === '' ? null : $isbn;
$book->description = $description;
$book->category_id = $categoryId;
$book->image = $image;
$book->bonus = $bonus;
$book->price = $price;

$bookRepo = new RepoBook();

try {
    $bookId = $bookRepo->create($book);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to create book (ISBN may already exist)'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'bookId' => $bookId,
    'image' => $book->image,
    'message' => 'Book created'
]);
