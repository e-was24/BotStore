<?php
if (session_status() == PHP_SESSION_NONE) session_start();

/**
 * Ambil daftar produk dari JSON.
 * JSON path: storage/products/products.json
 */
function get_products()
{
    $path = __DIR__ . '/../storage/products/products.json';
    if (!file_exists($path)) {
        return []; // jika file tidak ada, kembalikan array kosong
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

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
 * Create a single-use token after "payment".
 * tokens.json digunakan untuk menyimpan token: { "token": { "file": "...", "expires": timestamp } }
 */
function create_download_token($filename, $ttl_seconds = 3600)
{
    $token = bin2hex(random_bytes(16));
    $data = [];
    $path = __DIR__ . '/../storage/tokens.json';
    if (file_exists($path)) $data = json_decode(file_get_contents($path), true) ?? [];
    $data[$token] = [
        'file' => $filename,
        'expires' => time() + $ttl_seconds
    ];
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    return $token;
}

/**
 * Validate token dan hapus setelah dipakai (single-use)
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
    return $record['file'];
}
