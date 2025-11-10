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
function rupiah(float|int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Buat token single-use untuk download file produk
 */
function create_download_token(string $productFile, string $productName, string $userEmail, int $ttl = 3600): string
{
    $token = bin2hex(random_bytes(16));
    $storageDir = __DIR__ . '/../storage';
    $path = $storageDir . '/tokens.json';

    if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

    $data = [];
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true) ?? [];
        if (json_last_error() !== JSON_ERROR_NONE) $data = [];
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
function validate_and_consume_token(string $token): false|array
{
    $path = __DIR__ . '/../storage/tokens.json';
    if (!file_exists($path)) return false;

    $json = file_get_contents($path);
    $data = json_decode($json, true) ?? [];
    if (json_last_error() !== JSON_ERROR_NONE) return false;

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
function logDownload(string $message): void
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

/**
 * Path ke file products.json
 */
function products_json_path(): string
{
    return __DIR__ . '/../storage/products/products.json';
}

/**
 * Load semua produk dari JSON
 */
function load_products(): array
{
    $path = products_json_path();
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/**
 * Simpan semua produk ke JSON
 */
function save_products(array $products): void
{
    $path = products_json_path();
    file_put_contents($path, json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Buat ZIP terenkripsi AES-256 untuk produk tertentu
 * - source: file ZIP asli (disimpan di storage/products/)
 * - hasil: disimpan di storage/encrypted/
 * - return: path + key + nama file output
 */
function create_product_encrypted(string $sourceFile, string $productTitle, string $productId): array
{
    $sourcePath = __DIR__ . '/../storage/products/' . $sourceFile;
    if (!file_exists($sourcePath)) {
        throw new Exception("❌ File source tidak ditemukan: $sourcePath");
    }

    // Buat folder tujuan
    $outDir = __DIR__ . '/../storage/encrypted/';
    if (!is_dir($outDir)) mkdir($outDir, 0755, true);

    // Generate key unik
    $key = strtoupper(bin2hex(random_bytes(6))) . '-' . substr(md5($productId . microtime()), 0, 6);

    // Output filename
    $outName = pathinfo($sourceFile, PATHINFO_FILENAME) . '_enc_' . time() . '.zip';
    $outPath = $outDir . $outName;

    // Enkripsi ZIP
    $zip = new ZipArchive();
    if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("❌ Gagal membuat ZIP terenkripsi: $outPath");
    }

    // Tambahkan file asli ke dalam ZIP terenkripsi
    $zip->addFile($sourcePath, basename($sourcePath));
    $zip->setEncryptionName(basename($sourcePath), ZipArchive::EM_AES_256, $key);
    $zip->close();

    return [
        'path' => $outPath,
        'name' => $outName,
        'key'  => $key
    ];
}
