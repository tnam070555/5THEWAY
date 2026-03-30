<?php
session_start();
include_once('../includes/db_connect.php');

// 1. KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();    
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 2. XỬ LÝ THÊM MỚI SẢN PHẨM
$msg = "";
if (isset($_POST['btn_add'])) {
    $ten = mysqli_real_escape_string($conn, $_POST['ten_sp']);
    $gia = $_POST['gia_niemyet'];
    $sl = $_POST['soluongton'];
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $mota = mysqli_real_escape_string($conn, $_POST['mota']);
    $loai = $_POST['ma_loai'];
    
    $hinh = ""; 
    // Xử lý upload ảnh
    if (!empty($_FILES['hinh_anh']['name'])) {
        $hinh = $_FILES['hinh_anh']['name'];
        // Tùy chỉnh tên file để không bị trùng (vd: time_tên_ảnh)
        $hinh = time() . "_" . $hinh;
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], "../assets/img/" . $hinh);
    }

    $sql_insert = "INSERT INTO SANPHAM (TenSP, GiaNiemYet, SoLuongTon, Size, MoTa, MaLoai, HinhAnh) 
                   VALUES ('$ten', '$gia', '$sl', '$size', '$mota', '$loai', '$hinh')";

    if (mysqli_query($conn, $sql_insert)) {
        header("Location: products.php?status=added");
        exit();
    } else {
        $msg = "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THÊM SẢN PHẨM | 5THEWAY® ADMIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-black: #000;
            --bg-light: #f4f7f6;
            --accent: #ff3b30;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }

        /* --- SIDEBAR ĐỒNG NHẤT --- */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; color: #fff; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; transition: 0.3s; margin-bottom: 5px; }
        .nav-link i { width: 25px; font-size: 18px; margin-right: 12px; }
        .nav-link:hover, .nav-link.active { background: #1a1a1a; color: #fff; }
        .nav-link.active { border-left: 4px solid var(--accent); }
        .nav-logout { margin-top: auto; color: #ff4d4d !important; }

        /* --- MAIN CONTENT --- */
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header-flex h1 { font-weight: 900; font-size: 24px; text-transform: uppercase; }
        
        .back-btn { text-decoration: none; color: var(--primary-black); font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .back-btn:hover { opacity: 0.6; }

        /* Form Layout */
        .edit-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px; }
        .card { background: var(--white); padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #eee; }
        .card-title { font-weight: 900; font-size: 16px; text-transform: uppercase; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .card-title::before { content: ''; width: 4px; height: 18px; background: var(--primary-black); display: block; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #999; margin-bottom: 8px; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s; background: #fafafa; }
        .form-control:focus { border-color: var(--primary-black); background: #fff; box-shadow: 0 0 0 4px rgba(0,0,0,0.05); }

        .row-flex { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        /* Preview Image */
        .img-upload-box { text-align: center; padding: 20px; border: 2px dashed #eee; border-radius: 12px; background: #fafafa; }
        .img-upload-box img { max-width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); display: none; } /* Ẩn đi khi chưa có ảnh */
        
        .btn-submit { background: var(--primary-black); color: var(--white); width: 100%; padding: 16px; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        textarea.form-control { height: 120px; resize: none; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>5THEWAY®</h2>
        </div>
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

    <main class="main">
        <div class="header-flex">
            <h1>Thêm sản phẩm mới</h1>
            <a href="products.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i> QUAY LẠI</a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="edit-grid">
                <div class="card">
                    <h2 class="card-title">Thông tin cơ bản</h2>
                    
                    <div class="form-group">
                        <label>Tên sản phẩm</label>
                        <input type="text" name="ten_sp" class="form-control" placeholder="Nhập tên sản phẩm..." required>
                    </div>

                    <div class="form-group">
                        <label>Mô tả chi tiết</label>
                        <textarea name="mota" class="form-control" placeholder="Mô tả về chất liệu, thiết kế..."></textarea>
                    </div>

                    <div class="row-flex">
                        <div class="form-group">
                            <label>Giá bán (VNĐ)</label>
                            <input type="number" name="gia_niemyet" class="form-control" placeholder="VD: 350000" required>
                        </div>
                        <div class="form-group">
                            <label>Số lượng kho ban đầu</label>
                            <input type="number" name="soluongton" class="form-control" value="0" required>
                        </div>
                    </div>

                    <div class="row-flex">
                        <div class="form-group">
                            <label>Kích cỡ (Size)</label>
                            <input type="text" name="size" class="form-control" placeholder="S, M, L, XL...">
                        </div>
                        <div class="form-group">
                            <label>Loại sản phẩm</label>
                            <select name="ma_loai" class="form-control" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php
                                $sql_loai = "SELECT * FROM DANHMUC";
                                $res_loai = mysqli_query($conn, $sql_loai);
                                while($row_loai = mysqli_fetch_assoc($res_loai)) {
                                    echo "<option value='".$row_loai['MaLoai']."'>".$row_loai['TenLoai']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="card">
                        <h2 class="card-title">Hình ảnh</h2>
                        <div class="img-upload-box">
                            <img src="" id="previewImg" alt="Preview">
                            <i class="fa-solid fa-cloud-arrow-up" id="uploadIcon" style="font-size: 40px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                            <input type="file" name="hinh_anh" id="fileInput" style="font-size: 11px; margin-top: 10px;" required>
                        </div>
                        <p style="font-size: 11px; color: #999; margin-top: 10px; font-style: italic;">* Bắt buộc phải chọn ảnh cho sản phẩm mới.</p>
                    </div>

                    <button type="submit" name="btn_add" class="btn-submit">
                        <i class="fa-solid fa-plus"></i> LƯU SẢN PHẨM MỚI
                    </button>
                    
                    <?php if($msg != ""): ?>
                        <div style="background: #fff1f0; color: #ff4d4f; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; text-align: center; border: 1px solid #ffccc7;">
                            <?php echo $msg; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </main>

    <script>
        // Xử lý xem trước ảnh khi Admin chọn file
        document.getElementById('fileInput').onchange = function (e) {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (val) {
                    document.getElementById('previewImg').src = val.target.result;
                    document.getElementById('previewImg').style.display = 'inline-block';
                    document.getElementById('uploadIcon').style.display = 'none'; // Ẩn icon upload đi khi đã có ảnh
                }
                reader.readAsDataURL(this.files[0]);
            }
        }
    </script>
</body>
</html>