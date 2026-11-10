<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../models/RepoBook.php';

$season = strtolower(trim($_GET['season'] ?? ''));
$allowedSeasons = ['winter', 'spring', 'summer', 'autumn'];

if ($season !== '' && !in_array($season, $allowedSeasons, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid season'
    ]);
    exit;
}

$bookRepo = new RepoBook();

echo json_encode(
    $bookRepo->listBySeason($season === '' ? null : $season)
);
