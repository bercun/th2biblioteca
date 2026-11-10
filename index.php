<?php

/**
 * Front controller / enrutador.
 * Rutas: /biblioteca/index.php/{ruta}  o  /biblioteca/index.php?route={ruta}
 */

declare(strict_types=1);

require_once __DIR__ . '/config/paths.php';

$routes = [
    'home' => 'front/index.html',

    'users/register' => 'controllers/users/register.php',
    'users/login' => 'controllers/users/login.php',
    'users/logout' => 'controllers/users/logout.php',
    'users/current' => 'controllers/users/current.php',

    'books/search' => 'controllers/books/search.php',
    'books/favorites' => 'controllers/books/favorites.php',
    'books/buy' => 'controllers/books/buy.php',
    'books/review' => 'controllers/books/review.php',

    'admin/books_list' => 'controllers/admin/books_list.php',
    'admin/books_create' => 'controllers/admin/books_create.php',
    'admin/books_update' => 'controllers/admin/books_update.php',
    'admin/books_delete' => 'controllers/admin/books_delete.php',
    'admin/users_list' => 'controllers/admin/users_list.php',
    'admin/users_purchases' => 'controllers/admin/users_purchases.php',
];

$route = resolveRoute();

if ($route === '' || $route === 'home') {
    serveHome();
    exit;
}

if (!isset($routes[$route])) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Route not found',
        'route' => $route,
    ]);
    exit;
}

dispatchController($routes[$route]);

/**
 * Resolve route from PATH_INFO or ?route=
 */
function resolveRoute(): string
{
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    if (is_string($pathInfo) && $pathInfo !== '' && $pathInfo !== '/') {
        return trim($pathInfo, '/');
    }

    return trim((string) ($_GET['route'] ?? 'home'), '/');
}

/**
 * Serve the front library page.
 */
function serveHome(): void
{
    $view = __DIR__ . '/front/index.html';

    if (!is_file($view)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Front view not found';
        return;
    }

    header('Content-Type: text/html; charset=utf-8');
    $html = (string) file_get_contents($view);
    echo str_replace('"/biblioteca/', '"' . basePath() . '/', $html);
}

/**
 * Include a controller keeping relative require paths working.
 */
function dispatchController(string $relativePath): void
{
    $absolute = __DIR__ . '/' . $relativePath;

    if (!is_file($absolute)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Controller not found',
        ]);
        return;
    }

    $previousCwd = getcwd();
    chdir(dirname($absolute));
    require $absolute;

    if ($previousCwd !== false) {
        chdir($previousCwd);
    }
}
