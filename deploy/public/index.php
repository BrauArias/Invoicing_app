<?php
// PowerHelp Landing — sirve index.html para la raiz, WordPress para el resto
if ($_SERVER['REQUEST_URI'] === '/' || rtrim($_SERVER['REQUEST_URI'], '/') === '') {
    readfile(__DIR__ . '/index.html');
    exit;
}
define('WP_USE_THEMES', true);
require __DIR__ . '/wp-blog-header.php';
