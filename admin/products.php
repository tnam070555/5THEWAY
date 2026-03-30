<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. XỬ LÝ XÓA SẢN PHẨM
if (isset($_GET['delete_id'])) {
    $id_del = mysqli_real_escape_string($conn, $_GET['delete_id']);
    // Xóa sản phẩm khỏi database
    $sql_del = "DELETE FROM SANPHAM WHERE MaSP = '$id_del'";
    if (mysqli_query($conn, $sql_del)) {
        header("Location: products.php?status=deleted");
        exit();
    }
}

// 3. XỬ LÝ TÌM KIẾM VÀ LỌC
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$where_clause = " WHERE 1=1 ";
if ($search != '') {
    $where_clause .= " AND (sp.TenSP LIKE '%$search%' OR sp.MaSP LIKE '%$search%') ";
}
if ($category_filter != '') {
    $where_clause .= " AND sp.MaLoai = '$category_filter' ";
}

$sql = "SELECT sp.*, dm.TenLoai 
        FROM SANPHAM sp 
        LEFT JOIN DANHMUC dm ON sp.MaLoai = dm.MaLoai 
        $where_clause 
        ORDER BY sp.MaSP DESC";
$res = mysqli_query($conn, $sql);

// Lấy danh sách danh mục cho bộ lọc
$sql_categories = "SELECT * FROM DANHMUC";
$res_categories = mysqli_query($conn, $sql_categories);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ SẢN PHẨM | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- ĐỒNG NHẤT CSS ROOT VỚI CÁC TRANG KHÁC --- */
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; --accent: #ff3b30; }
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

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .top-header h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }

        /* --- TOOLBAR --- */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .filter-group { display: flex; gap: 10px; }
        .input-style { padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px; min-width: 200px; }
        .btn-search { background: var(--primary-black); color: #fff; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: 0.3s; }
        .btn-search:hover { opacity: 0.8; }
        .btn-add { background: var(--primary-black); color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: 800; font-size: 13px; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* --- TABLE STYLE --- */
        .data-box { background: #fff; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 5px 20px rgba(0,0,0,0.02); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #fafafa; padding: 18px 25px; font-size: 11px; text-transform: uppercase; color: #aaa; border-bottom: 1px solid #eee; }
        td { padding: 18px 25px; border-bottom: 1px solid #f8f8f8; font-size: 14px; vertical-align: middle; }
        
        .product-info-cell { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; background: #eee; border: 1px solid #eee; }
        .product-name { font-weight: 800; color: #000; display: block; }
        .product-meta { font-size: 12px; color: #999; }

        .price-text { font-weight: 800; color: #000; }
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .stock-in { background: #e8f5e9; color: #2e7d32; }
        .stock-out { background: #ffebee; color: #c62828; }

        /* Actions */
        .btn-action-group { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-circle { width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; font-size: 14px; border: 1px solid #eee; background: #fff; color: #555; }
        .btn-circle:hover.edit { background: var(--primary-black); color: #fff; border-color: var(--primary-black); }
        .btn-circle:hover.delete { background: var(--accent); color: #fff; border-color: var(--accent); }
        
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 13px; border: 1px solid #c8e6c9; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link active"><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
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
            <h1>Quản lý sản phẩm</h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert-success"><i class="fa-solid fa-check-circle"></i> Sản phẩm đã được xóa thành công!</div>
        <?php endif; ?>

        <div class="toolbar">
            <form action="" method="GET" class="filter-group">
                <input type="text" name="search" class="input-style" placeholder="Tìm tên hoặc mã SP..." value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="category" class="input-style">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php while($cat = mysqli_fetch_assoc($res_categories)): ?>
                        <option value="<?php echo $cat['MaLoai']; ?>" <?php echo ($category_filter == $cat['MaLoai']) ? 'selected' : ''; ?>>
                            <?php echo $cat['TenLoai']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit" class="btn-search"><i class="fa-solid fa-filter"></i> LỌC</button>
            </form>

            <a href="add_product.php" class="btn-add">+ THÊM SẢN PHẨM</a>
        </div>

        <div class="data-box">
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Size</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($res && mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $sl_ton = (int)$row['SoLuongTon'];
                    ?>
                        <tr>
                            <td>
                                <div class="product-info-cell">
                                    <img src="../assets/img/<?php echo $row['HinhAnh']; ?>" 
                                         class="product-img" 
                                         onerror="this.src='https://via.placeholder.com/100?text=5'">
                                    <div>
                                        <span class="product-name"><?php echo htmlspecialchars($row['TenSP']); ?></span>
                                        <span class="product-meta">Mã SP: #<?php echo $row['MaSP']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><span style="font-weight: 600; color: #666; font-size: 13px;"><?php echo htmlspecialchars($row['TenLoai'] ?? 'N/A'); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($row['Size']); ?></strong></td>
                            <td><span class="price-text"><?php echo number_format($row['GiaNiemYet'], 0, ',', '.'); ?>đ</span></td>
                            <td>
                                <?php if($sl_ton > 0): ?>
                                    <span class="badge stock-in">Còn hàng (<?php echo $sl_ton; ?>)</span>
                                <?php else: ?>
                                    <span class="badge stock-out">Hết hàng</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="btn-action-group">
                                    <a href="edit_product.php?id=<?php echo $row['MaSP']; ?>" class="btn-circle edit" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="products.php?delete_id=<?php echo $row['MaSP']; ?>" class="btn-circle delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này vĩnh viễn?')" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 60px; color: #999; font-weight: 600;'>Không tìm thấy sản phẩm nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>