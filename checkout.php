<?php
require_once 'auth.php';
requireLogin();

$db = db();
$c = require '/etc/shop/db-config.php';
$userId = (int)$_SESSION['user_id'];
$success = false;
$orderId = null;
$error = null;

// Load the current user's cart.
$stmt = $db->prepare('
    SELECT ci.id AS cart_id, ci.quantity,
           p.id AS product_id, p.name, p.price, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $row['quantity'] = (int)$row['quantity'];
    $row['subtotal'] = (float)$row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $rows[] = $row;
}

// Confirm order using the existing order flow.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($rows)) {
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, "paid")');
        $stmt->bind_param('id', $userId, $total);
        $stmt->execute();
        $orderId = $db->insert_id;

        $stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        foreach ($rows as $r) {
            $price = (float)$r['price'];
            $stmt->bind_param('iiid', $orderId, $r['product_id'], $r['quantity'], $price);
            $stmt->execute();
        }

        $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $db->commit();
        $success = true;
    } catch (Exception $e) {
        $db->rollback();
        $error = 'Có lỗi xảy ra, vui lòng thử lại.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán | MAIORA</title>
    <meta name="description" content="Xác nhận lựa chọn của bạn tại MAIORA.">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-page { max-width: 1100px; margin: 0 auto; padding: 0 0 70px; }
        .checkout-heading { text-align: center; margin: 0 0 35px; }
        .checkout-heading .eyebrow { color: var(--brown); margin-bottom: 9px; }
        .checkout-heading h1 { margin: 0; font-size: clamp(38px, 5vw, 54px); font-weight: 400; }
        .checkout-heading p { color: var(--brown); font-size: 13px; margin: 8px 0 0; }
        .checkout-steps { display: flex; justify-content: center; gap: 16px; align-items: center; margin: 22px 0 0; color: #A69A8D; font-size: 10px; letter-spacing: 1.4px; text-transform: uppercase; }
        .checkout-steps .active { color: var(--dark); }
        .checkout-steps .line { width: 35px; height: 1px; background: var(--beige); }
        .checkout-layout { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr); gap: 28px; align-items: start; }
        .checkout-panel { background: var(--white); border: 1px solid var(--light); }
        .checkout-panel-head { padding: 24px 28px 17px; border-bottom: 1px solid var(--light); }
        .checkout-panel-head .eyebrow { color: var(--brown); font-size: 9px; margin-bottom: 7px; }
        .checkout-panel-head h2 { margin: 0; font-size: 29px; font-weight: 500; }
        .checkout-panel-head p { margin: 5px 0 0; color: #887B70; font-size: 12px; }
        .checkout-items { padding: 0 28px; }
        .checkout-item { display: grid; grid-template-columns: 78px minmax(0,1fr) auto; gap: 17px; align-items: center; padding: 20px 0; border-bottom: 1px solid #F0ECE6; }
        .checkout-item:last-child { border-bottom: 0; }
        .checkout-item img { width: 78px; height: 100px; object-fit: cover; background: var(--light); }
        .checkout-item h3 { font-family: 'Be Vietnam Pro', sans-serif; font-size: 12px; font-weight: 400; margin: 0 0 7px; }
        .checkout-item .item-meta { color: #95877A; font-size: 11px; }
        .checkout-item .item-price { color: var(--brown); font-size: 12px; white-space: nowrap; }
        .checkout-summary { padding: 25px; position: sticky; top: 100px; }
        .checkout-summary h2 { font-size: 30px; font-weight: 500; margin: 0 0 20px; }
        .summary-row { display: flex; justify-content: space-between; gap: 15px; padding: 12px 0; border-bottom: 1px solid var(--light); color: #76685C; font-size: 12px; }
        .summary-row strong { color: var(--dark); font-weight: 400; text-align: right; }
        .summary-total { display: flex; justify-content: space-between; gap: 15px; align-items: baseline; padding: 19px 0 21px; }
        .summary-total span { font-size: 13px; color: var(--dark); }
        .summary-total strong { font-family: 'Cormorant Garamond', serif; font-size: 29px; font-weight: 500; color: var(--dark); }
        .checkout-submit { display: block; width: 100%; min-height: 50px; border: 1px solid var(--dark); background: var(--dark); color: #fff; font-size: 10px; letter-spacing: 1.5px; cursor: pointer; transition: .2s; }
        .checkout-submit:hover { background: var(--brown); border-color: var(--brown); }
        .checkout-back { display: block; text-align: center; margin-top: 16px; color: var(--brown); font-size: 11px; }
        .checkout-note { display: flex; gap: 10px; margin-top: 19px; padding: 13px; background: #F8F6F1; color: #887B70; font-size: 10px; line-height: 1.7; }
        .checkout-note span { color: var(--brown); font-size: 15px; }
        .checkout-alert { margin: 0 0 20px; padding: 14px 18px; border: 1px solid var(--beige); background: #fff; color: var(--brown); font-size: 13px; }
        .checkout-success { max-width: 680px; margin: 0 auto; padding: 48px 35px; text-align: center; background: #fff; border: 1px solid var(--light); }
        .success-mark { width: 54px; height: 54px; display: grid; place-items: center; margin: 0 auto 20px; border: 1px solid var(--beige); border-radius: 50%; color: var(--brown); font-size: 24px; }
        .checkout-success .eyebrow { color: var(--brown); }
        .checkout-success h2 { font-size: 38px; font-weight: 500; margin: 0 0 10px; }
        .checkout-success p { color: #887B70; font-size: 13px; }
        .success-order { margin: 25px 0; padding: 18px; background: var(--cream); color: var(--brown); font-size: 12px; }
        .success-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 25px; }
        .success-actions a { display: inline-flex; align-items: center; justify-content: center; min-height: 45px; padding: 0 22px; font-size: 10px; letter-spacing: 1px; border: 1px solid var(--dark); }
        .success-actions .primary { background: var(--dark); color: #fff; }
        .success-actions .secondary { color: var(--dark); background: transparent; }
        .checkout-empty { text-align: center; padding: 55px 25px; background: #fff; border: 1px solid var(--light); }
        .checkout-empty h2 { font-size: 32px; font-weight: 500; margin: 0 0 10px; }
        .checkout-empty p { color: #887B70; font-size: 13px; margin-bottom: 24px; }
        @media (max-width: 760px) {
            .checkout-page { padding-bottom: 45px; }
            .checkout-layout { grid-template-columns: 1fr; gap: 18px; }
            .checkout-summary { position: static; }
            .checkout-panel-head { padding: 21px 18px 15px; }
            .checkout-items { padding: 0 18px; }
            .checkout-item { grid-template-columns: 65px minmax(0,1fr) auto; gap: 12px; }
            .checkout-item img { width: 65px; height: 84px; }
            .checkout-summary { padding: 22px 18px; }
            .checkout-success { padding: 35px 20px; }
        }
        @media (max-width: 420px) {
            .checkout-item { grid-template-columns: 58px minmax(0,1fr); }
            .checkout-item img { width: 58px; height: 75px; }
            .checkout-item .item-price { grid-column: 2; }
            .checkout-steps { gap: 9px; font-size: 9px; }
            .checkout-steps .line { width: 20px; }
        }
    </style>
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
            <span class="header-user">Xin chào, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
            <a href="logout.php">Đăng xuất</a>
        </nav>
    </div>
</header>

<main class="checkout-page">
    <section class="checkout-heading">
        <div class="eyebrow">YOUR MAIORA MOMENT</div>
        <h1>Thanh toán</h1>
        <p>Hoàn tất lựa chọn tinh tế của bạn.</p>
        <div class="checkout-steps">
            <span>Giỏ hàng</span><span class="line"></span><span class="active">Xác nhận</span><span class="line"></span><span>Hoàn tất</span>
        </div>
    </section>

    <?php if ($error): ?>
        <div class="checkout-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <section class="checkout-success">
            <div class="success-mark">✓</div>
            <div class="eyebrow">ORDER CONFIRMED</div>
            <h2>Cảm ơn bạn!</h2>
            <p>Đơn hàng của bạn đã được ghi nhận. MAIORA rất vui được đồng hành cùng phong cách của bạn.</p>
            <div class="success-order">
                Mã đơn hàng <strong>#<?= (int)$orderId ?></strong><br>
                Tổng đơn hàng <strong><?= number_format($total, 0, ',', '.') ?>đ</strong>
            </div>
            <div class="success-actions">
                <a class="primary" href="orders.php">XEM ĐƠN HÀNG</a>
                <a class="secondary" href="products.php">TIẾP TỤC MUA SẮM</a>
            </div>
        </section>
    <?php elseif (empty($rows)): ?>
        <section class="checkout-empty">
            <div class="eyebrow">YOUR SELECTION</div>
            <h2>Giỏ hàng đang trống</h2>
            <p>Hãy khám phá những thiết kế thanh lịch từ bộ sưu tập MAIORA.</p>
            <a href="products.php" class="btn-primary">KHÁM PHÁ SẢN PHẨ</a>
        </section>
    <?php else: ?>
        <div class="checkout-layout">
            <section class="checkout-panel">
                <div class="checkout-panel-head">
                    <div class="eyebrow">ORDER DETAILS</div>
                    <h2>Lựa chọn của bạn</h2>
                    <p><?= count($rows) ?> sản phẩm trong đơn hàng</p>
                </div>
                <div class="checkout-items">
                    <?php foreach ($rows as $r): ?>
                        <article class="checkout-item">
                            <img src="<?= htmlspecialchars(($c['img_base'] ?? '') . $r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
                            <div>
                                <h3><?= htmlspecialchars($r['name']) ?></h3>
                                <div class="item-meta">Số lượng: <?= (int)$r['quantity'] ?></div>
                                <div class="item-meta"><?= number_format($r['price'], 0, ',', '.') ?>đ / sản phẩm</div>
                            </div>
                            <div class="item-price"><?= number_format($r['subtotal'], 0, ',', '.') ?>đ</div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="checkout-panel checkout-summary">
                <div class="eyebrow">A LITTLE SUMMARY</div>
                <h2>Đơn hàng</h2>
                <div class="summary-row"><span>Tạm tính</span><strong><?= number_format($total, 0, ',', '.') ?>đ</strong></div>
                <div class="summary-row"><span>Phí vận chuyển</span><strong>Miễn phí</strong></div>
                <div class="summary-total"><span>Tổng cộng</span><strong><?= number_format($total, 0, ',', '.') ?>đ</strong></div>
                <form method="post">
                    <button type="submit" class="checkout-submit">XÁC NHẬN ĐẶT HÀNG</button>
                </form>
                <a href="cart.php" class="checkout-back">← Quay lại giỏ hàng</a>
                <div class="checkout-note"><span>✧</span><div>Thank you for choosing MAIORA.<br>Đơn hàng được xác nhận sau khi bạn nhấn nút đặt hàng.</div></div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="footer-brand">MAIORA</div>
    <p>ELEVATE YOUR EVERYDAY STYLE</p>
    <p>© <?= date('Y') ?> MAIORA. ALL RIGHTS RESERVED.</p>
</footer>
</body>
</html>
