<?php
session_start();
include 'includes/db_connect.php';
$msg = "";
$error = "";

if (isset($_POST['btn_register'])) {
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = $_POST['password']; // Lấy pass thô
    $name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // 1. Kiểm tra username tồn tại
    $check = "SELECT * FROM KHACHHANG WHERE TenDangNhap = '$u'";
    $res_check = mysqli_query($conn, $check);

    if (mysqli_num_rows($res_check) > 0) {
        $error = "Tên đăng nhập này đã có người sử dụng!";
    } else {
        // 2. MÃ HÓA MẬT KHẨU (Web thật phải dùng cái này)
        $hashed_password = password_hash($p, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO KHACHHANG (TenDangNhap, MatKhau, HoTen, Email) VALUES ('$u', '$hashed_password', '$name', '$email')";
        if (mysqli_query($conn, $sql)) {
            $msg = "Đăng ký thành công! Đang chuyển hướng sau 2 giây...";
            header("refresh:2; url=login.php");
        } else {
            $error = "Lỗi hệ thống: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ĐĂNG KÝ THÀNH VIÊN | 5THEWAY</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --main-bg: #ffffff; --text-color: #000000; --input-bg: #f9f9f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { background: var(--main-bg); color: var(--text-color); display: flex; height: 100vh; overflow: hidden; flex-direction: row-reverse; } /* Đảo chiều để ảnh bên phải */

        /* Visual bên phải */
        .reg-visual { flex: 1.3; background: url('assets/img/register-banner2.jpg') no-repeat center center; background-size: cover; display: flex; align-items: flex-end; padding: 60px; position: relative; }
        .reg-visual::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent); }
        
        .visual-text { color: #fff; z-index: 2; position: relative; }
        .visual-text h1 { font-size: 70px; font-weight: 900; line-height: 0.9; margin-bottom: 15px; letter-spacing: -3px; text-transform: uppercase; }

        /* Section Form bên trái */
        .reg-section { flex: 1; display: flex; justify-content: center; align-items: center; padding: 0 6%; position: relative; background: #fff; }
        .reg-box { width: 100%; max-width: 440px; }
        
        .logo-top { text-align: center; margin-bottom: 40px; }
        .logo-top img { height: 70px; width: auto; }

        h2 { font-size: 28px; font-weight: 900; margin-bottom: 35px; letter-spacing: -1px; text-align: center; text-transform: uppercase; position: relative; padding-bottom: 12px; }
        h2::after { content: ""; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 40px; height: 3px; background: #000; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; } /* Chia cột cho Họ tên và Email cho gọn */

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 10px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; color: #777; }
        
        input { width: 100%; padding: 15px; background: var(--input-bg); border: 1.5px solid #eee; font-size: 14px; outline: none; transition: 0.3s; border-radius: 6px; }
        input:focus { border-color: #000; background: #fff; }

        .btn-reg { width: 100%; padding: 18px; background: #000; color: #fff; border: 1px solid #000; font-weight: 700; text-transform: uppercase; cursor: pointer; margin-top: 15px; letter-spacing: 2px; transition: 0.3s; border-radius: 6px; }
        .btn-reg:hover { background: #fff; color: #000; }

        .msg { padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .error { background: #fff5f5; color: #d00000; border: 1px solid #ffdada; }
        .success { background: #f5fff5; color: #008800; border: 1px solid #daffda; }

        .auth-nav { margin-top: 30px; font-size: 14px; color: #777; text-align: center; }
        .auth-nav a { color: #000; font-weight: 700; text-decoration: none; border-bottom: 2px solid #000; }

        .student-info { position: absolute; bottom: 25px; font-size: 10px; color: #bbb; letter-spacing: 1px; text-align: center; width: 100%; font-weight: 600; }
    </style>
</head>
<body>

    <div class="reg-visual">
        <div class="visual-text">
            <h1>JOIN THE<br>COMMUNITY</h1>
            <p>Become a member of 5THEWAY today.</p>
        </div>
    </div>

    <div class="reg-section">
        <div class="reg-box">
            <div class="logo-top">
                <a href="index.php"><img src="assets/img/logo.png" alt="5THEWAY"></a>
            </div>
            
            <h2>Đăng ký</h2>

            <?php if($error != "") echo "<div class='msg error'>$error</div>"; ?>
            <?php if($msg != "") echo "<div class='msg success'>$msg</div>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" placeholder="Nhập họ tên..." required>
                </div>

                <div class="form-group">
                    <label>Địa chỉ Email</label>
                    <input type="email" name="email" placeholder="abc@example.com" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" name="username" placeholder="username123" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" name="btn_register" class="btn-reg">Tạo tài khoản ngay</button>
            </form>

            <div class="auth-nav">
                Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a>
            </div>
        </div>

        <div class="student-info">
        
        </div>
    </div>

</body>
</html>