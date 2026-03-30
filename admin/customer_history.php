<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) { header('Location: ../login.php'); exit(); }
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

if (!isset($_GET['id'])) { header('Location: manage_customers.php'); exit(); }
$makh = mysqli_real_escape_string($conn, $_GET['id']);

// Lấy thông tin khách hàng - Sửa tên cột theo DB của bạn (HangThe)
$res_kh = mysqli_query($conn, "SELECT * FROM KHACHHANG WHERE MaKH = '$makh'");
$kh = mysqli_fetch_assoc($res_kh);

// Lấy lịch sử mua hàng
$sql_history = "SELECT * FROM HOADON WHERE MaKH = '$makh' ORDER BY NgayLap DESC";
$res_history = mysqli_query($conn, $sql_history);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>LỊCH SỬ KHÁCH HÀNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-width: 260px; 
            --primary-black: #000; 
            --bg-light: #f8f9fa; 
            --accent: #ff3b30; 
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { 
            display: flex; 
            background: var(--bg-light); 
            min-height: 100vh; 
            overflow-x: hidden;
        }

        /* --- SIDEBAR STYLE (Cố định bên trái) --- */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--primary-black); 
            color: #fff; 
            position: fixed; 
            height: 100vh; 
            padding: 30px 20px; 
            display: flex; 
            flex-direction: column; 
            z-index: 100; 
        }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; }
        
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 8px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { background: #222; color: #ff4d4d !important; margin-top: auto; }

        /* --- MAIN CONTENT (Phải có margin-left để không bị đè) --- */
        .main-content { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            padding: 40px; 
            width: calc(100% - var(--sidebar-width)); 
            min-width: 0; /* Tránh tràn bảng */
        }

        .header-box { margin-bottom: 30px; }
        .header-box h1 { font-weight: 900; font-size: 28px; }

        /* Customer Card */
        .customer-info-card { 
            background: #fff; 
            padding: 25px; 
            border-radius: 15px; 
            display: flex; 
            flex-wrap: wrap; /* Tự xuống dòng trên màn hình nhỏ */
            gap: 40px; 
            margin-bottom: 30px; 
            border: 1px solid #eee; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
        }
        .info-item label { display: block; font-size: 11px; font-weight: 800; color: #bbb; text-transform: uppercase; margin-bottom: 5px; }
        .info-item span { font-weight: 700; color: #000; font-size: 16px; }

        /* Data Table Box */
        .data-box { 
            background: #fff; 
            padding: 25px; 
            border-radius: 15px; 
            border: 1px solid #eee; 
            overflow-x: auto; /* Tạo thanh cuộn nếu bảng quá rộng */
        }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th { text-align: left; color: #aaa; font-size: 11px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #f8f9fa; }
        td { padding: 18px 15px; border-bottom: 1px solid #f8f9fa; font-size: 14px; }
        
        .badge-status { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .status-done { background: #e8f5e9; color: #2e7d32; }
        
        .btn-view { 
            background: #000; 
            color: #fff; 
            padding: 8px 16px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: 800; 
            transition: 0.2s;
        }
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
            <li><a href="manage_orders.php" class="nav-link"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link active"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header-box">
            <h1 style="text-transform: uppercase;">Lịch sử khách hàng</h1>
            <p style="color: #999;">Quản lý và theo dõi hành vi giao dịch</p>
        </div>

        <div class="customer-info-card">
            <div class="info-item">
                <label>Khách hàng</label>
                <span><?= htmlspecialchars($kh['HoTen'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <label>Số điện thoại</label>
                <span><?= $kh['SDT'] ?? 'N/A' ?></span>
            </div>
            <div class="info-item">
                <label>Tích điểm</label>
                <span style="color: var(--accent);"><?= number_format($kh['DiemTichLuy'] ?? 0) ?> điểm</span>
            </div>
            <div class="info-item">
                <label>Hạng thẻ</label>
                <span style="background:#000; color:#fff; padding:3px 12px; border-radius:4px; font-size:11px; font-weight:800;">
                    <?= $kh['HangThe'] ?? 'MEMBER' ?>
                </span>
            </div>
        </div>

        <div class="data-box">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày lập</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res_history && mysqli_num_rows($res_history) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td><strong>#<?= $row['MaHD'] ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['NgayLap'])) ?></td>
                            <td style="font-weight: 800;"><?= number_format($row['TongTien'], 0, ',', '.') ?>đ</td>
                            <td><span class="badge-status status-done"><?= $row['TrangThai'] ?></span></td>
                            <td style="text-align: right;">
                                <a href="order_detail.php?id=<?= $row['MaHD'] ?>" class="btn-view">CHI TIẾT</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 60px; color: #ccc; font-weight:600;">Khách hàng này chưa phát sinh giao dịch nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>