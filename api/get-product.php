<?php
/**
 * API: get-products.php
 * ----------------------
 * Mengambil daftar produk Bot dari storage/products/
 * Format output: JSON
 */

header('Content-Type: application/json');
$path = __DIR__ . '/../storage/products/';

if (!is_dir($path)) {
    echo json_encode(["status" => "error", "message" => "Products folder not found"]);
    exit;
}

$products = [];
foreach (glob($path . "*.zip") as $file) {
    $info = pathinfo($file);
    $name = ucfirst(str_replace("_", " ", $info['filename']));
    $size = round(filesize($file) / 1024 / 1024, 2) . " MB";
    $products[] = [
        "name" => $name,
        "file" => basename($file),
        "size" => $size,
        "url" => "/download.php?product=" . urlencode($info['filename'])
    ];
}

echo json_encode([
    "status" => "success",
    "count" => count($products),
    "data" => $products
], JSON_PRETTY_PRINT);
?>
