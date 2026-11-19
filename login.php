<?php
require 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = db();

    $stmt = $db->prepare(
        'SELECT id, name, password_hash FROM users WHERE email = ?'
    );

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
        $error = 'Email hoặc mật khẩu không chính xác. Vui lòng thử lại.';
    }

    $stmt->close();
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | MAIORA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #F8F6F1;
            color: #302A25;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        a {
            text-decoration: none;
        }

        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            padding: 22px;
            gap: 22px;
        }

        /* LEFT EDITORIAL PANEL */

        .editorial-panel {
            position: relative;
            min-height: 650px;
            overflow: hidden;
            background:
                linear-gradient(
                    180deg,
                    rgba(35, 29, 25, 0.05) 20%,
                    rgba(35, 29, 25, 0.55) 100%
                ),
                url('https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=1400&q=85')
                center 35% / cover no-repeat;
            border-radius: 4px;
        }

        .editorial-top {
            position: absolute;
            top: 38px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #FFFFFF;
        }

        .editorial-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            letter-spacing: 4px;
        }

        .editorial-label {
            font-size: 10px;
            letter-spacing: 2px;
        }

        .editorial-content {
            position: absolute;
            left: 48px;
            right: 48px;
            bottom: 55px;
            color: #FFFFFF;
        }

        .editorial-content .eyebrow {
            font-size: 10px;
            letter-spacing: 3px;
            margin-bottom: 15px;
        }

        .editorial-content h1 {
            max-width: 520px;
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(42px, 5vw, 68px);
            font-weight: 400;
            line-height: 1.05;
        }

        .editorial-content p {
            max-width: 360px;
            margin-top: 20px;
            font-size: 12px;
            line-height: 2;
            color: rgba(255,255,255,.88);
        }

        /* RIGHT LOGIN PANEL */

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 30px;
        }

        .login-content {
            width: 100%;
            max-width: 390px;
        }

        .mobile-logo {
            display: none;
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            letter-spacing: 4px;
            text-align: center;
            margin-bottom: 45px;
        }

        .login-heading {
            margin-bottom: 35px;
        }

        .login-heading .eyebrow {
            color: #A18E7C;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .login-heading h2 {
            margin: 13px 0 8px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            font-weight: 500;
            color: #302A25;
        }

        .login-heading p {
            margin: 0;
            color: #918478;
            font-size: 12px;
            line-height: 1.8;
        }

        .error-message {
            margin-bottom: 22px;
            padding: 13px 15px;
            border: 1px solid #E5D8CC;
            background: #F7F1EB;
            color: #856C5B;
            font-size: 12px;
            line-height: 1.7;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #66584C;
            font-size: 11px;
            letter-spacing: .5px;
        }

        .form-group input {
            width: 100%;
            height: 49px;
            padding: 0 15px;
            border: 1px solid #E2DBD2;
            border-radius: 2px;
            outline: none;
            background: #FFFFFF;
            color: #302A25;
            font-family: inherit;
            font-size: 12px;
            transition: .25s ease;
        }

        .form-group input::placeholder {
            color: #B4AAA0;
        }

        .form-group input:focus {
            border-color: #998675;
            box-shadow: 0 0 0 3px rgba(118,104,92,.08);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 75px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #8B7968;
            font-family: inherit;
            font-size: 10px;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            height: 49px;
            margin-top: 8px;
            border: 1px solid #76685C;
            border-radius: 2px;
            background: #76685C;
            color: #FFFFFF;
            font-family: inherit;
            font-size: 11px;
            letter-spacing: 2px;
            cursor: pointer;
            transition: .25s ease;
        }

        .login-button:hover {
            background: #302A25;
            border-color: #302A25;
        }

        .register-link {
            margin-top: 27px;
            text-align: center;
            color: #928579;
            font-size: 11px;
        }

        .register-link a {
            color: #76685C;
            font-weight: 500;
            border-bottom: 1px solid #CFC2B4;
            padding-bottom: 3px;
        }

        .register-link a:hover {
            color: #302A25;
        }

        .back-home {
            display: block;
            margin-top: 38px;
            text-align: center;
            color: #A09285;
            font-size: 10px;
            letter-spacing: 1px;
        }

        .back-home:hover {
            color: #302A25;
        }

        .login-footer {
            margin-top: 55px;
            text-align: center;
            color: #B0A59A;
            font-size: 9px;
            letter-spacing: 1.5px;
        }

        @media (max-width: 850px) {
            .login-wrapper {
                grid-template-columns: 0.85fr 1.15fr;
                padding: 12px;
                gap: 12px;
            }

            .editorial-panel {
                min-height: 600px;
            }

            .editorial-top {
                left: 22px;
                right: 22px;
            }

            .editorial-content {
                left: 25px;
                right: 25px;
                bottom: 35px;
            }

            .login-panel {
                padding: 30px 20px;
            }
        }

        @media (max-width: 620px) {
            .login-wrapper {
                display: block;
                padding: 18px;
            }

            .editorial-panel {
                display: none;
            }

            .login-panel {
                min-height: calc(100vh - 36px);
                padding: 35px 12px;
            }

            .mobile-logo {
                display: block;
            }

            .login-heading h2 {
                font-size: 42px;
            }

            .login-footer {
                margin-top: 45px;
            }
        }
    </style>
</head>

<body>
<div class="login-wrapper">

    <section class="editorial-panel">
        <div class="editorial-top">
            <div class="editorial-logo">MAIORA</div>
            <div class="editorial-label">FASHION & LIFESTYLE</div>
        </div>

        <div class="editorial-content">
            <div class="eyebrow">THE ART OF EVERYDAY STYLE</div>
            <h1>Elegance is an attitude.</h1>
            <p>
                Khám phá phong cách riêng của bạn cùng MAIORA.
                Tinh tế trong từng lựa chọn, thanh lịch trong từng khoảnh khắc.
            </p>
        </div>
    </section>

    <main class="login-panel">
        <div class="login-content">

            <div class="mobile-logo">MAIORA</div>

            <div class="login-heading">
                <div class="eyebrow">WELCOME BACK</div>
                <h2>Chào mừng trở lại</h2>
                <p>Đăng nhập để tiếp tục trải nghiệm mua sắm tại MAIORA.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="email">ĐỊA CHỈ EMAIL</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Nhập email của bạn"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">MẬT KHẨU</label>
                    <div class="password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Nhập mật khẩu"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                        >HIỆN</button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    ĐĂNG NHẬP
                </button>
            </form>

            <div class="register-link">
                Chưa có tài khoản?
                <a href="register.php">Tạo tài khoản</a>
            </div>

            <a href="products.php" class="back-home">
                ← QUAY LẠI CỬA HÀNG
            </a>

            <div class="login-footer">
                MAIORA · FASHION & LIFESTYLE
            </div>

        </div>
    </main>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const button = document.querySelector('.toggle-password');

    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'ẨN';
    } else {
        input.type = 'password';
        button.textContent = 'HIỆN';
    }
}
</script>
</body>
</html>
