<?php
// admin/process_upload.php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/header.php';

session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<p>Access denied. Please login as admin.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$action = $_POST['action'] ?? null;

if ($action === 'upload' && isset($_FILES['zipfile'])) {
    // basic validation
    $file = $_FILES['zipfile'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error code: " . $file['error']);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'zip') die("File harus .zip");

    // sanitize title/desc
    $title = trim($_POST['title'] ?? 'Untitled');
    $price = (int)($_POST['price'] ?? 0);
    $desc  = trim($_POST['desc'] ?? '');

    // create storage dir
    $destDir = __DIR__ . '/../storage/products/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    // unique filename to avoid collisions
    $filename = time() . '_' . preg_replace('/[^a-z0-9_\-\.]/i', '_', basename($file['name']));
    $destPath = $destDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        die("Gagal pindah file.");
    }

    // add product to JSON
    $products = load_products();
    $newId = 'prod-' . bin2hex(random_bytes(3));
    $product = [
        'id' => $newId,
        'title' => $title,
        'price' => $price,
        'desc' => $desc,
        'file' => $filename,      // original zip filename
        'status' => 'uploaded',   // uploaded -> admin harus 'encrypt' untuk publish
        'created_at' => date('c')
    ];
    $products[] = $product;
    save_products($products);

    header("Location: index.php");
    exit;
}

if ($action === 'encrypt' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $products = load_products();

    // find product
    $found = null;
    foreach ($products as $k => $p) {
        if ($p['id'] === $productId) {
            $found = [$k, $p];
            break;
        }
    }
    if (!$found) {
        die("Produk tidak ditemukan.");
    }

    list($index, $prod) = $found;

    try {
        // create encrypted zip for the product (product-level key)
        $encInfo = create_product_encrypted($prod['file'], $prod['title'], $prod['id']);
    } catch (Exception $e) {
        die("Gagal enkripsi: " . $e->getMessage());
    }

    // update product entry
    $products[$index]['enc_file'] = $encInfo['name'];
    $products[$index]['enc_key']  = $encInfo['key']; // simpan kunci produk (jaga baik-baik)
    $products[$index]['status']   = 'ready_to_sell';
    $products[$index]['encrypted_at'] = date('c');
    save_products($products);

    header("Location: index.php");
    exit;
}

// fallback
header("Location: index.php");
exit;
