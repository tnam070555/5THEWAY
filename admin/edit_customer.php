<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();    
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. LẤY MÃ KHÁCH HÀNG
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_customers.php');
    exit();
}

$makh = mysqli_real_escape_string($conn, $_GET['id']);
$msg = "";
$msg_type = "";

// 3. XỬ LÝ CẬP NHẬT KHI SUBMIT FORM
if (isset($_POST['btn_update'])) {
    $diem_moi = intval($_POST['diem_tich_luy']);
    $hang_moi = mysqli_real_escape_string($conn, $_POST['hang_the']);
    
    // Cập nhật vào DB với tên cột chuẩn của bạn là HangThe
    $sql_update = "UPDATE KHACHHANG SET DiemTichLuy = $diem_moi, HangThe = '$hang_moi' WHERE MaKH = '$makh'";
    
    if (mysqli_query($conn, $sql_update)) {
        $msg = "Cập nhật thông tin khách hàng thành công!";
        $msg_type = "success";
    } else {
        $msg = "Lỗi hệ thống: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

// 4. TRUY VẤN DỮ LIỆU HIỆN TẠI
$res = mysqli_query($conn, "SELECT * FROM KHACHHANG WHERE MaKH = '$makh'");
$data = mysqli_fetch_assoc($res);

if (!$data) {
    die("<h2 style='text-align:center; padding-top:50px; font-family:Inter;'>Khách hàng không tồn tại!</h2>");
}

// Gán giá trị mặc định nếu dữ liệu trong DB bị NULL để tránh lỗi Undefined Index
$current_hang = $data['HangThe'] ?? 'MEMBER';
$current_diem = $data['DiemTichLuy'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHỈNH SỬA KHÁCH HÀNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f4f7f6; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

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
        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; display: flex; flex-direction: column; align-items: center; }
        
        .header-flex { width: 100%; max-width: 600px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }
        
        .btn-back { padding: 10px 20px; background: #eee; color: #666; border-radius: 8px; font-weight: 800; text-decoration: none; font-size: 12px; transition: 0.2s; }
        .btn-back:hover { background: #ddd; }

        /* Form Card */
        .edit-card { background: #fff; width: 100%; max-width: 600px; padding: 40px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        
        .customer-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #f5f5f5; }
        .customer-header h2 { font-weight: 900; font-size: 22px; margin-bottom: 5px; }
        .customer-header p { color: #999; font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #bbb; margin-bottom: 8px; letter-spacing: 1px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; background: #fafafa; font-size: 15px; font-weight: 600; transition: 0.3s; }
        .form-control:focus { border-color: #000; outline: none; background: #fff; }

        .btn-submit { width: 100%; padding: 15px; background: var(--primary-black); color: #fff; border: none; border-radius: 12px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* Alert Box */
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; font-size: 14px; text-align: center; }
        .alert-success { background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; }
        .alert-error { background: #fff1f0; color: #ff4d4f; border: 1px solid #ffccc7; }
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
            <li><a href="manage_customers.php" class="nav-link active"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header-flex">
            <h1>Cập nhật hạng thẻ</h1>
            <a href="manage_customers.php" class="btn-back"><i class="fa-solid fa-chevron-left"></i> QUAY LẠI</a>
        </div>

        <div class="edit-card">
            <?php if($msg != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <i class="fa-solid <?php echo $msg_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> 
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="customer-header">
                <h2><?php echo htmlspecialchars($data['HoTen']); ?></h2>
                <p>Mã khách hàng: #<?php echo $data['MaKH']; ?></p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Điểm tích lũy hiện tại</label>
                    <input type="number" name="diem_tich_luy" class="form-control" 
                           value="<?php echo $current_diem; ?>" required min="0">
                </div>

                <div class="form-group">
                    <label>Phân hạng thẻ</label>
                    <select name="hang_the" class="form-control">
                        <option value="MEMBER" <?php if($current_hang == 'MEMBER') echo 'selected'; ?>>MEMBER (Mặc định)</option>
                        <option value="SILVER" <?php if($current_hang == 'SILVER') echo 'selected'; ?>>SILVER (Hạng Bạc)</option>
                        <option value="GOLD" <?php if($current_hang == 'GOLD') echo 'selected'; ?>>GOLD (Hạng Vàng)</option>
                        <option value="DIAMOND" <?php if($current_hang == 'DIAMOND') echo 'selected'; ?>>DIAMOND (Kim cương)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Thông tin đăng nhập (Chỉ xem)</label>
                    <input type="text" class="form-control" value="<?php echo $data['TenDangNhap']; ?>" disabled style="color: #999; cursor: not-allowed;">
                </div>

                <button type="submit" name="btn_update" class="btn-submit">
                    LƯU THÔNG TIN CẬP NHẬT
                </button>
            </form>
        </div>
    </main>

</body>
</html>