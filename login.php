<?php
require 'auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $db = db();
  $stmt = $db->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    header('Location: products.php');
    exit;
  } else {
    $error = 'Email hoặc mật khẩu không đúng.';
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Đăng nhập</title>
<style>body{font-family:Arial;max-width:400px;margin:60px auto}
input{width:100%;padding:8px;margin:6px 0}
.error{color:red}</style></head>
<body>
<h1>Đăng nhập</h1>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Mật khẩu" required>
  <button type="submit">Đăng nhập</button>
</form>
<p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
</body></html>
