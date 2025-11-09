<?php
if (session_status() == PHP_SESSION_NONE) session_start();

/**
 * Ambil daftar produk dari JSON
 */
function get_products()
{
    $path = __DIR__ . '/../storage/products/products.json';
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return $data ?: [];
}

/**
 * Format rupiah
 */
function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Buat token single-use untuk download
 */
function create_download_token($productFile, $productName, $userEmail, $ttl = 3600)
{
    $token = bin2hex(random_bytes(16));
    $data = [];
    $path = __DIR__ . '/../storage/tokens.json';

    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true) ?? [];
    }

    $data[$token] = [
        'file' => $productFile,
        'product_name' => $productName,
        'email' => $userEmail,
        'created_at' => date('Y-m-d H:i:s'),
        'expires' => time() + $ttl
    ];

    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    return $token;
}

/**
 * Validasi token dan hapus setelah dipakai
 */
function validate_and_consume_token($token)
{
    $path = __DIR__ . '/../storage/tokens.json';
    if (!file_exists($path)) return false;

    $data = json_decode(file_get_contents($path), true) ?? [];
    if (!isset($data[$token])) return false;

    $record = $data[$token];

    if ($record['expires'] < time()) {
        unset($data[$token]);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        return false;
    }

    // Hapus token (single-use)
    unset($data[$token]);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

    return $record;
}

/**
 * Logging download
 */
function logDownload($message)
{
    $logPath = __DIR__ . '/../storage/logs/security.log';
    $time = date('Y-m-d H:i:s');
    $line = "[$time] [DOWNLOAD] $message\n";
    file_put_contents($logPath, $line, FILE_APPEND);
}
