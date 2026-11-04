<?php
require_once 'auth.php';

$c = require '/etc/shop/db-config.php';
$db = db();

$rs = $db->query(
    'SELECT id, name, price, image FROM products ORDER BY id DESC'
);

if (!$rs) {
    http_response_code(500);
    exit('Không thể tải danh sách sản phẩm.');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm | MAIORA</title>
    <meta name="description" content="Khám phá bộ sưu tập thời trang Maiora.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand">MAIORA</a>

        <nav class="main-nav">
            <a href="index.php">Trang chủ</a>
            <a href="products.php">Sản phẩm</a>
            <a href="orders.php">Đơn hàng</a>
            <a href="cart.php">Giỏ hàng</a>

            <?php if (isLoggedIn()): ?>
                <span class="header-user">
                    Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
                <a href="logout.php">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <section class="page-title">
        <div class="eyebrow">THE MAIORA COLLECTION</div>
        <h1>Our Products</h1>
        <p>Khám phá những thiết kế dành riêng cho phong cách của bạn.</p>
    </section>

    <section class="section">
        <?php if ($rs->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($p = $rs->fetch_assoc()): ?>
                    <article class="product-card">
                        <div class="product-image-wrap">
                            <img
                                src="<?= htmlspecialchars(($c['img_base'] ?? '') . $p['image']) ?>"
                                alt="<?= htmlspecialchars($p['name']) ?>"
                                loading="lazy"
                            >
                        </div>

                        <div class="product-info">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>

                            <div class="product-price">
                                <?= number_format($p['price'], 0, ',', '.') ?>đ
                            </div>

                            <form method="post" action="cart.php">
                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$p['id'] ?>"
                                >
                                <input
                                    type="hidden"
                                    name="action"
                                    value="add"
                                >
                                <button type="submit" class="btn-add">
                                    THÊM VÀO GIỎ
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-message">
                Hiện chưa có sản phẩm nào.
            </div>
        <?php endif; ?>
    </section>

    <section class="collection-banner">
        <div class="eyebrow">MADE FOR YOUR EVERYDAY</div>
        <h2>Simply Beautiful</h2>
        <p>Thanh lịch trong từng lựa chọn.</p>
        <a href="index.php" class="btn-primary">Về trang chủ</a>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-brand">MAIORA</div>
    <p>ELEVATE YOUR EVERYDAY STYLE</p>
    <p>© <?= date('Y') ?> MAIORA. ALL RIGHTS RESERVED.</p>
</footer>

</body>
</html>
