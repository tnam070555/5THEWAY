<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN TRUY CẬP (Chỉ cho phép Admin)
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Quản trị viên';
$current_admin_id = $_SESSION['ma_admin'] ?? 0;

// 2. XỬ LÝ CẬP NHẬT QUYỀN NHANH (AJAX hoặc Page Reload)
if (isset($_GET['update_role']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $new_role = mysqli_real_escape_string($conn, $_GET['update_role']);
    
    $sql_update = "UPDATE NHANVIEN SET Quyen = '$new_role' WHERE MaNV = '$id'";
    if (mysqli_query($conn, $sql_update)) {
        // Ghi lịch sử (Sử dụng @ để ẩn lỗi nếu bảng lịch sử chưa tồn tại)
        $msg = "Đã đổi quyền nhân viên ID #$id thành $new_role";
        @mysqli_query($conn, "INSERT INTO LICHSU_HOATDONG (MaNV, HanhDong) VALUES ('$current_admin_id', '$msg')");
        
        header("Location: manage_staff.php?status=success");
        exit();
    }
}

// 3. XỬ LÝ XÓA
if (isset($_GET['delete_id'])) {
    $id_del = mysqli_real_escape_string($conn, $_GET['delete_id']);
    // Không cho phép tự xóa chính mình
    if ($id_del != $current_admin_id) {
        mysqli_query($conn, "DELETE FROM NHANVIEN WHERE MaNV = '$id_del'");
        header("Location: manage_staff.php?status=deleted");
    } else {
        header("Location: manage_staff.php?status=error_self_delete");
    }
    exit();
}

// 4. TRUY VẤN DỮ LIỆU
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = $search ? "WHERE HoTen LIKE '%$search%' OR TenDangNhap LIKE '%$search%'" : "";
$sql = "SELECT * FROM NHANVIEN $where ORDER BY MaNV DESC";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ NHÂN SỰ | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- CSS CORE (ĐỒNG NHẤT VỚI DASHBOARD) --- */
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 8px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); color: #fff; }
        .nav-logout { margin-top: auto; color: #ff4d4d !important; }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .top-header h1 { font-weight: 900; font-size: 22px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }

        /* Table & Controls */
        .card { background: #fff; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 5px 20px rgba(0,0,0,0.02); overflow: hidden; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 15px 25px; font-size: 11px; color: #aaa; text-transform: uppercase; border-bottom: 2px solid #f8f9fa; }
        .table td { padding: 15px 25px; border-bottom: 1px solid #f8f9fa; font-size: 14px; }

        .staff-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 35px; height: 35px; border-radius: 8px; background: #000; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 12px; }
        
        .role-badge { padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .role-Admin { background: #000; color: #fff; }
        .role-Kho { background: #fff3e0; color: #f57c00; }
        .role-Sale { background: #e3f2fd; color: #1976d2; }
        
        .role-select { border: 1px solid #ddd; padding: 4px; border-radius: 4px; font-size: 12px; outline: none; }
        .btn-tool { color: #ccc; margin-left: 10px; transition: 0.2s; text-decoration: none; }
        .btn-tool:hover { color: #000; }
        .btn-delete:hover { color: var(--accent); }

        .search-box { padding: 10px 15px; border: 1px solid #eee; border-radius: 8px; width: 250px; outline: none; }
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
            <li><a href="manage_staff.php" class="nav-link active"><i class="fa-solid fa-user-shield"></i> Nhân viên</a></li>
            <li><a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Tồn kho</a></li>
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
            <li><a href="reports.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Thống kê</a></li>
            <li><a href="../logout.php" class="nav-link nav-logout"><i class="fa-solid fa-power-off"></i> Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Quản lý nhân sự</h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?= htmlspecialchars($admin_name) ?></span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="" method="GET">
                    <input type="text" name="search" class="search-box" placeholder="Tìm tên nhân viên..." value="<?= htmlspecialchars($search) ?>">
                </form>
                <a href="add_staff.php" style="font-size: 12px; font-weight: 800; color: #000; text-decoration: none;">+ THÊM MỚI</a>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Phân quyền</th>
                        <th>Chức vụ hiện tại</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $role = $row['Quyen'] ?? 'Nhân viên';
                        ?>
                        <tr>
                            <td>
                                <div class="staff-info">
                                    <div class="avatar"><?= strtoupper(substr($row['HoTen'], 0, 1)) ?></div>
                                    <div>
                                        <div style="font-weight: 700;"><?= htmlspecialchars($row['HoTen']) ?></div>
                                        <div style="font-size: 11px; color: #aaa;">@<?= htmlspecialchars($row['TenDangNhap']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <select class="role-select" onchange="location.href='manage_staff.php?id=<?= $row['MaNV'] ?>&update_role='+this.value">
                                    <option value="Admin" <?= $role=='Admin'?'selected':'' ?>>Quản trị</option>
                                    <option value="Kho" <?= $role=='Kho'?'selected':'' ?>>Thủ kho</option>
                                    <option value="Sale" <?= $role=='Sale'?'selected':'' ?>>Bán hàng</option>
                                    <option value="Nhân viên" <?= $role=='Nhân viên'?'selected':'' ?>>Nhân viên</option>
                                </select>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $role ?>"><?= $role ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="staff_logs.php?sid=<?= $row['MaNV'] ?>" class="btn-tool" title="Xem lịch sử"><i class="fa-solid fa-clock-rotate-left"></i></a>
                                <a href="edit_staff.php?sid=<?= $row['MaNV'] ?>" class="btn-tool"><i class="fa-solid fa-pen"></i></a>
                                <a href="manage_staff.php?delete_id=<?= $row['MaNV'] ?>" class="btn-tool btn-delete" onclick="return confirm('Xóa nhân sự này?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 40px; color: #bbb;">Không tìm thấy nhân viên nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>