<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';

$products = get_products();

// jika datang lewat POST dari index (klik Buy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = $_POST['product_id'];
    $product = null;
    foreach ($products as $p) if ($p['id'] === $pid) {
        $product = $p;
        break;
    }
    if (!$product) {
        echo "<p>Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }
    // Simulasi halaman checkout
?>
    <h2>Checkout — <?= htmlspecialchars($product['title']) ?></h2>
    <div class="checkout-box">
        <p>Harga: <strong><?= rupiah($product['price']) ?></strong></p>
        <p>Pembayaran simulasi. Klik <strong>Pay</strong> untuk menyelesaikan.</p>
        <form method="post" action="checkout.php">
            <input type="hidden" name="confirm_product" value="<?= htmlspecialchars($product['id']) ?>">
            <button class="btn primary" type="submit">Pay (Simulate)</button>
        </form>
    </div>
<?php
    require_once 'includes/footer.php';
    exit;
}

// jika setelah "bayar" (POST confirm_product), buat token dan redirect ke download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_product'])) {
    $pid = $_POST['confirm_product'];
    $product = null;
    foreach ($products as $p) if ($p['id'] === $pid) {
        $product = $p;
        break;
    }
    if (!$product) {
        echo "<p>Produk tidak ditemukan.</p>";
        require_once 'includes/footer.php';
        exit;
    }

    // di sini logic payment gateway nyata akan memvalidasi webhook/IPN.
    // karena ini simulasi: anggap pembayaran berhasil -> buat token
    $token = create_download_token($product['file'], 3600); // valid 1 jam
    // redirect ke halaman download
    header("Location: download.php?token=$token");
    exit;
}

// default - tampil link ke toko
header("Location: index.php");
exit;
