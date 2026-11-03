<?php
include 'database.php';

// Lấy id sản phẩm cần xóa từ URL
$ma_sp = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ma_sp > 0) {
    // Thực thi câu lệnh DELETE bằng Prepared Statement PDO
    $stmt = $conn->prepare("DELETE FROM sanpham WHERE ma_sp = ?");
    
    if ($stmt->execute([$ma_sp])) {
        // Xóa thành công, điều hướng về lại trang chủ
        header("Location: index.php");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Lỗi khi xóa sản phẩm: " . $errorInfo[2];
    }
} else {
    header("Location: index.php");
    exit();
}
?>