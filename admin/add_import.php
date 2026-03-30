<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) { header('Location: ../login.php'); exit(); }
$ma_nv = $_SESSION['ma_admin'] ?? 1; 

if (isset($_POST['btn_save_import'])) {
    $nhacungcap = mysqli_real_escape_string($conn, $_POST['nha_cung_cap']);
    $tongtien = 0;

    // 1. Tạo phiếu nhập trước để lấy MaPN
    $sql_pn = "INSERT INTO PHIEUNHAP (NhaCungCap, MaNV, TongTien) VALUES ('$nhacungcap', '$ma_nv', 0)";
    if(mysqli_query($conn, $sql_pn)) {
        $ma_pn = mysqli_insert_id($conn);
        
        // 2. Lặp qua mảng sản phẩm được submit để thêm vào chi tiết
        $sp_arr = $_POST['sp'];
        $sl_arr = $_POST['sl'];
        $gia_arr = $_POST['gia'];
        
        for ($i = 0; $i < count($sp_arr); $i++) {
            $masp = $sp_arr[$i];
            $sl = $sl_arr[$i];
            $gia = $gia_arr[$i];
            
            if (!empty($masp) && $sl > 0) {
                $thanh_tien = $sl * $gia;
                $tongtien += $thanh_tien;
                
                // Thêm vào chi tiết phiếu nhập
                mysqli_query($conn, "INSERT INTO CHITIETPHIEUNHAP (MaPN, MaSP, SoLuong, GiaNhap) VALUES ('$ma_pn', '$masp', '$sl', '$gia')");
                
                // Cập nhật tồn kho sản phẩm
                mysqli_query($conn, "UPDATE SANPHAM SET SoLuongTon = SoLuongTon + $sl WHERE MaSP = '$masp'");
            }
        }
        
        // 3. Cập nhật lại tổng tiền cho phiếu nhập
        mysqli_query($conn, "UPDATE PHIEUNHAP SET TongTien = '$tongtien' WHERE MaPN = '$ma_pn'");
        header("Location: imports.php?msg=success");
        exit();
    }
}

// Lấy list SP để chọn
$res_sp = mysqli_query($conn, "SELECT MaSP, TenSP FROM SANPHAM");
$options = "";
while($r = mysqli_fetch_assoc($res_sp)) {
    $options .= "<option value='{$r['MaSP']}'>{$r['TenSP']}</option>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>TẠO PHIẾU NHẬP | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f4f7f6; --accent: #ff3b30; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); }
        
        /* Giao diện chung Sidebar & Main */
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand h2 { font-weight: 900; letter-spacing: 2px; font-size: 24px; color: #fff; }
        .nav-link { color: #888; text-decoration: none; display: flex; align-items: center; padding: 14px 18px; border-radius: 8px; font-weight: 600; margin-bottom: 5px; }
        .nav-link.active { background: #1a1a1a; color: #fff; border-left: 4px solid var(--accent); }
        .nav-link i { margin-right: 12px; width: 25px; }
        
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        .card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; }
        
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; color: #999; }
        .table td { padding: 10px; }
        
        .btn { padding: 12px 20px; border-radius: 8px; font-weight: 800; cursor: pointer; border: none; font-size: 13px; }
        .btn-add-row { background: #f0f0f0; color: #000; }
        .btn-submit { background: #000; color: #fff; width: 100%; margin-top: 20px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><h2>5THEWAY®</h2></div>
        <ul class="nav-menu" style="list-style: none;">
            <li><a href="imports.php" class="nav-link"><i class="fa-solid fa-chevron-left"></i> Quay lại</a></li>
            <li><a href="imports.php" class="nav-link active"><i class="fa-solid fa-truck-ramp-box"></i> Nhập hàng</a></li>
        </ul>
    </aside>

    <main class="main">
        <div class="card">
            <h2 style="font-weight: 900; text-transform: uppercase; margin-bottom: 25px;">Tạo phiếu nhập kho</h2>
            <form method="POST">
                <label style="font-size: 11px; font-weight: 800; color: #999; text-transform: uppercase;">Nhà cung cấp</label>
                <input type="text" name="nha_cung_cap" class="form-control" placeholder="Nhập tên đối tác / nhà cung cấp..." required>

                <table class="table" id="importTable">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá nhập (VNĐ)</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_items">
                        <tr>
                            <td>
                                <select name="sp[]" class="form-control" style="margin-bottom:0;" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?= $options ?>
                                </select>
                            </td>
                            <td><input type="number" name="sl[]" class="form-control" style="margin-bottom:0;" required min="1"></td>
                            <td><input type="number" name="gia[]" class="form-control" style="margin-bottom:0;" required min="0"></td>
                            <td><button type="button" class="btn btn-add-row" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                
                <button type="button" class="btn btn-add-row" onclick="addRow()">+ THÊM DÒNG MỚI</button>
                <button type="submit" name="btn_save_import" class="btn btn-submit">LƯU PHIẾU NHẬP & CỘNG KHO</button>
            </form>
        </div>
    </main>

    <script>
        // Xử lý Javascript thêm dòng linh hoạt
        const optionsHtml = `<?= $options ?>`;
        function addRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><select name="sp[]" class="form-control" style="margin-bottom:0;" required><option value="">-- Chọn sản phẩm --</option>${optionsHtml}</select></td>
                <td><input type="number" name="sl[]" class="form-control" style="margin-bottom:0;" required min="1"></td>
                <td><input type="number" name="gia[]" class="form-control" style="margin-bottom:0;" required min="0"></td>
                <td><button type="button" class="btn btn-add-row" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button></td>
            `;
            document.getElementById('tbody_items').appendChild(tr);
        }
        function removeRow(btn) {
            btn.closest('tr').remove();
        }
    </script>
</body>
</html>