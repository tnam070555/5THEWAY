<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) { header('Location: ../login.php'); exit(); }

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// --- CHỨC NĂNG MỚI: XỬ LÝ BỘ LỌC ---
$filter = isset($_GET['view']) ? $_GET['view'] : 'all';

if ($filter == 'old_stock') {
    // Lấy sản phẩm có ngày cập nhật cách đây hơn 30 ngày và vẫn còn tồn kho
    $sql = "SELECT * FROM SANPHAM WHERE SoLuongTon > 0 AND NgayCapNhat <= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY NgayCapNhat ASC";
    $page_title = "Sản phẩm tồn kho lâu ngày (>30 ngày)";
} else {
    $sql = "SELECT * FROM SANPHAM ORDER BY SoLuongTon ASC";
    $page_title = "Quản lý tồn kho";
}

$res = @mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* GIỮ NGUYÊN CSS GỐC VÀ THÊM STYLE CHO TAB */
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

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-header h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }

        .filter-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; transition: 0.3s; background: #fff; color: #666; border: 1px solid #eee; }
        .tab-btn.active { background: var(--primary-black); color: #fff; border-color: var(--primary-black); }

        .inventory-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 30px; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #bbb; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f9f9f9; }
        td { padding: 20px 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }

        .status-badge { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .stock-ok { background: #e8f5e9; color: #2e7d32; }
        .stock-low { background: #fff3e0; color: #f57c00; }
        .stock-out { background: #ffebee; color: #c62828; }
        
        .btn-update { color: #000; font-weight: 800; font-size: 12px; text-decoration: none; border-bottom: 2px solid #000; padding-bottom: 2px; }
        .btn-update:hover { opacity: 0.6; }
        
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
            <li><a href="inventory.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1><?= $page_title ?></h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="filter-tabs">
            <a href="inventory.php?view=all" class="tab-btn <?= $filter == 'all' ? 'active' : '' ?>">TẤT CẢ TỒN KHO</a>
            <a href="inventory.php?view=old_stock" class="tab-btn <?= $filter == 'old_stock' ? 'active' : '' ?>">
                <i class="fa-solid fa-hourglass-half"></i> TỒN KHO LÂU NGÀY
            </a>
        </div>

        <div class="inventory-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Size</th>
                        <th>Số lượng tồn</th>
                        <?php if($filter == 'old_stock'): ?><th>Ngày cập nhật cuối</th><?php endif; ?>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($res && mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) {
                            $sl = $row['SoLuongTon'];
                            if ($sl == 0) { $st_class = 'stock-out'; $st_text = 'Hết hàng'; }
                            elseif ($sl <= 5) { $st_class = 'stock-low'; $st_text = 'Sắp hết'; }
                            else { $st_class = 'stock-ok'; $st_text = 'Còn hàng'; }
                    ?>
                    <tr>
                        <td style="font-weight:700; color:#999;">#<?= $row['MaSP'] ?></td>
                        <td style="font-weight:700; color:#000;"><?= htmlspecialchars($row['TenSP']) ?></td>
                        <td><strong><?= $row['Size'] ?></strong></td>
                        <td style="font-size: 16px; font-weight: 900;"><?= $sl ?></td>
                        
                        <?php if($filter == 'old_stock'): ?>
                        <td style="color: #ff3b30; font-weight: 600;">
                            <?= date('d/m/Y', strtotime($row['NgayCapNhat'])) ?>
                        </td>
                        <?php endif; ?>

                        <td><span class="status-badge <?= $st_class ?>"><?= $st_text ?></span></td>
                        <td><a href="edit_product.php?id=<?= $row['MaSP'] ?>" class="btn-update">CẬP NHẬT</a></td>
                    </tr>
                    <?php 
                        } 
                    } else { 
                        echo "<tr><td colspan='7' style='text-align:center; padding: 80px; color: #ccc; font-weight: 700;'>KHÔNG CÓ DỮ LIỆU PHÙ HỢP.</td></tr>"; 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>