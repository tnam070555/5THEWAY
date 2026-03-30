<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: my_orders.php');
    exit();
}

$ma_hd = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Lấy thông tin đơn hàng
$sql_hd = "SELECT * FROM HOADON WHERE MaHD = '$ma_hd'";
$res_hd = mysqli_query($conn, $sql_hd);
$hd = mysqli_fetch_assoc($res_hd);

if (!$hd) {
    echo "Đơn hàng không tồn tại!";
    exit;
}

// 2. Lấy danh sách sản phẩm
$sql_ct = "SELECT ct.*, sp.TenSP, sp.HinhAnh 
           FROM CHITIETHOADON ct 
           JOIN SANPHAM sp ON ct.MaSP = sp.MaSP 
           WHERE ct.MaHD = '$ma_hd'";
$res_ct = mysqli_query($conn, $sql_ct);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CHI TIẾT ĐƠN HÀNG #<?php echo $ma_hd; ?> | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800;900&display=swap" rel="stylesheet">
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

        body { font-family: 'Inter', sans-serif; margin: 0; background-color: var(--bg-color); color: var(--text-main); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        
        /* Box chính giống một tờ hóa đơn */
        .container { max-width: 850px; margin: 40px auto; padding: 50px; background-color: var(--surface); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--border); }

        /* Header đơn hàng & Breadcrumb */
        .order-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 2px solid var(--primary); padding-bottom: 20px; }
        
        .breadcrumb { display: flex; flex-direction: column; gap: 8px; }
        .breadcrumb-link { font-size: 11px; color: var(--text-muted); text-decoration: none; font-weight: 700; text-transform: uppercase; transition: 0.2s; }
        .breadcrumb-link:hover { color: var(--primary); }
        
        .back-btn { text-decoration: none; color: var(--primary); font-weight: 900; font-size: 14px; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; transition: 0.2s; }
        .back-btn:hover { opacity: 0.6; gap: 12px; }

        .brand-hint { font-size: 16px; font-weight: 900; color: #e0e0e0; letter-spacing: 2px; }

        /* Tiêu đề & Trạng thái */
        .order-title-section { margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-start; }
        .order-title-section h1 { font-size: 36px; font-weight: 900; margin: 0 0 10px 0; letter-spacing: -1px; text-transform: uppercase; }
        .order-meta { color: var(--text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        
        /* Pill Trạng thái tự động đổi màu */
        .status-pill { padding: 6px 14px; border-radius: 4px; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; }
        .status-success { background: #e8f5e9; color: var(--success); }
        .status-danger { background: #ffebee; color: var(--danger); }
        .status-info { background: #e3f2fd; color: var(--info); }
        .status-warning { background: #fff4e5; color: var(--warning); }

        /* Grid thông tin vận chuyển */
        .info-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; background: #fafafa; padding: 30px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 50px; }
        .info-group h4 { font-size: 12px; font-weight: 900; text-transform: uppercase; margin: 0 0 15px 0; letter-spacing: 1px; color: var(--text-muted); border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .info-group p { font-size: 14px; margin: 6px 0; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .info-group p i { color: #aaa; width: 16px; text-align: center; }

        /* Danh sách sản phẩm */
        .product-list { margin-bottom: 50px; }
        .product-list-title { font-size: 14px; font-weight: 900; text-transform: uppercase; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary); }
        
        .product-item { display: grid; grid-template-columns: 80px 1fr 150px; gap: 20px; padding: 20px 0; border-bottom: 1px dashed var(--border); align-items: center; transition: 0.3s; }
        .product-item:hover { background: #fafafa; padding-left: 10px; padding-right: 10px; border-radius: 8px; border-bottom: 1px dashed transparent; }
        .product-img { width: 80px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); background: #f9f9f9; }
        .product-detail h3 { font-size: 15px; font-weight: 800; margin: 0 0 5px 0; text-transform: uppercase; }
        .product-detail p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500; }
        .product-price { text-align: right; font-weight: 900; font-size: 16px; color: var(--primary); }

        /* Tổng kết đơn hàng */
        .order-summary { width: 350px; margin-left: auto; padding-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; font-weight: 600; color: var(--text-muted); }
        .summary-row.total { margin-top: 20px; padding-top: 20px; border-top: 2px dashed var(--border); font-size: 24px; font-weight: 900; color: var(--danger); }
        .summary-row.total span:first-child { color: var(--primary); font-size: 16px; display: flex; align-items: center; }

        /* Footer trang trí */
        .footer-note { text-align: center; margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border); }
        .footer-note p { font-size: 11px; font-weight: 900; letter-spacing: 4px; color: #bbb; margin-bottom: 15px; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container { margin: 20px; padding: 30px 20px; }
            .info-grid { grid-template-columns: 1fr; gap: 30px; padding: 20px; }
            .order-title-section { flex-direction: column; gap: 15px; }
            .order-title-section h1 { font-size: 28px; }
            .product-item { grid-template-columns: 70px 1fr; }
            .product-price { text-align: left; grid-column: 2; font-size: 14px; margin-top: 5px; }
            .order-summary { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <nav class="order-nav">
        <div class="breadcrumb">
            <a href="profile.php" class="breadcrumb-link"><i class="fa-solid fa-user-circle"></i> Quay lại Tài khoản</a>
            <a href="my_orders.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Danh sách đơn hàng</a>
        </div>
        <span class="brand-hint">5THEWAY®</span>
    </nav>

    <div class="order-title-section">
        <div>
            <h1>Đơn hàng #<?php echo $hd['MaHD']; ?></h1>
            <div class="order-meta">
                <i class="fa-regular fa-calendar-days"></i> NGÀY ĐẶT: <?php echo date('d.m.Y - H:i', strtotime($hd['NgayLap'])); ?>
            </div>
        </div>
        
        <?php 
            // Xử lý logic màu sắc cho trạng thái
            $status_class = 'status-warning';
            $icon = 'fa-clock';
            if($hd['TrangThai'] == 'Đã nhận') { $status_class = 'status-success'; $icon = 'fa-check-circle'; }
            elseif($hd['TrangThai'] == 'Đã hủy') { $status_class = 'status-danger'; $icon = 'fa-times-circle'; }
            elseif($hd['TrangThai'] == 'Đã gửi hàng') { $status_class = 'status-info'; $icon = 'fa-truck-fast'; }
        ?>
        <div class="status-pill <?php echo $status_class; ?>">
            <i class="fa-solid <?php echo $icon; ?>"></i> <?php echo $hd['TrangThai']; ?>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-group">
            <h4>Thông tin giao hàng</h4>
            <p><i class="fa-solid fa-user"></i> <?php echo $hd['HoTen']; ?></p>
            <p><i class="fa-solid fa-phone"></i> <?php echo $hd['SDT']; ?></p>
            <p><i class="fa-solid fa-location-dot"></i> <?php echo $hd['DiaChi']; ?></p>
            <?php if(!empty($hd['GhiChu'])): ?>
                <p style="margin-top: 15px; color: var(--warning);"><i class="fa-solid fa-note-sticky"></i> "<?php echo $hd['GhiChu']; ?>"</p>
            <?php endif; ?>
        </div>
        <div class="info-group">
            <h4>Thanh toán</h4>
            <p><i class="fa-solid fa-wallet"></i> <?php echo $hd['PTThanhToan']; ?></p>
            <p><i class="fa-solid fa-box"></i> Phí ship: Miễn phí</p>
        </div>
    </div>

    <div class="product-list">
        <div class="product-list-title">Chi tiết sản phẩm</div>
        
        <?php while($item = mysqli_fetch_assoc($res_ct)): ?>
        <div class="product-item">
            <img src="assets/img/<?php echo $item['HinhAnh']; ?>" class="product-img" onerror="this.src='assets/img/default.jpg'">
            <div class="product-detail">
                <h3><?php echo $item['TenSP']; ?></h3>
                <p>Số lượng: x<?php echo $item['SoLuong']; ?></p>
                <p style="margin-top: 5px;">Đơn giá: <?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ</p>
            </div>
            <div class="product-price">
                <?php echo number_format($item['DonGia'] * $item['SoLuong'], 0, ',', '.'); ?>đ
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="order-summary">
        <div class="summary-row">
            <span>Tạm tính</span>
            <span><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</span>
        </div>
        <div class="summary-row">
            <span>Phí vận chuyển</span>
            <span style="color: var(--success); font-weight: 800;">MIỄN PHÍ</span>
        </div>
        <div class="summary-row total">
            <span>TỔNG CỘNG</span>
            <span><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</span>
        </div>
    </div>

    <div class="footer-note">
        <p>THANK YOU FOR SHOPPING WITH US</p>
        <img src="assets/img/logo.png" alt="5THEWAY" style="height: 24px; filter: grayscale(1); opacity: 0.2;">
    </div>
</div>

</body>
</html>