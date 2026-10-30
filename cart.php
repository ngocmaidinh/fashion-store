<?php
require 'auth.php';
requireLogin();
$db = db();
$c = require '/etc/shop/db-config.php';
$userId = $_SESSION['user_id'];

// Xử lý thêm / xoá / cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'add') {
    $productId = (int)$_POST['product_id'];
    $stmt = $db->prepare('SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?');
    $stmt->bind_param('ii', $userId, $productId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
      $newQty = $existing['quantity'] + 1;
      $stmt = $db->prepare('UPDATE cart_items SET quantity=? WHERE id=?');
      $stmt->bind_param('ii', $newQty, $existing['id']);
      $stmt->execute();
    } else {
      $stmt = $db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)');
      $stmt->bind_param('ii', $userId, $productId);
      $stmt->execute();
    }
  }

  if ($action === 'remove') {
    $itemId = (int)$_POST['item_id'];
    $stmt = $db->prepare('DELETE FROM cart_items WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $itemId, $userId);
    $stmt->execute();
  }

  if ($action === 'update') {
    $itemId = (int)$_POST['item_id'];
    $qty = max(1, (int)$_POST['quantity']);
    $stmt = $db->prepare('UPDATE cart_items SET quantity=? WHERE id=? AND user_id=?');
    $stmt->bind_param('iii', $qty, $itemId, $userId);
    $stmt->execute();
  }

  header('Location: cart.php');
  exit;
}

// Lấy danh sách giỏ hàng kèm thông tin sản phẩm
$stmt = $db->prepare('
  SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.price, p.image
  FROM cart_items ci
  JOIN products p ON ci.product_id = p.id
  WHERE ci.user_id = ?
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result();

$total = 0;
$rows = [];
while ($row = $items->fetch_assoc()) {
  $row['subtotal'] = $row['price'] * $row['quantity'];
  $total += $row['subtotal'];
  $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Giỏ hàng</title>
<style>
 body{font-family:Arial;margin:30px}
 table{border-collapse:collapse;width:100%;max-width:700px}
 td,th{border:1px solid #ddd;padding:8px;text-align:left}
 img{width:60px;height:60px;object-fit:cover}
 nav{margin-bottom:20px}
 nav a{margin-right:15px}
 .total{font-weight:bold;font-size:1.2em;margin-top:15px}
</style></head>
<body>
<nav>
  <a href="products.php">Sản phẩm</a>
  <a href="cart.php">Giỏ hàng</a>
  <a href="orders.php">Đơn hàng</a>
  Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?> |
  <a href="logout.php">Đăng xuất</a>
</nav>
<h1>Giỏ hàng</h1>

<?php if (empty($rows)): ?>
  <p>Giỏ hàng trống. <a href="products.php">Tiếp tục mua sắm</a></p>
<?php else: ?>
<table>
<tr><th>Ảnh</th><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><img src="<?= htmlspecialchars($c['img_base'].$r['image']) ?>" alt=""></td>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td><?= number_format($r['price'], 0, ',', '.') ?>đ</td>
  <td>
    <form method="post" style="display:flex;gap:5px">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="item_id" value="<?= $r['id'] ?>">
      <input type="number" name="quantity" value="<?= $r['quantity'] ?>" min="1" style="width:50px">
      <button type="submit">Cập nhật</button>
    </form>
  </td>
  <td><?= number_format($r['subtotal'], 0, ',', '.') ?>đ</td>
  <td>
    <form method="post">
      <input type="hidden" name="action" value="remove">
      <input type="hidden" name="item_id" value="<?= $r['id'] ?>">
      <button type="submit">Xoá</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
<p class="total">Tổng cộng: <?= number_format($total, 0, ',', '.') ?>đ</p>
<form method="get" action="checkout.php">
  <button type="submit" style="padding:10px 20px">Tiến hành thanh toán</button>
</form>
<?php endif; ?>
</body></html>

