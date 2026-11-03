<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = floatval($_POST['gia']);
    $so_luong = intval($_POST['so_luong']);
    $ma_dm = intval($_POST['ma_dm']);
    $ma_ncc = intval($_POST['ma_ncc']);
    
    // Kiểm tra dữ liệu bắt buộc
    $errors = [];
    if (empty($ten_sp)) {
        $errors[] = "Tên sản phẩm không được để trống.";
    }
    if ($gia <= 0) {
        $errors[] = "Giá sản phẩm phải lớn hơn 0.";
    }
    if ($so_luong < 0) {
        $errors[] = "Số lượng không được nhỏ hơn 0.";
    }

    // Xử lý upload ảnh
    $hinh_anh = "";
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $hinh_anh = time() . "_" . basename($_FILES['hinh_anh']['name']);
        $target_file = $target_dir . $hinh_anh;
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file);
    } else {
        $errors[] = "Vui lòng chọn ảnh sản phẩm.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sanpham (ten_sp, ma_dm, ma_ncc, gia, so_luong, hinh_anh) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Truyền trực tiếp mảng giá trị vào execute() trong PDO
        if ($stmt->execute([$ten_sp, $ma_dm, $ma_ncc, $gia, $so_luong, $hinh_anh])) {
            header("Location: index.php");
            exit();
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "Lỗi khi thêm: " . $errorInfo[2];
        }
    } else {
        foreach ($errors as $err) {
            echo "<p style='color:red;'>$err</p>";
        }
        echo "<a href='index.php'>Quay lại</a>";
    }
}
?>