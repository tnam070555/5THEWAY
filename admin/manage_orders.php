<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) { header('Location: ../login.php'); exit(); }
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Cập nhật trạng thái nhanh từ danh sách
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];
    $new_status = "";
    
    if ($action == 'approve') $new_status = "Đang giao";
    elseif ($action == 'complete') $new_status = "Hoàn thành";
    
    if ($new_status != "") {
        mysqli_query($conn, "UPDATE HOADON SET TrangThai = '$new_status' WHERE MaHD = '$id'");
        header("Location: manage_orders.php?msg=updated");
        exit();
    }
}

// Lấy danh sách hóa đơn
$sql = "SELECT * FROM HOADON ORDER BY NgayLap DESC";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>QUẢN LÝ HÓA ĐƠN | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

        /* Sidebar & Header */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 8px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { margin-top: auto; color: #ff4d4d !important; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .top-header h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }

        /* Table */
        .data-box { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #aaa; font-size: 11px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #f8f9fa; }
        td { padding: 18px 15px; border-bottom: 1px solid #f8f9fa; font-size: 14px; vertical-align: middle; }

        .cust-name { font-weight: 800; color: #000; display: block; }
        .cust-phone { font-size: 12px; color: #888; }
        
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .status-pending { background: #fff3e0; color: #f57c00; }
        .status-shipping { background: #e3f2fd; color: #1976d2; }
        .status-done { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }

        .btn-group { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-action { padding: 8px 15px; border-radius: 6px; font-size: 12px; font-weight: 800; text-decoration: none; transition: 0.2s; border: 1px solid #eee; color: #000; }
        .btn-action:hover { background: #000; color: #fff; }
        .btn-view { background: #000; color: #fff; }
        .btn-view:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link"><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            <li><a href="manage_orders.php" class="nav-link active"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Quản lý hóa đơn</h1>
            <div class="user-pill"><i class="fa-solid fa-circle-user"></i><span><?= htmlspecialchars($admin_name) ?></span></div>
        </div>

        <div class="data-box">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res)) { 
                        $st = $row['TrangThai'];
                        $cls = 'status-pending';
                        if($st == 'Đang giao') $cls = 'status-shipping';
                        if($st == 'Hoàn thành') $cls = 'status-done';
                        if($st == 'Đã hủy') $cls = 'status-cancelled';
                    ?>
                        <tr>
                            <td><strong>#<?= $row['MaHD'] ?></strong></td>
                            <td>
                                <span class="cust-name"><?= htmlspecialchars($row['HoTen']) ?></span>
                                <span class="cust-phone"><?= $row['SDT'] ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['NgayLap'])) ?></td>
                            <td style="font-weight: 800;"><?= number_format($row['TongTien'], 0, ',', '.') ?>đ</td>
                            <td><span class="badge <?= $cls ?>"><?= $st ?></span></td>
                            <td>
                                <div class="btn-group">
                                    <?php if($st == 'Chờ xử lý'): ?>
                                        <a href="?action=approve&id=<?= $row['MaHD'] ?>" class="btn-action" title="Duyệt đơn"><i class="fa-solid fa-truck-fast"></i></a>
                                    <?php endif; ?>
                                    <a href="order_detail.php?id=<?= $row['MaHD'] ?>" class="btn-action btn-view">CHI TIẾT</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>