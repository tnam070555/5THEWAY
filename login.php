<?php
session_start();
include 'includes/db_connect.php'; 
$error = "";

if (isset($_POST['btn_login'])) {
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = $_POST['password']; 

    // 1. Kiểm tra trong bảng NHANVIEN (Admin/Staff)
    $sql_admin = "SELECT * FROM NHANVIEN WHERE TenDangNhap = '$u'";
    $res_admin = mysqli_query($conn, $sql_admin);

    // 2. Kiểm tra trong bảng KHACHHANG (Khách hàng)
    $sql_user = "SELECT * FROM KHACHHANG WHERE TenDangNhap = '$u'";
    $res_user = mysqli_query($conn, $sql_user);

    if ($res_admin && mysqli_num_rows($res_admin) > 0) {
        $row = mysqli_fetch_assoc($res_admin);
        
        // Kiểm tra mật khẩu (hỗ trợ cả mã hóa password_hash và văn bản thô để Nam dễ test)
        if (password_verify($p, $row['MatKhau']) || $p == $row['MatKhau']) {
            
            // --- LOGIC ADMIN ĐỒNG BỘ Ở ĐÂY ---
            $_SESSION['user_admin'] = $row['MaNV'];
            $_SESSION['admin_name'] = $row['HoTen'];
            $_SESSION['user_name']  = $row['HoTen']; // Dùng chung để hiển thị Header nếu cần
            
            // Nếu bảng NHANVIEN của Nam có cột HinhAnh, hãy bỏ comment dòng dưới
            // $_SESSION['user_avatar'] = $row['HinhAnh']; 
            
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Mật khẩu Admin không chính xác.";
        }
    } elseif ($res_user && mysqli_num_rows($res_user) > 0) {
        $row = mysqli_fetch_assoc($res_user);
        
        if (password_verify($p, $row['MatKhau']) || $p == $row['MatKhau']) {
            $_SESSION['user_id'] = $row['MaKH'];
            $_SESSION['user_name'] = $row['HoTen'];
            $_SESSION['user_avatar'] = $row['HinhAnh']; 
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Mật khẩu không chính xác.";
        }
    } else {
        $error = "Tài khoản không tồn tại trên hệ thống.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ĐĂNG NHẬP | 5THEWAY SYSTEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-bg: #ffffff;
            --text-color: #000000;
            --input-bg: #f9f9f9;
            --input-border: #e0e0e0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--main-bg); color: var(--text-color); display: flex; height: 100vh; overflow: hidden; }
        .login-visual { flex: 1.3; background: url('assets/img/login-banner.jpg') no-repeat center center; background-size: cover; display: flex; align-items: flex-end; padding: 60px; position: relative; }
        .login-visual::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent); }
        .visual-text { color: #fff; z-index: 2; position: relative; }
        .visual-text h1 { font-size: 70px; font-weight: 900; line-height: 0.9; margin-bottom: 15px; letter-spacing: -3px; text-transform: uppercase; }
        .login-section { flex: 1; display: flex; justify-content: center; align-items: center; padding: 0 6%; position: relative; background: #fff; }
        .login-box { width: 100%; max-width: 440px; }
        .logo-top { text-align: center; margin-bottom: 60px; }
        .logo-top img { height: 80px; width: auto; object-fit: contain; }
        h2 { 
            font-size: 24px; font-weight: 900; margin-bottom: 50px; letter-spacing: -1px; 
            text-align: center; text-transform: uppercase; position: relative; padding-bottom: 15px;
        }
        h2::after { content: ""; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 50px; height: 3px; background: #000; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 11px; font-weight: 700; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #666; }
        input { 
            width: 100%; padding: 18px 20px; background: var(--input-bg); border: 1.5px solid var(--input-border); 
            font-size: 14px; outline: none; transition: 0.3s; border-radius: 8px;
        }
        input:focus { border-color: #000; background: #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .btn-login { 
            width: 100%; padding: 20px; background: #000; color: #fff; border: 1px solid #000; 
            font-weight: 700; text-transform: uppercase; cursor: pointer; margin-top: 25px; 
            letter-spacing: 2.5px; transition: 0.3s; border-radius: 8px; font-size: 13px;
        }
        .btn-login:hover { background: #fff; color: #000; }
        .error-msg { 
            background: #fff5f5; color: #d00000; padding: 15px; border-radius: 8px; 
            font-size: 13px; margin-bottom: 30px; text-align: center; border: 1px solid #ffdada;
            font-weight: 600;
        }
        .auth-nav { margin-top: 40px; font-size: 14px; color: #777; text-align: center; }
        .auth-nav a { color: #000; font-weight: 700; text-decoration: none; border-bottom: 2px solid #000; }
        .student-info { position: absolute; bottom: 30px; font-size: 11px; color: #bbb; text-align: center; width: 100%; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="login-visual">
        <div class="visual-text">
            <h1>5THEWAY<br>STREETWEAR</h1>
        </div>
    </div>
    <div class="login-section">
        <div class="login-box">
            <div class="logo-top">
                <a href="index.php"><img src="assets/img/logo.png" alt="5THEWAY"></a>
            </div>  
            <h2>Đăng nhập hệ thống</h2>
            <?php if($error != "") echo "<div class='error-msg'>$error</div>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" placeholder="Nhập username..." required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" placeholder="••••••••••••" required>
                </div>
                <button type="submit" name="btn_login" class="btn-login">Xác nhận đăng nhập</button>
            </form>
            <div class="auth-nav">
                Bạn chưa có tài khoản? <a href="register.php">Đăng ký thành viên</a>
            </div>
        </div>
        <div class="student-info">
        </div>
    </div>
</body>
</html>