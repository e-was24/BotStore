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

$products = get_products();
?>
<h1>Marketplace Bot — BotStore</h1>
<p class="lead">Pilih script bot WA / Telegram, lakukan pembayaran, lalu unduh file ZIP.</p>

<div class="products-grid">
    <?php foreach ($products as $p): ?>
        <div class="card">
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p class="price"><?= rupiah($p['price']) ?></p>
            <p><?= htmlspecialchars($p['desc']) ?></p>
            <form method="post" action="checkout.php">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button class="btn" type="submit">Buy</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>