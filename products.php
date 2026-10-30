<?php
require 'auth.php';
$c = require '/etc/shop/db-config.php';
$db = db();
$rs = $db->query('SELECT id, name, price, image FROM products');
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Fashion Store</title>
<style>
 body{font-family:Arial;margin:30px}
 .grid{display:flex;flex-wrap:wrap;gap:20px}
 .card{border:1px solid #ddd;padding:12px;width:200px;text-align:center}
 .card img{width:100%;height:200px;object-fit:cover}
 .card button{width:100%;padding:8px;margin-top:8px;cursor:pointer}
 nav{margin-bottom:20px}
 nav a{margin-right:15px}
</style></head>
<body>
<nav>
  <a href="products.php">Sản phẩm</a>
  <a href="cart.php">Giỏ hàng</a>
  <?php if (isLoggedIn()): ?>
    <a href="orders.php">Đơn hàng</a>
    Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?> |
    <a href="logout.php">Đăng xuất</a>
  <?php else: ?>
    <a href="login.php">Đăng nhập</a>
  <?php endif; ?>
</nav>
<h1>Fashion Store</h1>
<div class="grid">
<?php while ($p = $rs->fetch_assoc()): ?>
  <div class="card">
    <img src="<?= htmlspecialchars($c['img_base'] ?? '') ?><?= htmlspecialchars($p['image']) ?>" alt="">
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p><?= number_format($p['price'], 0, ',', '.') ?>đ</p>
    <form method="post" action="cart.php">
      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
      <input type="hidden" name="action" value="add">
      <button type="submit">Thêm vào giỏ</button>
    </form>
  </div>
<?php endwhile; ?>
</div>
</body></html>
