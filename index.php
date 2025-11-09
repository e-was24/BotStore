<?php
require_once __DIR__.'/security/sanitizer.php';
require_once __DIR__.'/security/firewall.php';
require_once __DIR__.'/security/rate_limiter.php';
require_once __DIR__.'/security/session_guard.php';
require_once __DIR__.'/config/app_config.php';

// Rate limit per halaman
rate_limit('index', 30, 60);

require_once 'includes/functions.php';
require_once 'includes/header.php';

// Ambil daftar produk dari JSON
$products = get_products();

?>

<script>
    console.log("Path kerja PHP:", <?= json_encode(__DIR__) ?>);
    console.log("Data produk dari PHP:", <?= json_encode($products, JSON_PRETTY_PRINT) ?>);
</script>

<h1>Marketplace Bot — BotStore</h1>
<p class="lead">Pilih script bot WA / Telegram, lakukan pembayaran, lalu unduh file ZIP.</p>



<div class="products-grid">
    <?php foreach ($products as $p): ?>
        <div class="card">
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p class="price"><?= rupiah($p['price']) ?></p>
            <p><?= htmlspecialchars($p['desc']) ?></p>
            <form method="post" action="checkout.php">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                <input type="hidden" name="product_file" value="<?= htmlspecialchars($p['file']) ?>">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($p['title']) ?>">
                <input type="hidden" name="product_price" value="<?= htmlspecialchars($p['price']) ?>">
                <button class="btn" type="submit">Buy</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
