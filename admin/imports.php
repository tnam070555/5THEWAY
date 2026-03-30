<?php
session_start();
include_once('../includes/db_connect.php');

// 1. Kiểm tra quyền
if (!isset($_SESSION['user_admin'])) { 
    header('Location: ../login.php'); 
    exit(); 
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. Chức năng mới: Truy vấn lịch sử nhập hàng từ Database
// JOIN với bảng NHANVIEN để biết ai là người nhập (nếu cần)
$sql = "SELECT pn.*, nv.HoTen as TenNhanVien 
        FROM PHIEUNHAP pn 
        LEFT JOIN NHANVIEN nv ON pn.MaNV = nv.MaNV 
        ORDER BY pn.NgayNhap DESC";

$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>QUẢN LÝ NHẬP HÀNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* GIỮ NGUYÊN CSS GỐC CỦA BẠN */
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f4f7f6; --accent: #ff3b30; }
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

        .main { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }

        .btn-primary { background: var(--primary-black); color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; transition: 0.3s; }
        .btn-primary:hover { opacity: 0.8; transform: translateY(-2px); }

        .card-table { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 30px; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #bbb; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f9f9f9; }
        td { padding: 20px 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }

        .badge-import { background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }
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
        <div class="header-flex">
            <div>
                <h1>Lịch sử nhập hàng</h1>
                <p style="color: #999; font-size: 13px; margin-top: 5px;">Theo dõi các đợt nhập hàng hóa vào kho</p>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="user-pill">
                    <i class="fa-solid fa-circle-user"></i>
                    <span><?= htmlspecialchars($admin_name) ?></span>
                </div>
                <a href="add_import.php" class="btn-primary">+ TẠO PHIẾU NHẬP</a>
            </div>
        </div>

        <div class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Ngày Nhập</th>
                        <th>Nhà Cung Cấp</th>
                        <th>Người Nhập</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($res && mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                    ?>
                        <tr>
                            <td style="font-weight: 800;">#PN<?= str_pad($row['MaPN'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['NgayNhap'])) ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['NhaCungCap']) ?></td>
                            <td><?= htmlspecialchars($row['TenNhanVien'] ?? 'Hệ thống') ?></td>
                            <td style="font-weight: 800; color: #000;"><?= number_format($row['TongTien'], 0, ',', '.') ?>đ</td>
                            <td><span class="badge-import">Đã nhập kho</span></td>
                            <td style="text-align: right;">
                                <a href="import_detail.php?id=<?= $row['MaPN'] ?>" style="color: #000; font-weight: 700; text-decoration: none; border-bottom: 2px solid #000;">CHI TIẾT</a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 50px; color: #ccc;'>Chưa có lịch sử nhập hàng nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>