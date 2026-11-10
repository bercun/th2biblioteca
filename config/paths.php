<?php

/**
 * URL base path of the project (e.g. "" when served from the web root,
 * "/biblioteca" when served from a subfolder).
 */
function basePath(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $root = realpath(__DIR__ . '/..');
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $base = '';

    if ($root !== false && $docRoot !== false) {
        $root = str_replace('\\', '/', $root);
        $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');

        if (strncasecmp($root, $docRoot, strlen($docRoot)) === 0) {
            $base = rtrim(substr($root, strlen($docRoot)), '/');
        }
    }

    return $base;
}
