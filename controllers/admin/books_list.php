<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once '../../models/RepoBook.php';

$bookRepo = new RepoBook();

echo json_encode([
    'success' => true,
    'books' => $bookRepo->listForAdmin()
]);
