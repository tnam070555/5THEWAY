<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Nhận tham số sid (Staff ID)
if (!isset($_GET['sid']) || empty($_GET['sid'])) {
    header('Location: manage_staff.php');
    exit();
}

$manv = mysqli_real_escape_string($conn, $_GET['sid']);
$msg = "";
$msg_type = "";

// 2. XỬ LÝ CẬP NHẬT KHI NHẤN NÚT
if (isset($_POST['btn_update'])) {
    $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $quyen = mysqli_real_escape_string($conn, $_POST['quyen']);
    $password = $_POST['password'];

    // Kiểm tra tên đăng nhập có bị trùng với người khác không
    $check_user = mysqli_query($conn, "SELECT MaNV FROM NHANVIEN WHERE TenDangNhap = '$username' AND MaNV != '$manv'");
    if (mysqli_num_rows($check_user) > 0) {
        $msg = "Tên đăng nhập này đã tồn tại!";
        $msg_type = "error";
    } else {
        if (!empty($password)) {
            $pass_hash = md5($password); // Dùng md5 theo chuẩn chung của bạn
            $sql_update = "UPDATE NHANVIEN SET HoTen = '$hoten', TenDangNhap = '$username', MatKhau = '$pass_hash', Quyen = '$quyen' WHERE MaNV = '$manv'";
        } else {
            $sql_update = "UPDATE NHANVIEN SET HoTen = '$hoten', TenDangNhap = '$username', Quyen = '$quyen' WHERE MaNV = '$manv'";
        }

        if (mysqli_query($conn, $sql_update)) {
            $msg = "Cập nhật thành công!";
            $msg_type = "success";
        } else {
            $msg = "Lỗi: " . mysqli_error($conn);
            $msg_type = "error";
        }
    }
}

// 3. LẤY DỮ LIỆU NHÂN VIÊN HIỆN TẠI
$res = mysqli_query($conn, "SELECT * FROM NHANVIEN WHERE MaNV = '$manv'");
$staff = mysqli_fetch_assoc($res);

if (!$staff) {
    die("<h2 style='text-align:center; margin-top:50px;'>Nhân viên không tồn tại!</h2>");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>SỬA NHÂN VIÊN | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; display: flex; justify-content: center; }
        
        .card { background: #fff; width: 100%; max-width: 500px; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 8px; text-transform: uppercase; color: #666; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-weight: 600; }
        .form-control:focus { border-color: #000; }
        
        .btn-save { width: 100%; padding: 15px; background: #000; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px; font-weight: 600; }
        .alert-success { background: #e6fffa; color: #2d6a4f; }
        .alert-error { background: #fff5f5; color: #c53030; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2 style="text-align:center; margin-bottom:40px;">5THEWAY</h2>
        <a href="manage_staff.php" style="color:#fff; text-decoration:none; font-weight:700;"><i class="fa-solid fa-arrow-left"></i> QUAY LẠI</a>
    </aside>

    <main class="main-content">
        <div class="card">
            <h2 style="margin-bottom: 25px; text-transform: uppercase; font-weight: 900;">Chỉnh sửa nhân sự</h2>
            
            <?php if($msg): ?>
                <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input type="text" name="hoten" class="form-control" value="<?= htmlspecialchars($staff['HoTen']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($staff['TenDangNhap']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Phân quyền</label>
                    <select name="quyen" class="form-control">
                        <option value="Admin" <?= $staff['Quyen']=='Admin'?'selected':'' ?>>Quản trị viên</option>
                        <option value="Kho" <?= $staff['Quyen']=='Kho'?'selected':'' ?>>Thủ kho</option>
                        <option value="Sale" <?= $staff['Quyen']=='Sale'?'selected':'' ?>>Bán hàng</option>
                        <option value="Nhân viên" <?= $staff['Quyen']=='Nhân viên'?'selected':'' ?>>Nhân viên</option>
                    </select>
                </div>
                <button type="submit" name="btn_update" class="btn-save">LƯU THAY ĐỔI</button>
            </form>
        </div>
    </main>
</body>
</html>