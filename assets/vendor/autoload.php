<?php
/**
 * Simple Autoloader for PHPMailer and Vendor Classes
 * --------------------------------------------------
 * Agar kamu bisa include dengan:
 * require_once 'assets/vendor/autoload.php';
 */

spl_autoload_register(function ($class) {
    $prefix = 'PHPMailer\\PHPMailer\\';
    $base_dir = __DIR__ . '/phpmailer/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Bukan class PHPMailer
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
?>
