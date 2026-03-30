<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. LẤY DỮ LIỆU ĐỂ ĐỔ VÀO FORM SỬA
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $res_edit = mysqli_query($conn, "SELECT * FROM DANHMUC WHERE MaLoai = '$id_edit'");
    $edit_data = mysqli_fetch_assoc($res_edit);
}

// 3. XỬ LÝ LƯU (THÊM MỚI HOẶC CẬP NHẬT)
if (isset($_POST['btn_save'])) {
    $ten = mysqli_real_escape_string($conn, $_POST['ten_loai']);
    $mota = mysqli_real_escape_string($conn, $_POST['mota_loai']);
    $thutu = intval($_POST['thu_tu_ht']);
    $id_up = isset($_POST['id_loai']) ? mysqli_real_escape_string($conn, $_POST['id_loai']) : '';

    if (!empty($ten)) {
        if ($id_up != '') {
            $sql = "UPDATE DANHMUC SET TenLoai='$ten', MoTaLoai='$mota', ThuTuHienThi='$thutu' WHERE MaLoai='$id_up'";
        } else {
            $sql = "INSERT INTO DANHMUC (TenLoai, MoTaLoai, ThuTuHienThi) VALUES ('$ten', '$mota', '$thutu')";
        }
        mysqli_query($conn, $sql);
        header("Location: manage_categories.php?status=success");
        exit();
    }
}

// 4. XỬ LÝ LƯU NHANH THỨ TỰ
if (isset($_POST['btn_update_order'])) {
    foreach ($_POST['order_val'] as $id => $val) {
        $id = mysqli_real_escape_string($conn, $id);
        $val = intval($val);
        mysqli_query($conn, "UPDATE DANHMUC SET ThuTuHienThi='$val' WHERE MaLoai='$id'");
    }
    header("Location: manage_categories.php?status=ordersaved");
    exit();
}

// 5. XỬ LÝ XÓA
if (isset($_GET['delete_id'])) {
    $id_del = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM DANHMUC WHERE MaLoai = '$id_del'");
    header("Location: manage_categories.php?status=deleted");
    exit();
}

// TRUY VẤN DANH SÁCH
$res = mysqli_query($conn, "SELECT * FROM DANHMUC ORDER BY ThuTuHienThi ASC, MaLoai DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ DANH MỤC | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- ĐỒNG NHẤT CSS ROOT VỚI DASHBOARD.PHP --- */
        :root { 
            --sidebar-width: 260px; 
            --primary-black: #000; 
            --bg-light: #f8f9fa; 
            --accent: #ff3b30; 
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

        /* --- SIDEBAR STYLE (MATCHING DASHBOARD) --- */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 8px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { margin-top: auto; background: #222; color: #ff4d4d !important; }

        /* --- MAIN CONTENT & LAYOUT --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .top-header h1 { font-weight: 900; font-size: 22px; text-transform: uppercase; }
        .user-pill { background: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-weight: 700; display: flex; align-items: center; gap: 10px; }
        
        .admin-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 30px; align-items: start; }
        .card { background: #fff; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 5px 20px rgba(0,0,0,0.02); overflow: hidden; }
        .card-header { padding: 20px 25px; font-weight: 800; border-bottom: 1px solid #eee; background: #fafafa; font-size: 13px; text-transform: uppercase; display: flex; justify-content: space-between; align-items: center; }

        /* --- TABLE & FORM --- */
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 15px 25px; font-size: 11px; color: #aaa; text-transform: uppercase; border-bottom: 2px solid #f8f9fa; }
        .table td { padding: 15px 25px; border-bottom: 1px solid #f8f9fa; font-size: 14px; vertical-align: middle; }
        
        .form-body { padding: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; color: #666; }
        .form-control { width: 100%; padding: 12px 15px; border: 1.5px solid #eee; border-radius: 8px; font-size: 14px; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary-black); outline: none; }
        
        .btn-submit { width: 100%; background: var(--primary-black); color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 800; cursor: pointer; transition: 0.3s; text-transform: uppercase; font-size: 13px; }
        .btn-submit:hover { opacity: 0.8; }
        .btn-update-order { background: #fff; border: 2px solid #000; color: #000; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer; }
        .btn-update-order:hover { background: #000; color: #fff; }

        .action-btns a { color: #ccc; margin-left: 15px; font-size: 16px; transition: 0.2s; }
        .action-btns a:hover.edit { color: #2196F3; }
        .action-btns a:hover.delete { color: var(--accent); }
        .input-order { width: 50px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 5px; font-weight: 700; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY</h2></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="products.php" class="nav-link"><i class="fa-solid fa-shirt"></i> Sản phẩm</a></li>
            <li><a href="manage_categories.php" class="nav-link active"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
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
            <h1>Quản lý danh mục</h1>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user"></i>
                <span><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="admin-grid">
            <div class="card">
                <form action="" method="POST">
                    <div class="card-header">
                        <span>Danh sách phân loại</span>
                        <button type="submit" name="btn_update_order" class="btn-update-order">
                            <i class="fa-solid fa-sort"></i> LƯU THỨ TỰ
                        </button>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="80">Vị trí</th>
                                <th>Tên danh mục</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                                <tr>
                                    <td>
                                        <input type="number" name="order_val[<?= $row['MaLoai'] ?>]"
                                            value="<?= $row['ThuTuHienThi'] ?>" class="input-order">
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['TenLoai']) ?></strong></td>
                                    <td style="text-align: right;" class="action-btns">
                                        <a href="manage_categories.php?edit_id=<?= $row['MaLoai'] ?>" class="edit" title="Sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="manage_categories.php?delete_id=<?= $row['MaLoai'] ?>" class="delete"
                                            onclick="return confirm('Xác nhận xóa danh mục này?')" title="Xóa">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <?= $edit_data ? "Cập nhật danh mục" : "Thêm danh mục mới" ?>
                </div>
                <div class="form-body">
                    <form action="" method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_loai" value="<?= $edit_data['MaLoai'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Tên loại sản phẩm</label>
                            <input type="text" name="ten_loai" class="form-control"
                                value="<?= $edit_data ? htmlspecialchars($edit_data['TenLoai']) : '' ?>"
                                placeholder="VD: Outerwear, Tops..." required>
                        </div>

                        <div class="form-group">
                            <label>Mô tả ngắn</label>
                            <textarea name="mota_loai" class="form-control"
                                style="height: 100px; resize: none;"><?= $edit_data ? htmlspecialchars($edit_data['MoTaLoai']) : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Thứ tự hiển thị</label>
                            <input type="number" name="thu_tu_ht" class="form-control"
                                value="<?= $edit_data ? $edit_data['ThuTuHienThi'] : '0' ?>">
                        </div>

                        <button type="submit" name="btn_save" class="btn-submit">
                            <?= $edit_data ? 'Cập nhật thay đổi' : 'Thêm danh mục' ?>
                        </button>

                        <?php if ($edit_data): ?>
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="manage_categories.php"
                                    style="font-size: 12px; color: #999; text-decoration: none;">Hủy bỏ chỉnh sửa</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>