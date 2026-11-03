<?php
include 'database.php';

// 1. Lấy ma_sp từ URL
$ma_sp = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ma_sp <= 0) {
    header("Location: index.php");
    exit();
}

// 2. Lấy thông tin sản phẩm cần sửa bằng Prepared Statement PDO
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE ma_sp = ?");
$stmt->execute([$ma_sp]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy sản phẩm
if (!$sp) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit();
}

// 3. Lấy danh sách Danh mục và Nhà cung cấp cho thẻ <select>
$danhmuc_stmt = $conn->query("SELECT * FROM danhmuc");
$danhmucs = $danhmuc_stmt->fetchAll(PDO::FETCH_ASSOC);

$ncc_stmt = $conn->query("SELECT * FROM nhacungcap");
$nccs = $ncc_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Xử lý khi Submit Form Cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = floatval($_POST['gia']);
    $so_luong = intval($_POST['so_luong']);
    $ma_dm = intval($_POST['ma_dm']);
    $ma_ncc = intval($_POST['ma_ncc']);
    
    // Kiểm tra tính hợp lệ của dữ liệu
    if (empty($ten_sp) || $gia <= 0 || $so_luong < 0) {
        echo "<script>alert('Vui lòng kiểm tra lại: Tên không trống, giá > 0 và số lượng không âm!');</script>";
    } else {
        // Giữ lại tên ảnh cũ mặc định
        $hinh_anh = $sp['hinh_anh'];

        // Nếu người dùng chọn ảnh mới
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $hinh_anh = time() . "_" . basename($_FILES['hinh_anh']['name']);
            move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_dir . $hinh_anh);
        }

        // Truy vấn Cập nhật (UPDATE) bằng PDO
        $sql = "UPDATE sanpham SET ten_sp = ?, ma_dm = ?, ma_ncc = ?, gia = ?, so_luong = ?, hinh_anh = ? WHERE ma_sp = ?";
        $update_stmt = $conn->prepare($sql);
        
        if ($update_stmt->execute([$ten_sp, $ma_dm, $ma_ncc, $gia, $so_luong, $hinh_anh, $ma_sp])) {
            echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='index.php';</script>";
            exit();
        } else {
            $errorInfo = $update_stmt->errorInfo();
            echo "Lỗi cập nhật: " . $errorInfo[2];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Sản Phẩm</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f6f9; }
        .form-container { background: white; padding: 25px; border-radius: 6px; max-width: 600px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-group { margin-top: 20px; }
        .btn-submit { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>CẬP NHẬT SẢN PHẨM</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" name="ten_sp" value="<?= htmlspecialchars($sp['ten_sp']) ?>" required>
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <select name="ma_dm" required>
                    <?php foreach($danhmucs as $dm): ?>
                        <option value="<?= $dm['ma_dm'] ?>" <?= ($dm['ma_dm'] == $sp['ma_dm']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dm['ten_dm']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Nhà cung cấp:</label>
                <select name="ma_ncc" required>
                    <?php foreach($nccs as $ncc): ?>
                        <option value="<?= $ncc['ma_ncc'] ?>" <?= ($ncc['ma_ncc'] == $sp['ma_ncc']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ncc['ten_ncc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Giá (VNĐ):</label>
                <input type="number" name="gia" step="0.01" value="<?= $sp['gia'] ?>" required>
            </div>

            <div class="form-group">
                <label>Số lượng:</label>
                <input type="number" name="so_luong" value="<?= $sp['so_luong'] ?>" required>
            </div>

            <div class="form-group">
                <label>Ảnh hiện tại:</label><br>
                <?php if(!empty($sp['hinh_anh'])): ?>
                    <img src="uploads/<?= htmlspecialchars($sp['hinh_anh']) ?>" width="120" style="margin: 8px 0; border-radius: 4px;"><br>
                <?php endif; ?>
                <input type="file" name="hinh_anh">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-submit">Lưu thay đổi</button>
                <a href="index.php" class="btn-back">Hủy bỏ</a>
            </div>

        </form>
    </div>

</body>
</html>