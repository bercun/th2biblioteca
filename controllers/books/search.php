<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../models/RepoBook.php';

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode([]);
    exit;
}

$bookRepo = new RepoBook();

echo json_encode(
    $bookRepo->search($query)
);
