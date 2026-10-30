<?php
require 'auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $name = trim($_POST['name']);
  $password = $_POST['password'];

  if ($email && $password) {
    $db = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $email, $hash, $name);

    if ($stmt->execute()) {
      $_SESSION['user_id'] = $db->insert_id;
      $_SESSION['user_name'] = $name;
      header('Location: products.php');
      exit;
    } else {
      $error = 'Email đã được sử dụng.';
    }
  } else {
    $error = 'Vui lòng nhập đủ thông tin.';
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Đăng ký</title>
<style>body{font-family:Arial;max-width:400px;margin:60px auto}
input{width:100%;padding:8px;margin:6px 0}
.error{color:red}</style></head>
<body>
<h1>Đăng ký</h1>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="text" name="name" placeholder="Họ tên" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Mật khẩu" required>
  <button type="submit">Đăng ký</button>
</form>
<p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</body></html>

