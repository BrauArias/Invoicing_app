<?php
// PowerHelp Landing — portada personalizada (WordPress template hierarchy)
$file = get_stylesheet_directory() . '/powerhelp-landing.html';
if (file_exists($file)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($file);
    exit;
}
get_template_part('index');
