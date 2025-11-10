<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Ambil daftar produk dari JSON
 */
function get_products()
{
    $path = __DIR__ . '/../storage/products/products.json';

    if (!file_exists($path)) {
        error_log("❌ File products.json tidak ditemukan di: $path");
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("❌ Error decoding products.json: " . json_last_error_msg());
        return [];
    }

    if (!is_array($data)) {
        error_log("⚠️ Format data JSON tidak valid (bukan array).");
        return [];
    }

    return $data;
}

/**
 * Format angka ke format Rupiah
 */
function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Buat token single-use untuk download file produk
 */
function create_download_token($productFile, $productName, $userEmail, $ttl = 3600)
{
    $token = bin2hex(random_bytes(16));
    $storageDir = __DIR__ . '/../storage';
    $path = $storageDir . '/tokens.json';

    if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

    $data = [];
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true) ?: [];
    }

    $data[$token] = [
        'file'         => $productFile,
        'product_name' => $productName,
        'email'        => $userEmail,
        'created_at'   => date('Y-m-d H:i:s'),
        'expires'      => time() + $ttl
    ];

    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

    return $token;
}

/**
 * Validasi token dan hapus setelah dipakai (single-use)
 */
function validate_and_consume_token($token)
{
    $path = __DIR__ . '/../storage/tokens.json';
    if (!file_exists($path)) return false;

    $json = file_get_contents($path);
    $data = json_decode($json, true) ?: [];

    if (!isset($data[$token])) return false;
    $record = $data[$token];

    if ($record['expires'] < time()) {
        unset($data[$token]);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        return false;
    }

    unset($data[$token]);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    return $record;
}

/**
 * Catat log aktivitas download
 */
function logDownload($message)
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);

    $logPath = $logDir . '/security.log';
    $time = date('Y-m-d H:i:s');
    $line = "[$time] [DOWNLOAD] $message\n";
    file_put_contents($logPath, $line, FILE_APPEND);
}

// ======================================================================
// === Tambahan admin & enkripsi ZIP untuk produk jualan ===
// ======================================================================

function products_json_path()
{
    return __DIR__ . '/../storage/products/products.json';
}

function load_products()
{
    $path = products_json_path();
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function save_products($products)
{
    $path = products_json_path();
    file_put_contents($path, json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Buat ZIP terenkripsi AES-256 untuk produk jualan
 */
function create_product_encrypted($sourceFile, $productTitle, $productId)
{
    $sourcePath = __DIR__ . '/../storage/products/' . $sourceFile;
    if (!file_exists($sourcePath)) {
        throw new Exception("❌ File source tidak ditemukan: $sourcePath");
    }

    $outDir = __DIR__ . '/../storage/encrypted/';
    if (!is_dir($outDir)) mkdir($outDir, 0755, true);

    $key = strtoupper(bin2hex(random_bytes(6))) . '-' . substr(md5($productId . microtime()), 0, 6);

    $outName = pathinfo($sourceFile, PATHINFO_FILENAME) . '_enc_' . time() . '.zip';
    $outPath = $outDir . $outName;

    $zip = new ZipArchive();
    if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("❌ Gagal membuat ZIP terenkripsi: $outPath");
    }

    $zip->addFile($sourcePath, basename($sourcePath));
    $zip->setEncryptionName(basename($sourcePath), ZipArchive::EM_AES_256, $key);
    $zip->close();

    return [
        'path' => $outPath,
        'name' => $outName,
        'key'  => $key
    ];
}

