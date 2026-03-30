<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();    
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. LẤY MÃ HÓA ĐƠN
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_orders.php');
    exit();
}

$mahd = mysqli_real_escape_string($conn, $_GET['id']);

// 3. XỬ LÝ CẬP NHẬT TRẠNG THÁI & HỦY ĐƠN
$update_msg = "";
if (isset($_POST['btn_update_status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    $reason = mysqli_real_escape_string($conn, $_POST['ly_do_huy'] ?? '');

    // Lưu ý: Nếu DB của bạn chưa có cột LyDoHuy, hãy chạy câu lệnh SQL sau trong phpMyAdmin:
    // ALTER TABLE HOADON ADD COLUMN LyDoHuy TEXT NULL;
    
    $sql_update = "UPDATE HOADON SET TrangThai = '$status', LyDoHuy = '$reason' WHERE MaHD = '$mahd'";
    
    if (mysqli_query($conn, $sql_update)) {
        $update_msg = "Cập nhật trạng thái thành công!";
    } else {
        $update_msg = "Lỗi: " . mysqli_error($conn);
    }
}

// 4. TRUY VẤN THÔNG TIN HÓA ĐƠN CHI TIẾT
$sql_hd = "SELECT * FROM HOADON WHERE MaHD = '$mahd'";
$res_hd = mysqli_query($conn, $sql_hd);
$order = mysqli_fetch_assoc($res_hd);

if (!$order) {
    die("<h2 style='text-align:center; padding-top:50px; font-family:Inter;'>Hóa đơn này không tồn tại!</h2>");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHI TIẾT #<?php echo $mahd; ?> | 5THEWAY®</title>
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
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; width: calc(100% - var(--sidebar-width)); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 { font-weight: 900; font-size: 26px; text-transform: uppercase; }
        
        .btn-group-top { display: flex; gap: 10px; }
        .btn-top { padding: 10px 20px; border-radius: 8px; font-weight: 800; text-decoration: none; cursor: pointer; border: none; font-size: 13px; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-print { background: #fff; color: #000; border: 2px solid #000; }
        .btn-back { background: #eee; color: #666; }
        .btn-top:hover { opacity: 0.7; }

        /* Order Details Layout */
        .order-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #eee; margin-bottom: 25px; }
        .card-title { font-weight: 800; font-size: 14px; margin-bottom: 20px; text-transform: uppercase; color: #999; display: flex; align-items: center; gap: 10px; }

        /* Table Products */
        .table-products { width: 100%; border-collapse: collapse; }
        .table-products th { text-align: left; padding: 12px; font-size: 11px; text-transform: uppercase; color: #bbb; border-bottom: 1px solid #f5f5f5; }
        .table-products td { padding: 15px 12px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .total-row { font-size: 18px; font-weight: 900; text-align: right; margin-top: 20px; border-top: 2px solid #eee; padding-top: 20px; }

        /* Form Update Status */
        .status-badge { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .status-waiting { background: #fff3e0; color: #ef6c00; } /* Chờ duyệt */
        .status-shipping { background: #e3f2fd; color: #1565c0; } /* Đang giao */
        .status-success { background: #e8f5e9; color: #2e7d32; } /* Thành công */
        .status-cancel { background: #ffebee; color: #c62828; } /* Đã hủy */

        .form-control { width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 8px; background: #fafafa; font-size: 14px; margin-bottom: 15px; }
        .btn-save { background: #000; color: #fff; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; }
        
        #cancel_reason_area { display: none; }

        /* Print Style */
        @media print {
            .sidebar, .btn-group-top, .status-form-card { display: none !important; }
            .main-content { margin-left: 0; padding: 0; width: 100%; }
            .card { box-shadow: none; border: 1px solid #000; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link "><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            <li><a href="manage_orders.php" class="nav-link active"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header-flex">
            <div>
                <p style="color: #999; font-weight: 600; font-size: 13px;">Chi tiết hóa đơn</p>
                <h1>Mã đơn: #<?php echo $order['MaHD']; ?></h1>
            </div>
            <div class="btn-group-top">
                <a href="manage_orders.php" class="btn-top btn-back"><i class="fa-solid fa-arrow-left"></i> QUAY LẠI</a>
                <button onclick="window.print()" class="btn-top btn-print"><i class="fa-solid fa-print"></i> IN HÓA ĐƠN</button>
            </div>
        </div>

        <?php if($update_msg != ""): ?>
            <div style="background: #e6f7ff; color: #1890ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; border: 1px solid #91d5ff;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $update_msg; ?>
            </div>
        <?php endif; ?>

        <div class="order-grid">
            <div class="left-col">
                <div class="card">
                    <h3 class="card-title"><i class="fa-solid fa-box-open"></i> Sản phẩm đã đặt</h3>
                    <table class="table-products">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th style="text-align: right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_ct = "SELECT ct.*, sp.TenSP FROM CHITIETHOADON ct 
                                       JOIN SANPHAM sp ON ct.MaSP = sp.MaSP 
                                       WHERE ct.MaHD = '$mahd'";
                            $res_ct = mysqli_query($conn, $sql_ct);
                            while($item = mysqli_fetch_assoc($res_ct)) {
                                $thanhtien = $item['DonGia'] * $item['SoLuong'];
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #000;"><?php echo htmlspecialchars($item['TenSP']); ?></div>
                                    <div style="font-size: 11px; color: #999;">Mã SP: #<?php echo $item['MaSP']; ?></div>
                                </td>
                                <td><?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ</td>
                                <td>x<?php echo $item['SoLuong']; ?></td>
                                <td style="text-align: right; font-weight: 800;"><?php echo number_format($thanhtien, 0, ',', '.'); ?>đ</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="total-row">
                        <span style="font-size: 14px; color: #999; font-weight: 400; margin-right: 20px;">TỔNG THANH TOÁN:</span>
                        <?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> Ghi chú đơn hàng</h3>
                    <p style="font-size: 14px; color: #666; line-height: 1.6;">
                        <?php echo !empty($order['GhiChu']) ? htmlspecialchars($order['GhiChu']) : "Không có ghi chú từ khách hàng."; ?>
                    </p>
                    <?php if(!empty($order['LyDoHuy'])): ?>
                        <div style="margin-top: 15px; padding: 15px; background: #fff1f0; border-left: 4px solid #ff4d4f; border-radius: 4px;">
                            <strong style="color: #cf1322; font-size: 13px;">LÝ DO HỦY ĐƠN:</strong><br>
                            <span style="font-size: 14px;"><?php echo htmlspecialchars($order['LyDoHuy']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="right-col">
                <div class="card status-form-card">
                    <h3 class="card-title"><i class="fa-solid fa-rotate"></i> Cập nhật trạng thái</h3>
                    <form method="POST">
                        <label style="font-size: 11px; font-weight: 800; color: #bbb; display: block; margin-bottom: 8px;">TRẠNG THÁI HIỆN TẠI</label>
                        <select name="trang_thai" class="form-control" id="statusSelect" onchange="toggleReason()">
                            <option value="Chờ duyệt" <?php if($order['TrangThai'] == 'Chờ duyệt') echo 'selected'; ?>>Chờ duyệt (Mới)</option>
                            <option value="Đang giao" <?php if($order['TrangThai'] == 'Đang giao') echo 'selected'; ?>>Đang giao</option>
                            <option value="Thành công" <?php if($order['TrangThai'] == 'Thành công') echo 'selected'; ?>>Giao thành công</option>
                            <option value="Đã hủy" <?php if($order['TrangThai'] == 'Đã hủy') echo 'selected'; ?>>Hủy đơn hàng</option>
                        </select>

                        <div id="cancel_reason_area">
                            <label style="font-size: 11px; font-weight: 800; color: #ff3b30; display: block; margin-bottom: 8px;">LÝ DO HỦY</label>
                            <textarea name="ly_do_huy" class="form-control" placeholder="Nhập lý do tại sao hủy đơn..." style="height: 100px; resize: none;"><?php echo $order['LyDoHuy'] ?? ''; ?></textarea>
                        </div>

                        <button type="submit" name="btn_update_status" class="btn-save">LƯU THAY ĐỔI</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title"><i class="fa-solid fa-user-tag"></i> Thông tin giao hàng</h3>
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 11px; color: #bbb; font-weight: 800; text-transform: uppercase;">Người nhận</div>
                        <div style="font-weight: 700; font-size: 15px;"><?php echo htmlspecialchars($order['HoTen']); ?></div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 11px; color: #bbb; font-weight: 800; text-transform: uppercase;">Số điện thoại</div>
                        <div style="font-weight: 700;"><?php echo $order['SDT']; ?></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #bbb; font-weight: 800; text-transform: uppercase;">Địa chỉ</div>
                        <div style="font-weight: 600; font-size: 14px; line-height: 1.5;"><?php echo htmlspecialchars($order['DiaChi']); ?></div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title"><i class="fa-solid fa-credit-card"></i> Thanh toán</h3>
                    <p style="font-weight: 700;"><?php echo $order['PTThanhToan']; ?></p>
                    <p style="font-size: 12px; color: #999; margin-top: 5px;">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayLap'])); ?></p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleReason() {
            var select = document.getElementById("statusSelect");
            var reasonArea = document.getElementById("cancel_reason_area");
            if (select.value === "Đã hủy") {
                reasonArea.style.display = "block";
            } else {
                reasonArea.style.display = "none";
            }
        }
        // Gọi lúc load trang để kiểm tra nếu đơn đã hủy sẵn
        window.onload = toggleReason;
    </script>

</body>
</html>