<?php
function clean_input($data) {
    if (is_array($data)) return array_map('clean_input', $data);
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitize_globals() {
    $_GET = clean_input($_GET);
    $_POST = clean_input($_POST);
    $_COOKIE = clean_input($_COOKIE);
}
sanitize_globals();
