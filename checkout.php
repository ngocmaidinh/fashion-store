<?php
require 'auth.php';
requireLogin();
$db = db();
$c = require '/etc/shop/db-config.php';
$userId = $_SESSION['user_id'];
$success = false;
$orderId = null;

// Lấy giỏ hàng hiện tại
$stmt = $db->prepare('
  SELECT ci.id as cart_id, ci.quantity, p.id as product_id, p.name, p.price, p.image
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

// Xử lý xác nhận thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($rows)) {
  $db->begin_transaction();
  try {
    $stmt = $db->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, "paid")');
    $stmt->bind_param('ii', $userId, $total);
    $stmt->execute();
    $orderId = $db->insert_id;

    $stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($rows as $r) {
      $stmt->bind_param('iiii', $orderId, $r['product_id'], $r['quantity'], $r['price']);
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
<head><meta charset="UTF-8"><title>Thanh toán</title>
<style>
 body{font-family:Arial;margin:30px;max-width:700px}
 table{border-collapse:collapse;width:100%}
 td,th{border:1px solid #ddd;padding:8px;text-align:left}
 .total{font-weight:bold;font-size:1.2em;margin-top:15px}
 .success{background:#e6ffed;border:1px solid #4caf50;padding:15px;border-radius:4px}
 nav{margin-bottom:20px}
 nav a{margin-right:15px}
</style></head>
<body>
<nav>
  <a href="products.php">Sản phẩm</a>
  <a href="cart.php">Giỏ hàng</a>
  <a href="orders.php">Đơn hàng</a>
  Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?> |
  <a href="logout.php">Đăng xuất</a>
</nav>

<?php if ($success): ?>
  <div class="success">
    <h2>Đặt hàng thành công!</h2>
    <p>Mã đơn hàng: #<?= $orderId ?></p>
    <p>Tổng tiền: <?= number_format($total, 0, ',', '.') ?>đ</p>
  </div>
  <p><a href="products.php">Tiếp tục mua sắm</a> | <a href="orders.php">Xem đơn hàng của tôi</a></p>

<?php elseif (empty($rows)): ?>
  <p>Giỏ hàng trống. <a href="products.php">Quay lại mua sắm</a></p>

<?php else: ?>
  <h1>Xác nhận đơn hàng</h1>
  <table>
  <tr><th>Sản phẩm</th><th>Số lượng</th><th>Thành tiền</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= $r['quantity'] ?></td>
    <td><?= number_format($r['subtotal'], 0, ',', '.') ?>đ</td>
  </tr>
  <?php endforeach; ?>
  </table>
  <p class="total">Tổng cộng: <?= number_format($total, 0, ',', '.') ?>đ</p>
  <form method="post">
    <button type="submit" style="padding:10px 20px">Xác nhận thanh toán</button>
  </form>
<?php endif; ?>
</body></html>
