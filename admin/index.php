<?php
// admin/index.php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/header.php';

// Simple auth check (replace dengan sistem auth-mu)
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<p>Access denied. Please login as admin.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$products = load_products(); // from includes/functions.php
?>

<h1>Admin — Upload Product ZIP</h1>

<section class="box">
    <h2>Upload ZIP baru</h2>
    <form method="post" action="process_upload.php" enctype="multipart/form-data">
        <label>
            Product Title:<br>
            <input type="text" name="title" required>
        </label><br><br>
        <label>
            Price (angka):<br>
            <input type="number" name="price" required>
        </label><br><br>
        <label>
            Description:<br>
            <textarea name="desc" rows="3"></textarea>
        </label><br><br>
        <label>
            Select ZIP file:<br>
            <input type="file" name="zipfile" accept=".zip" required>
        </label><br><br>
        <button type="submit" name="action" value="upload">Upload & Save</button>
    </form>
</section>

<section class="box">
    <h2>Daftar Produk</h2>
    <table border="1" cellpadding="8">
        <thead><tr><th>ID</th><th>Title</th><th>Price</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($products as $prod): ?>
            <tr>
                <td><?= htmlspecialchars($prod['id']) ?></td>
                <td><?= htmlspecialchars($prod['title']) ?></td>
                <td><?= rupiah($prod['price']) ?></td>
                <td><?= htmlspecialchars($prod['status'] ?? 'draft') ?></td>
                <td>
                    <?php if (isset($prod['enc_file'])): ?>
                        <small>Encrypted: <?= htmlspecialchars($prod['enc_file']) ?></small><br>
                        <button disabled>Ready</button>
                    <?php else: ?>
                        <form method="post" action="process_upload.php" style="display:inline">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($prod['id']) ?>">
                            <button type="submit" name="action" value="encrypt">Encrypt / Prepare</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
