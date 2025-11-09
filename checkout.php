<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Ambil data produk
$products = get_products();

/**
 * Tahap 1: User klik "Buy" dari index.php
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = $_POST['product_id'];
    $product = null;

    // Cari produk berdasarkan ID
    foreach ($products as $p) {
        if ($p['id'] === $pid) {
            $product = $p;
            break;
        }
    }

    // Jika tidak ketemu
    if (!$product) {
        echo "<p>❌ Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }

    // Tampilkan halaman checkout
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
 * Tahap 2: Setelah user klik tombol "Pay"
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_product'])) {
    $pid = $_POST['confirm_product'];
    $product = null;

    // Cari produk berdasarkan ID
    foreach ($products as $p) {
        if ($p['id'] === $pid) {
            $product = $p;
            break;
        }
    }

    // Jika tidak ketemu
    if (!$product) {
        echo "<p>❌ Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }

    // Simulasi email user (karena belum ada sistem login)
    $userEmail = "guest@example.com";

    // Buat token download valid 1 jam
    $token = create_download_token(
        $product['file'],     // file ZIP produk
        $product['title'],    // nama produk
        $userEmail,           // email user
        3600                  // durasi token (detik)
    );

    // Redirect ke halaman download
    header("Location: download.php?token=$token");
    exit;
}

/**
 * Default: jika tidak lewat POST, kembali ke index
 */
header("Location: index.php");
exit;
