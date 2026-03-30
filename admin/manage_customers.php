<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();    
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// --- CHỨC NĂNG MỚI 1: XỬ LÝ KHÓA/MỞ KHÓA TÀI KHOẢN ---
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $current_status = intval($_GET['toggle_status']);
    $new_status = ($current_status == 1) ? 0 : 1;
    
    mysqli_query($conn, "UPDATE KHACHHANG SET TrangThai = $new_status WHERE MaKH = '$id'");
    header("Location: manage_customers.php?msg=status_updated");
    exit();
}

// 2. XỬ LÝ TÌM KIẾM
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_sql = $search ? "WHERE HoTen LIKE '%$search%' OR TenDangNhap LIKE '%$search%' OR SDT LIKE '%$search%'" : "";

// 3. TRUY VẤN DANH SÁCH
$sql = "SELECT * FROM KHACHHANG $where_sql ORDER BY MaKH DESC";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ KHÁCH HÀNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- GIỮ NGUYÊN CSS ROOT & SIDEBAR ĐÃ ĐỒNG NHẤT --- */
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

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .top-header h1 { font-weight: 900; font-size: 22px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }

        .search-wrapper { position: relative; }
        .search-wrapper input { padding: 12px 15px 12px 40px; border-radius: 10px; border: 1px solid #eee; width: 350px; outline: none; transition: 0.3s; }
        .search-wrapper i { position: absolute; left: 15px; top: 15px; color: #aaa; }

        /* --- TABLE STYLE --- */
        .data-box { background: #fff; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #fafafa; padding: 20px; font-size: 11px; text-transform: uppercase; color: #aaa; border-bottom: 1px solid #eee; }
        td { padding: 20px; border-bottom: 1px solid #f8f8f8; font-size: 14px; vertical-align: middle; }
        
        .cust-cell { display: flex; align-items: center; gap: 15px; }
        .cust-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; background: #eee; }
        .cust-name { font-weight: 800; color: #000; display: block; }
        .cust-user { font-size: 12px; color: #999; }

        /* Status & Tier Badges */
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-locked { background: #ffebee; color: #c62828; }
        
        .tier-badge { padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 900; margin-top: 5px; display: inline-block; }
        .tier-diamond { background: #000; color: #fff; }
        .tier-gold { background: #FFD700; color: #000; }
        .tier-none { background: #eee; color: #666; }

        /* --- ACTIONS --- */
        .btn-action-group { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-circle { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; font-size: 14px; border: 1px solid #eee; background: #fff; color: #555; }
        .btn-circle:hover { background: #000; color: #fff; border-color: #000; }
        .btn-lock { color: #f44336; }
        .btn-unlock { color: #4caf50; }
        .btn-history { color: #2196f3; }
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
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Quản lý khách hàng</h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <form action="" method="GET" class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Tìm tên, SĐT hoặc username..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <div class="data-box">
            <table>
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Tích điểm / Hạng</th>
                        <th>Liên hệ</th>
                        <th style="text-align: right;">Công cụ quản lý</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res && mysqli_num_rows($res) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $status = $row['TrangThai'] ?? 1;
                            $tier = $row['HangThe'];
                            $tier_class = ($tier == 'Kim cương') ? 'tier-diamond' : (($tier == 'Vàng') ? 'tier-gold' : 'tier-none');
                        ?>
                        <tr>
                            <td>
                                <div class="cust-cell">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['HoTen']) ?>&background=random" class="cust-img">
                                    <div>
                                        <span class="cust-name"><?= htmlspecialchars($row['HoTen']) ?></span>
                                        <span class="cust-user">@<?= htmlspecialchars($row['TenDangNhap']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($status == 1): ?>
                                    <span class="badge status-active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge status-locked">Bị khóa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 800;"><?= number_format($row['DiemTichLuy']) ?> <small>PTS</small></div>
                                <span class="tier-badge <?= $tier_class ?>"><?= $tier ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 13px;"><?= $row['SDT'] ?></div>
                                <div style="font-size: 11px; color: #999;"><?= htmlspecialchars($row['Email']) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="btn-action-group">
                                    <a href="manage_customers.php?toggle_status=<?= $status ?>&id=<?= $row['MaKH'] ?>" 
                                       class="btn-circle <?= $status == 1 ? 'btn-lock' : 'btn-unlock' ?>" 
                                       title="<?= $status == 1 ? 'Khóa tài khoản' : 'Mở khóa' ?>"
                                       onclick="return confirm('Xác nhận thay đổi trạng thái tài khoản này?')">
                                        <i class="fa-solid <?= $status == 1 ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                    </a>

                                    <a href="customer_history.php?id=<?= $row['MaKH'] ?>" class="btn-circle btn-history" title="Lịch sử mua hàng">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>

                                    <a href="edit_customer.php?id=<?= $row['MaKH'] ?>&focus=points" class="btn-circle" title="Chỉnh sửa điểm/hạng">
                                        <i class="fa-solid fa-award"></i>
                                    </a>
                                    
                                    <a href="delete_customer.php?id=<?= $row['MaKH'] ?>" class="btn-circle btn-lock" title="Xóa vĩnh viễn" onclick="return confirm('Xóa khách hàng này?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 60px; color: #bbb;">Không tìm thấy khách hàng phù hợp.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>