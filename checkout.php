<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'includes/encrypt_zip.php'; // 🔒 tambahan

// Ambil data produk
$products = get_products();

/**
 * Tahap 1: User klik "Buy"
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = $_POST['product_id'];
    $product = null;

    foreach ($products as $p) {
        if ($p['id'] === $pid) {
            $product = $p;
            break;
        }
    }

    if (!$product) {
        echo "<p>❌ Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }
    ?>
    <h2>Checkout — <?= htmlspecialchars($product['title']) ?></h2>
    <div class="checkout-box">
        <p>Harga: <strong><?= rupiah($product['price']) ?></strong></p>
        <p>Pembayaran simulasi. Klik <strong>Pay</strong> untuk menyelesaikan transaksi.</p>
        <form method="post" action="checkout.php">
            <input type="hidden" name="confirm_product" value="<?= htmlspecialchars($product['id']) ?>">
            <button class="btn primary" type="submit">Pay (Simulate)</button>
        </form>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

/**
 * Tahap 2: Setelah klik "Pay"
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_product'])) {
    $pid = $_POST['confirm_product'];
    $product = null;

    foreach ($products as $p) {
        if ($p['id'] === $pid) {
            $product = $p;
            break;
        }
    }

    if (!$product) {
        echo "<p>❌ Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }

    // Simulasi email pembeli
    $userEmail = "guest@example.com";

    // 🔒 Enkripsi otomatis file produk
    try {
        $enc = create_encrypted_zip($product['file'], $userEmail, $product['title']);
    } catch (Exception $e) {
        echo "<p>⚠️ Gagal enkripsi file: " . htmlspecialchars($e->getMessage()) . "</p>";
        require_once 'includes/footer.php';
        exit;
    }

    // Simpan token download (tetap aman)
    $token = create_download_token(
        basename($enc['path']),
        $product['title'],
        $userEmail,
        3600
    );

    echo "<h2>✅ Pembayaran Berhasil!</h2>";
    echo "<p>File ZIP terenkripsi kamu sudah siap.</p>";
    echo "<p><b>Kunci Enkripsi:</b> <code>{$enc['key']}</code></p>";
    echo "<p><a class='btn' href='download.php?token={$token}'>Download Sekarang</a></p>";

    require_once 'includes/footer.php';
    exit;
}

// Default redirect
header("Location: index.php");
exit;
