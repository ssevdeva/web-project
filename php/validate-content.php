<?php
require_once '../htmlpurifier-4.15.0/library/HTMLPurifier.auto.php'; // Path to HTML Purifier library

function validateAndSanitizeSlides($slides) {
    // Sanitize the user-input HTML using HTML Purifier
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $sanitizedSlides = array_map(function ($slide) use ($purifier) {
        return $purifier->purify($slide);
    }, $slides);

    return $sanitizedSlides;
}
?>
