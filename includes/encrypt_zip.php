<?php
/**
 * Buat file ZIP terenkripsi AES-256 dengan password unik
 * Return: path hasil ZIP terenkripsi
 */

function create_encrypted_zip($sourceFile, $buyerEmail, $productTitle)
{
    $sourcePath = __DIR__ . '/../storage/products/' . $sourceFile;
    $outputDir  = __DIR__ . '/../storage/encrypted/';
    if (!file_exists($outputDir)) mkdir($outputDir, 0775, true);

    // buat password unik untuk pembeli
    $key = strtoupper(bin2hex(random_bytes(4))) . '-' . substr(md5($buyerEmail), 0, 6);
    $zipName = pathinfo($sourceFile, PATHINFO_FILENAME) . "_enc_" . time() . ".zip";
    $outputPath = $outputDir . $zipName;

    // Buat ZIP baru terenkripsi
    $zip = new ZipArchive();
    if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Gagal membuat ZIP terenkripsi");
    }

    // tambahkan file asli ke dalam ZIP baru
    $zip->addFile($sourcePath, basename($sourcePath));
    $zip->setEncryptionName(basename($sourcePath), ZipArchive::EM_AES_256, $key);
    $zip->close();

    return [
        'path' => $outputPath,
        'key'  => $key,
        'name' => $zipName
    ];
}
