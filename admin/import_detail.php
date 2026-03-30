<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Quản trị viên';

// 2. NHẬN MÃ PHIẾU NHẬP
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: imports.php');
    exit();
}

$mapn = mysqli_real_escape_string($conn, $_GET['id']);

// 3. TRUY VẤN THÔNG TIN CHUNG CỦA PHIẾU NHẬP
$sql_phieu = "SELECT pn.*, nv.HoTen 
              FROM PHIEUNHAP pn 
              LEFT JOIN NHANVIEN nv ON pn.MaNV = nv.MaNV 
              WHERE pn.MaPN = '$mapn'";
$res_phieu = mysqli_query($conn, $sql_phieu);
$phieu = mysqli_fetch_assoc($res_phieu);

if (!$phieu) {
    die("<h2 style='text-align:center; margin-top:50px;'>Phiếu nhập không tồn tại!</h2>");
}

// 4. TRUY VẤN CHI TIẾT (Đã sửa tên bảng thành chitiet_phieunhap và cột GiaNhap)
$sql_ct = "SELECT ct.*, sp.TenSP 
           FROM chitiet_phieunhap ct 
           JOIN SANPHAM sp ON ct.MaSP = sp.MaSP 
           WHERE ct.MaPN = '$mapn'";
$res_ct = mysqli_query($conn, $sql_ct);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CHI TIẾT PHIẾU NHẬP #<?= str_pad($mapn, 6, '0', STR_PAD_LEFT) ?> | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

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
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { color: #000; text-decoration: none; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; border: none; background: none; cursor: pointer; }

        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .info-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee; }
        .info-card label { display: block; font-size: 11px; color: #aaa; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
        .info-card span { font-weight: 700; font-size: 15px; color: #000; }

        /* Table Card */
        .card-table { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 0; border: 1px solid #eee; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 18px 25px; color: #bbb; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f9f9f9; background: #fafafa; }
        td { padding: 20px 25px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        
        .total-row { background: #000; color: #fff; }
        .total-row td { border: none; padding: 25px; font-weight: 900; font-size: 18px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link"><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            <li><a href="manage_orders.php" class="nav-link"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link active"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>
    <main class="main">
        <div class="top-header">
            <div>
                <h1 style="font-weight: 900; text-transform: uppercase;">Chi tiết phiếu nhập</h1>
                <p style="color: #999; font-size: 13px;">Mã định danh: #PN<?= str_pad($phieu['MaPN'], 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            <button onclick="window.print()" class="btn-back">
                <i class="fa-solid fa-print"></i> IN PHIẾU
            </button>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <label>Nhà cung cấp</label>
                <span><?= htmlspecialchars($phieu['NhaCungCap']) ?></span>
            </div>
            <div class="info-card">
                <label>Nhân viên nhập hàng</label>
                <span><?= htmlspecialchars($phieu['HoTen'] ?? 'Hệ thống') ?></span>
            </div>
            <div class="info-card">
                <label>Ngày nhập kho</label>
                <span><?= date('d/m/Y | H:i', strtotime($phieu['NgayNhap'])) ?></span>
            </div>
        </div>

        <div class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>Mã Sản phẩm</th>
                        <th>Tên Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá nhập</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_qty = 0;
                    if (mysqli_num_rows($res_ct) > 0) {
                        while ($row = mysqli_fetch_assoc($res_ct)) {
                            $total_qty += $row['SoLuong'];
                            // Tính toán dựa trên cột GiaNhap trong DB của bạn
                            $thanhtien = $row['SoLuong'] * $row['GiaNhap'];
                    ?>
                        <tr>
                            <td style="font-weight: 600; color: #888;">#SP<?= $row['MaSP'] ?></td>
                            <td style="font-weight: 700;"><?= htmlspecialchars($row['TenSP']) ?></td>
                            <td style="font-weight: 600;"><?= number_format($row['SoLuong']) ?></td>
                            <td><?= number_format($row['GiaNhap'], 0, ',', '.') ?>đ</td>
                            <td style="text-align: right; font-weight: 800;"><?= number_format($thanhtien, 0, ',', '.') ?>đ</td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:30px; color:#ccc;'>Không có dữ liệu chi tiết.</td></tr>";
                    }
                    ?>
                    <tr class="total-row">
                        <td colspan="2">TỔNG CỘNG</td>
                        <td><?= number_format($total_qty) ?></td>
                        <td></td>
                        <td style="text-align: right;"><?= number_format($phieu['TongTien'] ?? 0, 0, ',', '.') ?>đ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px; color: #aaa; font-size: 12px; font-style: italic; text-align: center;">
            Phiếu này được truy xuất từ bảng chitiet_phieunhap của hệ thống 5THEWAY®.
        </div>
    </main>

</body>
</html>