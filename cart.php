<?php
require_once 'auth.php';
requireLogin();

$db = db();
$c = require '/etc/shop/db-config.php';
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $stmt = $db->prepare(
                'SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?'
            );
            $stmt->bind_param('ii', $userId, $productId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                $newQty = (int)$existing['quantity'] + 1;
                $stmt = $db->prepare(
                    'UPDATE cart_items SET quantity=? WHERE id=? AND user_id=?'
                );
                $stmt->bind_param('iii', $newQty, $existing['id'], $userId);
                $stmt->execute();
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)'
                );
                $stmt->bind_param('ii', $userId, $productId);
                $stmt->execute();
            }
        }
    }

    if ($action === 'remove') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        $stmt = $db->prepare(
            'DELETE FROM cart_items WHERE id=? AND user_id=?'
        );
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();
    }

    if ($action === 'update') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $stmt = $db->prepare(
            'UPDATE cart_items SET quantity=? WHERE id=? AND user_id=?'
        );
        $stmt->bind_param('iii', $qty, $itemId, $userId);
        $stmt->execute();
    }

    header('Location: cart.php');
    exit;
}

$stmt = $db->prepare('
    SELECT ci.id, ci.quantity,
           p.id AS product_id, p.name, p.price, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result();

$rows = [];
$total = 0;

while ($row = $items->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng | MAIORA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 35px;
            align-items: start;
        }

        .cart-list {
            background: var(--white);
            border: 1px solid var(--light);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 105px minmax(0, 1fr) 110px;
            gap: 20px;
            align-items: center;
            padding: 22px;
            border-bottom: 1px solid var(--light);
        }

        .cart-item:last-child {
            border-bottom: 0;
        }

        .cart-item img {
            width: 105px;
            aspect-ratio: 3 / 4;
            object-fit: cover;
            background: var(--light);
        }

        .cart-item h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 23px;
            font-weight: 500;
            margin: 0 0 5px;
        }

        .cart-price {
            color: var(--brown);
            font-size: 13px;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }

        .cart-quantity input {
            width: 65px;
            padding: 8px;
            border: 1px solid var(--beige);
            background: var(--cream);
            color: var(--dark);
        }

        .cart-summary {
            padding: 25px;
            background: var(--white);
            border: 1px solid var(--light);
        }

        .cart-summary h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 29px;
            font-weight: 500;
            margin: 0 0 20px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid var(--light);
        }

        .summary-total {
            font-size: 17px;
            font-weight: 500;
            margin: 18px 0;
        }

        .cart-summary .btn-primary {
            width: 100%;
            text-align: center;
        }

        .cart-action {
            border: 0;
            background: none;
            color: var(--brown);
            cursor: pointer;
            font-size: 11px;
            text-decoration: underline;
            padding: 5px 0;
        }

        .cart-update {
            border: 1px solid var(--beige);
            padding: 8px 12px;
            background: transparent;
            color: var(--dark);
            cursor: pointer;
            font-size: 11px;
        }

        .cart-update:hover {
            background: var(--dark);
            color: white;
        }

        @media(max-width: 750px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 80px minmax(0, 1fr);
                gap: 14px;
                padding: 15px;
            }

            .cart-item img {
                width: 80px;
            }

            .cart-item-total {
                grid-column: 2;
            }
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
            <span class="header-user">
                Xin chào, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
            </span>
            <a href="logout.php">Đăng xuất</a>
        </nav>
    </div>
</header>

<main>
    <section class="page-title">
        <div class="eyebrow">YOUR SELECTION</div>
        <h1>Giỏ hàng</h1>
        <p>Những lựa chọn dành riêng cho phong cách của bạn.</p>
    </section>

    <section class="section">
        <?php if (empty($rows)): ?>
            <div class="empty-message">
                <h2>Giỏ hàng của bạn đang trống</h2>
                <p>Hãy khám phá bộ sưu tập MAIORA.</p>
                <a href="products.php" class="btn-primary">TIẾP TỤC MUA SẮM</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-list">
                    <?php foreach ($rows as $r): ?>
                        <article class="cart-item">
                            <img
                                src="<?= htmlspecialchars(($c['img_base'] ?? '') . $r['image']) ?>"
                                alt="<?= htmlspecialchars($r['name']) ?>"
                            >

                            <div>
                                <h3><?= htmlspecialchars($r['name']) ?></h3>
                                <div class="cart-price">
                                    <?= number_format($r['price'], 0, ',', '.') ?>đ
                                </div>

                                <form method="post" class="cart-quantity">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="item_id" value="<?= (int)$r['id'] ?>">
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="<?= (int)$r['quantity'] ?>"
                                        min="1"
                                        aria-label="Số lượng"
                                    >
                                    <button class="cart-update" type="submit">
                                        CẬP NHẬT
                                    </button>
                                </form>

                                <form method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="item_id" value="<?= (int)$r['id'] ?>">
                                    <button class="cart-action" type="submit">
                                        Xóa sản phẩm
                                    </button>
                                </form>
                            </div>

                            <div class="cart-item-total">
                                <strong>
                                    <?= number_format($r['subtotal'], 0, ',', '.') ?>đ
                                </strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <aside class="cart-summary">
                    <h2>Tóm tắt đơn hàng</h2>

                    <div class="summary-line">
                        <span>Tạm tính</span>
                        <span><?= number_format($total, 0, ',', '.') ?>đ</span>
                    </div>

                    <div class="summary-line">
                        <span>Phí vận chuyển</span>
                        <span>Được xác nhận khi đặt hàng</span>
                    </div>

                    <div class="summary-line summary-total">
                        <span>Tổng cộng</span>
                        <span><?= number_format($total, 0, ',', '.') ?>đ</span>
                    </div>

                    <a href="checkout.php" class="btn-primary">
                        TIẾN HÀNH THANH TOÁN
                    </a>

                    <p style="font-size:11px;color:var(--brown);text-align:center;margin-top:18px">
                        Cảm ơn bạn đã lựa chọn MAIORA.
                    </p>
                </aside>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-brand">MAIORA</div>
    <p>ELEVATE YOUR EVERYDAY STYLE</p>
    <p>© <?= date('Y') ?> MAIORA. ALL RIGHTS RESERVED.</p>
</footer>

</body>
</html>
