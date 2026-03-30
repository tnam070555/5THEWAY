<?php
include 'includes/db_connect.php';
session_start();

// Lấy trạng thái từ URL (mặc định là 'Chờ xử lý')
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'Chờ xử lý';

// Truy vấn danh sách đơn hàng theo Tab hiện tại
$sql = "SELECT hd.*, 
        (SELECT sp.HinhAnh FROM CHITIETHOADON ct JOIN SANPHAM sp ON ct.MaSP = sp.MaSP WHERE ct.MaHD = hd.MaHD LIMIT 1) as HinhAnhDauTien,
        (SELECT COUNT(*) FROM CHITIETHOADON WHERE MaHD = hd.MaHD) as SoLoaiSP
        FROM HOADON hd 
        WHERE hd.TrangThai = '$current_tab'
        ORDER BY hd.NgayLap DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>ĐƠN HÀNG CỦA TÔI | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #000;
            --bg-color: #f5f5f5;
            --surface: #fff;
            --border: #eaeaea;
            --text-main: #111;
            --text-muted: #717171;
            --success: #008a00;
            --danger: #e02b27;
            --warning: #e67e22;
            --info: #0056b3;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); margin: 0; color: var(--text-main); -webkit-font-smoothing: antialiased; }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        
        /* Navigation (Back to Profile) */
        .top-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .back-link { text-decoration: none; color: var(--primary); font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; transition: 0.2s ease; }
        .back-link:hover { opacity: 0.6; gap: 12px; }
        .brand-hint { font-size: 11px; font-weight: 900; color: #ccc; letter-spacing: 1px; }

        .page-title { font-weight: 900; text-transform: uppercase; font-size: 28px; margin: 0 0 30px 0; letter-spacing: -0.5px; }

        /* Tab Navigation - Chuẩn UI/UX hiện đại */
        .order-tabs { display: flex; background: var(--surface); margin-bottom: 25px; position: sticky; top: 0; z-index: 100; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; }
        .tab-item { flex: 1; text-align: center; padding: 18px 10px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); text-decoration: none; transition: all 0.3s ease; position: relative; }
        .tab-item:hover { color: var(--primary); background: #fafafa; }
        .tab-item.active { color: var(--primary); font-weight: 900; }
        .tab-item.active::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background-color: var(--primary); }

        /* Order Card - Thiết kế thẻ nổi (Elevated Card) */
        .order-card { background: var(--surface); margin-bottom: 20px; border: 1px solid var(--border); border-radius: 8px; padding: 25px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,0.04); border-color: #ddd; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); }
        .order-id { font-weight: 900; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Trạng thái đơn hàng có background nhẹ */
        .status-text { font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 4px 10px; border-radius: 4px; background: #fff4e5; color: var(--warning); display: flex; align-items: center; gap: 6px; }
        .status-received { background: #e8f5e9; color: var(--success); }
        .status-cancelled { background: #ffebee; color: var(--danger); }
        .status-shipping { background: #e3f2fd; color: var(--info); }

        /* Order Content */
        .order-content { display: flex; gap: 20px; align-items: flex-start; }
        .product-img { width: 90px; height: 110px; object-fit: cover; border: 1px solid var(--border); border-radius: 4px; background: #f9f9f9; }
        
        .order-info { flex: 1; }
        .order-info h3 { margin: 0 0 8px 0; font-size: 14px; font-weight: 800; text-transform: uppercase; color: var(--text-main); }
        .order-info p { margin: 4px 0; font-size: 13px; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .order-info p i { width: 14px; text-align: center; opacity: 0.7; }
        
        /* Order Footer */
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .total-box { display: flex; flex-direction: column; align-items: flex-start; }
        .total-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .total-price { font-size: 18px; font-weight: 900; color: var(--danger); }
        
        /* Nút chức năng */
        .btn-action { background: var(--primary); color: var(--surface); text-decoration: none; padding: 12px 28px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border-radius: 4px; transition: background 0.3s ease; }
        .btn-action:hover { background: #333; }
        
        /* Trạng thái trống */
        .empty-state { text-align: center; padding: 80px 20px; background: var(--surface); border: 1px dashed #ccc; border-radius: 8px; }
        .empty-state i { font-size: 48px; color: #ddd; margin-bottom: 20px; }
        .empty-state p { color: var(--text-muted); font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        /* Responsive */
        @media (max-width: 600px) {
            .order-tabs { overflow-x: auto; white-space: nowrap; border-radius: 0; }
            .tab-item { padding: 15px 20px; }
            .order-content { flex-direction: column; }
            .product-img { width: 100%; height: 200px; }
            .order-footer { flex-direction: column; gap: 15px; align-items: flex-start; }
            .btn-action { width: 100%; text-align: center; box-sizing: border-box; }
        }
    </style>
</head>
<body>

<div class="container">
    <nav class="top-nav">
        <a href="profile.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Quay lại tài khoản
        </a>
        <span class="brand-hint">5THEWAY®</span>
    </nav>

    <h1 class="page-title">Đơn hàng của tôi</h1>

    <div class="order-tabs">
        <a href="?tab=Chờ xử lý" class="tab-item <?php echo ($current_tab == 'Chờ xử lý') ? 'active' : ''; ?>">Chờ xử lý</a>
        <a href="?tab=Đã xác nhận" class="tab-item <?php echo ($current_tab == 'Đã xác nhận') ? 'active' : ''; ?>">Đã xác nhận</a>
        <a href="?tab=Đã gửi hàng" class="tab-item <?php echo ($current_tab == 'Đã gửi hàng') ? 'active' : ''; ?>">Đã gửi hàng</a>
        <a href="?tab=Đã nhận" class="tab-item <?php echo ($current_tab == 'Đã nhận') ? 'active' : ''; ?>">Đã nhận</a>
        <a href="?tab=Đã hủy" class="tab-item <?php echo ($current_tab == 'Đã hủy') ? 'active' : ''; ?>">Đã hủy</a>
    </div>

    <div class="order-list">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Đơn hàng #<?php echo $row['MaHD']; ?></span>
                        <span class="status-text 
                            <?php 
                                if($row['TrangThai'] == 'Đã nhận') echo 'status-received';
                                if($row['TrangThai'] == 'Đã hủy') echo 'status-cancelled';
                                if($row['TrangThai'] == 'Đã gửi hàng') echo 'status-shipping';
                            ?>">
                            <i class="fa-solid <?php 
                                echo ($row['TrangThai'] == 'Đã nhận') ? 'fa-check-circle' : 
                                    (($row['TrangThai'] == 'Đã hủy') ? 'fa-times-circle' : 'fa-truck');
                            ?>"></i> 
                            <?php echo $row['TrangThai']; ?>
                        </span>
                    </div>

                    <div class="order-content">
                        <img src="assets/img/<?php echo $row['HinhAnhDauTien']; ?>" class="product-img" onerror="this.src='assets/img/default.jpg'">
                        <div class="order-info">
                            <h3>Ngày đặt: <?php echo date('d/m/Y', strtotime($row['NgayLap'])); ?></h3>
                            <p><i class="fa-solid fa-user"></i> Người nhận: <?php echo $row['HoTen']; ?></p>
                            <p><i class="fa-solid fa-box-open"></i> Số lượng: <?php echo $row['SoLoaiSP']; ?> sản phẩm</p>
                            <p><i class="fa-solid fa-credit-card"></i> Thanh toán: <?php echo $row['PTThanhToan']; ?></p>
                        </div>
                    </div>

                    <div class="order-footer">
                        <div class="total-box">
                            <span class="total-label">Tổng thanh toán</span>
                            <span class="total-price"><?php echo number_format($row['TongTien'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <a href="orders_detail.php?id=<?php echo $row['MaHD']; ?>" class="btn-action">Xem chi tiết</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-receipt"></i>
                <p>Bạn chưa có đơn hàng nào trong mục này.</p>
                <a href="index.php" class="btn-action">Mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>