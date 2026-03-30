<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

function countTable($conn, $tableName, $condition = "") {
    $sql = "SELECT COUNT(*) as total FROM $tableName $condition";
    $result = @mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

$count_sp = countTable($conn, "SANPHAM");
$count_hd = countTable($conn, "HOADON", "WHERE TrangThai = 'Chờ xử lý'");
$count_kh = countTable($conn, "KHACHHANG");
$count_nv = countTable($conn, "NHANVIEN");

$total_revenue = 0;
$res_rev = @mysqli_query($conn, "SELECT SUM(TongTien) as revenue FROM HOADON WHERE TrangThai = 'Hoàn thành'");
if($res_rev) {
    $row_rev = mysqli_fetch_assoc($res_rev);
    $total_revenue = $row_rev['revenue'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HỆ THỐNG QUẢN TRỊ | 5THEWAY®</title>
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
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .top-header h1 { font-weight: 900; font-size: 22px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .grid-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; }
        .stat-card p { font-size: 12px; color: #888; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card h2 { font-size: 30px; font-weight: 900; }
        .trend { font-size: 12px; margin-top: 10px; color: #16a34a; font-weight: 700; }
        .data-box { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; }
        .data-box h3 { margin-bottom: 25px; font-weight: 900; font-size: 16px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #aaa; font-size: 11px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #f8f9fa; }
        td { padding: 18px 15px; border-bottom: 1px solid #f8f9fa; font-size: 14px; font-weight: 500; vertical-align: middle; }
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; background: #fff3e0; color: #f57c00; text-transform: uppercase; }
        .btn-action { color: var(--primary-black); text-decoration: none; font-weight: 800; font-size: 12px; border-bottom: 2px solid var(--primary-black); }
        .order-products { font-size: 11px; color: #777; margin-top: 5px; font-weight: 400; line-height: 1.4; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link"><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
            <li><a href="manage_orders.php" class="nav-link"><i class="fa-solid fa-receipt"></i> Hóa đơn</a></li>
            <li><a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Khách hàng</a></li>
            <li><a href="manage_staff.php" class="nav-link"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Bảng điều khiển</h1>
            <div class="user-pill"><i class="fa-solid fa-circle-user"></i><span><?php echo htmlspecialchars($admin_name); ?></span></div>
        </div>

        <div class="grid-stats">
            <div class="stat-card"><p>Sản phẩm</p><h2><?php echo number_format($count_sp); ?></h2><div class="trend">Mặt hàng hiện có</div></div>
            <div class="stat-card"><p>Đơn chờ duyệt</p><h2 style="color: var(--accent);"><?php echo number_format($count_hd); ?></h2><div class="trend" style="color: #f57c00;">Cần xử lý ngay</div></div>
            <div class="stat-card"><p>Khách hàng</p><h2><?php echo number_format($count_kh); ?></h2><div class="trend">Thành viên đăng ký</div></div>
            <div class="stat-card"><p>Doanh thu</p><h2><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</h2><div class="trend">Tổng đơn hoàn thành</div></div>
        </div>

        <div class="data-box">
            <h3>Đơn hàng mới nhất & Sản phẩm</h3>
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Sản phẩm trong đơn</th>
                        <th>Ngày lập</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // SQL FIX: Bỏ JOIN với KHACHHANG nếu không chắc chắn tên cột, chỉ tập trung vào Sản phẩm
                    $sql_hd = "SELECT 
                                    hd.MaHD, hd.NgayLap, hd.TongTien, hd.TrangThai, 
                                    GROUP_CONCAT(sp.TenSP SEPARATOR ', ') AS DanhSachSP
                               FROM HOADON hd
                               LEFT JOIN CHITIETHOADON ct ON hd.MaHD = ct.MaHD
                               LEFT JOIN SANPHAM sp ON ct.MaSP = sp.MaSP
                               GROUP BY hd.MaHD
                               ORDER BY hd.NgayLap DESC 
                               LIMIT 5";

                    $res_hd = @mysqli_query($conn, $sql_hd);

                    if ($res_hd && mysqli_num_rows($res_hd) > 0) {
                        while($row = mysqli_fetch_assoc($res_hd)) {
                            $sp_list = !empty($row['DanhSachSP']) ? $row['DanhSachSP'] : "Không có dữ liệu SP";
                            if(strlen($sp_list) > 60) $sp_list = mb_substr($sp_list, 0, 57) . "...";
                    ?>
                        <tr>
                            <td><strong>#<?php echo $row['MaHD']; ?></strong></td>
                            <td>
                                <div class="order-products">
                                    <i class="fa-solid fa-cart-shopping"></i> <?php echo htmlspecialchars($sp_list); ?>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['NgayLap'])); ?></td>
                            <td><strong><?php echo number_format($row['TongTien'], 0, ',', '.'); ?>đ</strong></td>
                            <td><span class="badge"><?php echo $row['TrangThai']; ?></span></td>
                            <td><a href="manage_orders.php?id=<?php echo $row['MaHD']; ?>" class="btn-action">CHI TIẾT</a></td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 40px;'>Chưa có dữ liệu hóa đơn.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>