<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Ambil daftar produk dari JSON
 * @return array
 */
function get_products(): array
{
    $path = __DIR__ . '/../storage/products/products.json';

    if (!file_exists($path)) {
        error_log("❌ File products.json tidak ditemukan di: $path");
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("❌ Error decoding JSON products.json: " . json_last_error_msg());
        return [];
    }

    return $data ?: [];
}

/**
 * Format rupiah (Rp 199.000)
 */
function rupiah(float|int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Buat token single-use untuk download
 */
function create_download_token(string $productFile, string $productName, string $userEmail, int $ttl = 3600): string
{
    $token = bin2hex(random_bytes(16));
    $path = __DIR__ . '/../storage/tokens.json';

    // Baca file token lama (jika ada)
    $data = [];
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true) ?? [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("❌ Error decoding tokens.json: " . json_last_error_msg());
            $data = [];
        }
    }

    // Tambahkan token baru
    $data[$token] = [
        'file' => $productFile,
        'product_name' => $productName,
        'email' => $userEmail,
        'created_at' => date('Y-m-d H:i:s'),
        'expires' => time() + $ttl
    ];

    // Simpan file tokens.json
    if (false === file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT))) {
        error_log("❌ Gagal menulis token ke $path");
    }

    return $token;
}

/**
 * Validasi token dan hapus setelah dipakai (single-use)
 */
function validate_and_consume_token(string $token): false|array
{
    $path = __DIR__ . '/../storage/tokens.json'; // ❗ diperbaiki (tadinya tanpa '..')

    if (!file_exists($path)) {
        error_log("❌ File tokens.json tidak ditemukan di: $path");
        return false;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true) ?? [];

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("❌ Error decoding tokens.json: " . json_last_error_msg());
        return false;
    }

    if (!isset($data[$token])) return false;

    $record = $data[$token];

    // Hapus token jika expired
    if ($record['expires'] < time()) {
        unset($data[$token]);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        error_log("⚠️ Token expired: $token");
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
function logDownload(string $message): void
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);

    $logPath = $logDir . '/security.log';
    $time = date('Y-m-d H:i:s');
    $line = "[$time] [DOWNLOAD] $message\n";
    
    if (false === file_put_contents($logPath, $line, FILE_APPEND)) {
        error_log("❌ Gagal menulis log ke $logPath");
    }
}
