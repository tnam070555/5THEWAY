<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. THỐNG KÊ TỔNG QUAN
// Tổng doanh thu
$res_rev = @mysqli_query($conn, "SELECT SUM(TongTien) as total FROM HOADON WHERE TrangThai = 'Hoàn thành'");
$total_revenue = ($res_rev) ? mysqli_fetch_assoc($res_rev)['total'] : 0;

// Đơn hàng chờ xử lý
$res_pend = @mysqli_query($conn, "SELECT COUNT(*) as total FROM HOADON WHERE TrangThai = 'Chờ xử lý'");
$count_pending = ($res_pend) ? mysqli_fetch_assoc($res_pend)['total'] : 0;

// Tổng số khách hàng
$res_cust = @mysqli_query($conn, "SELECT COUNT(*) as total FROM KHACHHANG");
$count_cust = ($res_cust) ? mysqli_fetch_assoc($res_cust)['total'] : 0;

// 3. DỮ LIỆU BIỂU ĐỒ DOANH THU 7 NGÀY QUA
$days = [];
$revenues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('d/m', strtotime($date));
    $sql_day = "SELECT SUM(TongTien) as daily_total FROM HOADON WHERE DATE(NgayLap) = '$date' AND TrangThai = 'Hoàn thành'";
    $res_day = mysqli_query($conn, $sql_day);
    $revenues[] = ($res_day) ? (mysqli_fetch_assoc($res_day)['daily_total'] ?? 0) : 0;
}

// 4. CHỨC NĂNG MỚI: TOP 5 SẢN PHẨM BÁN CHẠY NHẤT
// Truy vấn này chỉ lấy TenSP và số lượng bán để tránh lỗi sai tên cột giá tiền (GiaBan/DonGia)
$sql_top_sp = "SELECT sp.TenSP, SUM(ct.SoLuong) as TongBan 
               FROM CHITIETHOADON ct 
               JOIN SANPHAM sp ON ct.MaSP = sp.MaSP 
               JOIN HOADON hd ON ct.MaHD = hd.MaHD
               WHERE hd.TrangThai = 'Hoàn thành'
               GROUP BY sp.MaSP 
               ORDER BY TongBan DESC 
               LIMIT 5";
$res_top = mysqli_query($conn, $sql_top_sp);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BÁO CÁO THỐNG KÊ | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand h2 { font-weight: 900; text-align: center; margin-bottom: 40px; letter-spacing: 2px; font-size: 24px; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; margin-bottom: 8px; transition: 0.3s; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { margin-top: auto; color: #ff4d4d !important; }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .top-header h1 { font-weight: 900; text-transform: uppercase; font-size: 24px; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }

        /* Thẻ thống kê */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #eee; display: flex; align-items: center; gap: 20px; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .stat-icon { width: 55px; height: 55px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .icon-blue { background: #e3f2fd; color: #1976d2; }
        .icon-orange { background: #fff3e0; color: #f57c00; }
        .icon-green { background: #e8f5e9; color: #2e7d32; }
        .stat-info p { font-size: 11px; color: #888; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .stat-info h3 { font-size: 22px; font-weight: 900; color: #000; }

        /* Layout Biểu đồ & Bảng */
        .report-layout { display: grid; grid-template-columns: 1.6fr 1fr; gap: 30px; }
        .card { background: #fff; padding: 30px; border-radius: 20px; border: 1px solid #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .card h3 { font-weight: 900; text-transform: uppercase; font-size: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: #333; }

        /* Bảng sản phẩm bán chạy */
        .top-table { width: 100%; border-collapse: collapse; }
        .top-table th { text-align: left; font-size: 10px; color: #bbb; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #f8f9fa; }
        .top-table td { padding: 15px 10px; border-bottom: 1px solid #f8f9fa; font-size: 14px; font-weight: 600; vertical-align: middle; }
        .rank-badge { width: 28px; height: 28px; background: #f0f0f0; color: #000; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 12px; font-weight: 900; margin-right: 12px; }
        tr:nth-child(1) .rank-badge { background: #000; color: #fff; } /* Hạng 1 màu đen */
        .qty-sold { background: #fff0f0; color: #ff3b30; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 12px; }
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
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Phân tích dữ liệu</h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?= htmlspecialchars($admin_name) ?></span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="stat-info">
                    <p>Tổng doanh thu</p>
                    <h3><?= number_format($total_revenue, 0, ',', '.') ?>đ</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-orange"><i class="fa-solid fa-spinner"></i></div>
                <div class="stat-info">
                    <p>Đơn đang xử lý</p>
                    <h3><?= $count_pending ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="fa-solid fa-user-check"></i></div>
                <div class="stat-info">
                    <p>Khách hàng mới</p>
                    <h3><?= $count_cust ?></h3>
                </div>
            </div>
        </div>

        <div class="report-layout">
            <div class="card">
                <h3><i class="fa-solid fa-chart-line"></i> Doanh thu 7 ngày gần nhất</h3>
                <div style="height: 300px; position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-crown" style="color: #ffcc00;"></i> Best Sellers</h3>
                <table class="top-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style="text-align: right;">Đã bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        if($res_top && mysqli_num_rows($res_top) > 0):
                            while($row = mysqli_fetch_assoc($res_top)): 
                        ?>
                        <tr>
                            <td>
                                <span class="rank-badge"><?= $rank++ ?></span>
                                <span style="color: #000;"><?= htmlspecialchars($row['TenSP']) ?></span>
                            </td>
                            <td style="text-align: right;">
                                <span class="qty-sold"><?= $row['TongBan'] ?> SP</span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="2" style="text-align: center; color: #ccc; padding: 40px;">Chưa có dữ liệu giao dịch.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Tạo gradient cho biểu đồ
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(0, 0, 0, 0.1)');
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($days) ?>,
                datasets: [{
                    label: 'Doanh thu',
                    data: <?= json_encode($revenues) ?>,
                    borderColor: '#000',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#000',
                    pointBorderWidth: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [5, 5] },
                        ticks: { 
                            callback: v => v.toLocaleString() + 'đ',
                            font: { size: 11 }
                        } 
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>