<?php
$config = require __DIR__ . '/../config/app_config.php';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';

if ($origin && !in_array(parse_url($origin, PHP_URL_HOST), $config['allowed_origins'])) {
    http_response_code(403);
    die("Forbidden origin");
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(curl|bot|crawler|wget)/i', $ua)) {
    file_put_contents(__DIR__ . '/../logs/access.log', "[".date('Y-m-d H:i:s')."] Blocked UA: $ua\n", FILE_APPEND);
    http_response_code(403);
    die("Suspicious request blocked.");
}
