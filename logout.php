<?php
session_start();

// Xóa toàn bộ dữ liệu phiên đăng nhập
$_SESSION = [];

// Xóa cookie phiên nếu đang sử dụng cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hủy phiên
session_destroy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng xuất | MAIORA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-page {
            min-height: 75vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 18px;
            box-sizing: border-box;
        }

        .logout-card {
            width: min(460px, 100%);
            padding: 55px 35px;
            box-sizing: border-box;
            text-align: center;
            background: #FFFFFF;
            border: 1px solid #EAE5DD;
            border-radius: 4px;
            box-shadow: 0 12px 35px rgba(48, 42, 37, .05);
        }

        .logout-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            border: 1px solid #DED4C8;
            border-radius: 50%;
            color: #76685C;
            font-size: 25px;
        }

        .logout-card .eyebrow {
            color: #9A8877;
            font-size: 11px;
            letter-spacing: 3px;
        }

        .logout-card h1 {
            margin: 15px 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 500;
            color: #302A25;
        }

        .logout-card p {
            color: #887B70;
            font-size: 13px;
            line-height: 1.8;
        }

        .logout-button {
            display: inline-block;
            margin-top: 22px;
            padding: 13px 30px;
            background: #76685C;
            border: 1px solid #76685C;
            border-radius: 3px;
            color: #FFFFFF !important;
            font-size: 12px;
            letter-spacing: 1px;
            text-decoration: none;
            transition: .2s ease;
        }

        .logout-button:hover {
            background: #302A25;
            border-color: #302A25;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main class="logout-page">
        <section class="logout-card">
            <div class="logout-icon">✓</div>
            <div class="eyebrow">MAIORA · FASHION & LIFESTYLE</div>
            <h1>Đăng xuất thành công</h1>
            <p>
                Phiên đăng nhập của bạn đã kết thúc.
                Cảm ơn bạn đã ghé thăm MAIORA.
            </p>
            <a class="logout-button" href="login.php">
                ĐĂNG NHẬP LẠI
            </a>
        </section>
    </main>
</body>
</html>
