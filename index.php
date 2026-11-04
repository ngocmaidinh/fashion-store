<?php
require_once 'auth.php';

$c = require '/etc/shop/db-config.php';
$db = db();

$rs = $db->query(
    'SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 4'
);

if (!$rs) {
    http_response_code(500);
    exit('Không thể tải danh sách sản phẩm.');
}

$cartCount = isset($_SESSION['cart']) && is_array($_SESSION['cart'])
    ? array_sum($_SESSION['cart'])
    : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maiora | Elevate Your Everyday Style</title>
    <meta name="description" content="Maiora - Khám phá phong cách thời trang thanh lịch và hiện đại.">
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
            <a href="cart.php">Giỏ hàng (<?= (int)$cartCount ?>)</a>

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
    <section class="hero">
        <div class="hero-image">
            <div class="hero-content">
                <div class="eyebrow">MAIORA — NEW COLLECTION</div>
                <h1>Elegance in<br>Every Detail</h1>
                <p>
                    Vẻ đẹp đến từ những điều giản đơn.
                    Khám phá phong cách riêng của bạn cùng Maiora.
                </p>
                <a href="products.php" class="btn-primary">
                    Khám phá ngay
                </a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div class="eyebrow">SELECTED FOR YOU</div>
            <h2>New Collection</h2>
            <p>Những lựa chọn dành cho phong cách mỗi ngày.</p>
        </div>

        <div class="product-grid">
            <?php while ($p = $rs->fetch_assoc()): ?>
                <article class="product-card">
                    <a href="products.php" class="product-image-wrap">
                        <img
                            src="<?= htmlspecialchars(($c['img_base'] ?? '') . $p['image']) ?>"
                            alt="<?= htmlspecialchars($p['name']) ?>"
                            loading="lazy"
                        >
                    </a>

                    <div class="product-info">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <div class="product-price">
                            <?= number_format($p['price'], 0, ',', '.') ?>đ
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="collection-banner">
        <div class="eyebrow">THE MAIORA EXPERIENCE</div>
        <h2>Find Your Own Elegance</h2>
        <p>Phong cách không cần cầu kỳ, chỉ cần là chính bạn.</p>
        <a href="products.php" class="btn-primary">Xem tất cả sản phẩm</a>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-brand">MAIORA</div>
    <p>ELEVATE YOUR EVERYDAY STYLE</p>
    <p>© <?= date('Y') ?> MAIORA. ALL RIGHTS RESERVED.</p>
</footer>

</body>
</html>
