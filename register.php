<?php
require 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $name && $password) {
        $db = db();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            'INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $email, $hash, $name);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $db->insert_id;
            $_SESSION['user_name'] = $name;

            header('Location: products.php');
            exit;
        } else {
            $error = 'Email đã được sử dụng. Vui lòng thử email khác.';
        }

        $stmt->close();
        $db->close();
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | MAIORA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #F8F6F1;
            color: #302A25;
            font-family: 'Be Vietnam Pro', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 20px;
        }

        .register-wrapper {
            width: 100%;
            max-width: 1120px;
            min-height: 690px;
            background: #FFFFFF;
            display: grid;
            grid-template-columns: 1fr 0.9fr;
            overflow: hidden;
            border-radius: 5px;
            box-shadow: 0 18px 55px rgba(48, 42, 37, 0.08);
        }

        .editorial-panel {
            position: relative;
            min-height: 690px;
            background:
                linear-gradient(
                    to bottom,
                    rgba(35, 29, 24, 0.12),
                    rgba(35, 29, 24, 0.52)
                ),
                url('https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=1200&q=85')
                center center / cover no-repeat;
            display: flex;
            align-items: flex-end;
            padding: 55px;
            color: #FFFFFF;
        }

        .brand {
            position: absolute;
            top: 38px;
            left: 45px;
            color: #FFFFFF;
            font-family: 'Cormorant Garamond', serif;
            font-size: 37px;
            letter-spacing: 5px;
            font-weight: 500;
        }

        .editorial-content {
            max-width: 390px;
        }

        .editorial-label {
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 17px;
            opacity: 0.9;
        }

        .editorial-content h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 55px;
            line-height: 1.08;
            font-weight: 400;
            margin-bottom: 18px;
        }

        .editorial-content p {
            font-size: 12px;
            line-height: 2;
            font-weight: 300;
            max-width: 330px;
            color: rgba(255,255,255,0.9);
        }

        .register-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 55px 65px;
        }

        .register-content {
            width: 100%;
            max-width: 360px;
        }

        .top-label {
            color: #998B7D;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .register-content h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 43px;
            font-weight: 500;
            color: #302A25;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #8B8178;
            font-size: 12px;
            line-height: 1.8;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            color: #51483F;
            margin-bottom: 9px;
        }

        .input-wrapper {
            position: relative;
        }

.form-group input {
    width: 100%;
    height: 47px;
    padding: 0 14px;
    border: 1px solid #E5DED5;
    background-color: #FDFCFA;
    color: #302A25;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 12px;
    outline: none;
    transition: 0.25s;
    box-shadow: none;
    appearance: none;
    -webkit-appearance: none;
}

.form-group input:focus {
    border-color: #998675;
    background-color: #FDFCFA;
    box-shadow: none;
}
        .password-input {
            padding-right: 75px !important;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #897B6D;
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 10px;
            cursor: pointer;
        }

        .error {
            padding: 12px 14px;
            margin-bottom: 20px;
            background: #F8F1ED;
            border-left: 3px solid #A77B67;
            color: #895D4C;
            font-size: 11px;
            line-height: 1.6;
        }

        .register-button {
            width: 100%;
            height: 49px;
            margin-top: 8px;
            border: 1px solid #76685C;
            background: #76685C;
            color: #FFFFFF;
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.25s;
        }

        .register-button:hover {
            background: #5F5349;
            border-color: #5F5349;
        }

        .login-link {
            margin-top: 25px;
            text-align: center;
            font-size: 11px;
            color: #8B8178;
        }

        .login-link a,
        .back-link {
            color: #76685C;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover,
        .back-link:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            font-size: 11px;
        }

        @media (max-width: 800px) {
            .register-wrapper {
                max-width: 480px;
                grid-template-columns: 1fr;
            }

            .editorial-panel {
                min-height: 260px;
                padding: 35px;
            }

            .brand {
                top: 25px;
                left: 30px;
                font-size: 30px;
            }

            .editorial-content h1 {
                font-size: 36px;
                margin-bottom: 8px;
            }

            .editorial-content p {
                font-size: 10px;
            }

            .editorial-label {
                margin-bottom: 8px;
            }

            .register-panel {
                padding: 45px 30px;
            }
        }

        @media (max-width: 420px) {
            body {
                padding: 12px;
            }

            .register-panel {
                padding: 35px 22px;
            }
        }
    </style>
</head>

<body>
    <main class="register-wrapper">

        <section class="editorial-panel">
            <div class="brand">MAIORA</div>

            <div class="editorial-content">
                <div class="editorial-label">A timeless wardrobe</div>
                <h1>Discover your own elegance.</h1>
                <p>
                    Phong cách không chỉ nằm ở những gì bạn mặc,
                    mà còn là cách bạn thể hiện chính mình.
                    Cùng MAIORA tạo nên dấu ấn riêng qua từng lựa chọn.
                </p>
            </div>
        </section>

        <section class="register-panel">
            <div class="register-content">

                <div class="top-label">Welcome to MAIORA</div>

                <h2>Tạo tài khoản</h2>

                <p class="subtitle">
                    Bắt đầu hành trình khám phá phong cách của riêng bạn.
                </p>

                <?php if ($error): ?>
                    <div class="error">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="register.php">

                    <div class="form-group">
                        <label for="name">Họ và tên</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Nhập họ và tên của bạn"
                            value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Địa chỉ email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="example@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="password-input"
                                placeholder="Tạo mật khẩu"
                                required
                            >
                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('password', this)"
                            >Hiển thị</button>
                        </div>
                    </div>

                    <button type="submit" class="register-button">
                        Tạo tài khoản
                    </button>

                </form>

                <div class="login-link">
                    Đã có tài khoản?
                    <a href="login.php">Đăng nhập</a>
                </div>

                <a href="products.php" class="back-link">
                    ← Quay lại cửa hàng
                </a>

            </div>
        </section>

    </main>

    <script>
        function togglePassword(id, button) {
            const input = document.getElementById(id);

            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Ẩn';
            } else {
                input.type = 'password';
                button.textContent = 'Hiển thị';
            }
        }
    </script>
</body>
</html>
