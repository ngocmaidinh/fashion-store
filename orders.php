<?php
require 'auth.php';
requireLogin();
$db = db();
$userId = $_SESSION['user_id'];

// Xử lý xoá đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  $orderId = (int)$_POST['order_id'];

  $db->begin_transaction();
  try {
    $stmt = $db->prepare('DELETE FROM order_items WHERE order_id = ?');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();

    $stmt = $db->prepare('DELETE FROM orders WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();

    $db->commit();
  } catch (Exception $e) {
    $db->rollback();
  }

  header('Location: orders.php');
  exit;
}

$stmt = $db->prepare('SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Đơn hàng của tôi</title>
<style>
 body{font-family:Arial;margin:30px}
 table{border-collapse:collapse;width:100%;max-width:700px}
 td,th{border:1px solid #ddd;padding:8px;text-align:left}
 nav{margin-bottom:20px}
 nav a{margin-right:15px}
 button.danger{background:#d9534f;color:white;border:none;padding:6px 12px;cursor:pointer;border-radius:4px}
</style>    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
  <a href="products.php">Sản phẩm</a>
  <a href="cart.php">Giỏ hàng</a>
  <a href="orders.php">Đơn hàng</a> |
  <a href="logout.php">Đăng xuất</a>
</nav>
<h1>Đơn hàng của tôi</h1>
<table>
<tr><th>Mã đơn</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th></th></tr>
<?php while ($o = $orders->fetch_assoc()): ?>
<tr>
  <td>#<?= $o['id'] ?></td>
  <td><?= number_format($o['total'], 0, ',', '.') ?>đ</td>
  <td><?= htmlspecialchars($o['status']) ?></td>
  <td><?= $o['created_at'] ?></td>
  <td>
    <form method="post" onsubmit="return confirm('Xoá đơn hàng #<?= $o['id'] ?>?');">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
      <button type="submit" class="danger">Xoá</button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
