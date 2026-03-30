<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) { header('Location: ../login.php'); exit(); }

$msg = "";
if (isset($_POST['btn_add'])) {
    $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = md5($_POST['password']); // Hoặc password_hash tùy hệ thống của bạn
    $quyen = mysqli_real_escape_string($conn, $_POST['quyen']);
    
    // Kiểm tra trùng username
    $check = mysqli_query($conn, "SELECT * FROM NHANVIEN WHERE TenDangNhap = '$user'");
    if(mysqli_num_rows($check) > 0) {
        $msg = "Tên đăng nhập đã tồn tại!";
    } else {
        $sql = "INSERT INTO NHANVIEN (HoTen, TenDangNhap, MatKhau, Quyen) VALUES ('$hoten', '$user', '$pass', '$quyen')";
        if(mysqli_query($conn, $sql)){
            header("Location: manage_staff.php?msg=added");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>THÊM NHÂN VIÊN | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* CSS tương tự add_product.php */
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f4f7f6; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); }
        /* --- SIDEBAR STYLE --- */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 8px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { margin-top: auto; background: #222; color: #ff4d4d !important; }
        
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .card { background: #fff; padding: 30px; border-radius: 16px; border: 1px solid #eee; width: 600px; margin: 0 auto; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #999; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; font-size: 14px; background: #fafafa; }
        .btn-submit { background: #000; color: #fff; width: 100%; padding: 15px; border: none; border-radius: 10px; font-weight: 800; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link "><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            <li><a href="manage_orders.php" class="nav-link"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link active"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main">
        <div class="card">
            <h2 style="font-weight: 900; margin-bottom: 25px; text-transform: uppercase;">Thêm nhân sự mới</h2>
            <?php if($msg) echo "<p style='color:red; font-weight:bold; margin-bottom: 15px;'>$msg</p>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="hoten" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phân quyền</label>
                    <select name="quyen" class="form-control" required>
                        <option value="Nhân viên">Nhân viên bán hàng</option>
                        <option value="Kho">Thủ kho</option>
                        <option value="Admin">Quản trị viên</option>
                    </select>
                </div>
                <button type="submit" name="btn_add" class="btn-submit">TẠO TÀI KHOẢN</button>
            </form>
        </div>
    </main>
</body>
</html>