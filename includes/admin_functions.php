<?php
// includes/admin_functions.php
require_once __DIR__ . '/encrypt_zip.php'; // gunakan util create_encrypted_zip()
require_once __DIR__ . '/functions.php';

function create_product_encrypted($sourceFile, $productTitle, $productId)
{
    $storageSrc = __DIR__ . '/../storage/products/files' . $sourceFile;
    if (!file_exists($storageSrc)) {
        throw new Exception("Source file tidak ditemukan: $sourceFile");
    }

    // generate product-level key (unik)
    $key = strtoupper(bin2hex(random_bytes(6))) . '-' . substr(md5($productId . time()), 0, 6);

    // output filename
    $outDir = __DIR__ . '/../storage/encrypted/';
    if (!is_dir($outDir)) mkdir($outDir, 0755, true);

    $outName = pathinfo($sourceFile, PATHINFO_FILENAME) . "_prodenc_" . time() . ".zip";
    $outPath = $outDir . $outName;

    // create zip + encrypt using ZipArchive AES-256 (we'll add file inside and set encryption)
    $zip = new ZipArchive();
    if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Gagal membuat file ZIP terenkripsi.");
    }

    // tambahkan whole original zip sebagai file di dalam zip terenkripsi
    // sehingga user akan mengekstrak 1 file .zip => lalu di dalamnya ada file original.zip terenkripsi
    // atau bisa langsung mengekstrak isi. Di sini kita tambahkan file aslinya langsung.
    $zip->addFile($storageSrc, basename($storageSrc));
    $zip->setEncryptionName(basename($storageSrc), ZipArchive::EM_AES_256, $key);

    $zip->close();

    return [
        'path' => $outPath,
        'name' => $outName,
        'key'  => $key
    ];
}
