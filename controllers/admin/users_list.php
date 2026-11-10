<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/require_admin.php';
require_once '../../models/RepoUser.php';

$userRepo = new RepoUser();

echo json_encode([
    'success' => true,
    'users' => $userRepo->listForAdmin()
]);
