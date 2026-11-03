
<?php
include 'database.php';
// Xử lý tìm kiếm bằng phương thức GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Truy vấn lấy danh sách sản phẩm kết hợp JOIN 3 bảng
$sql = "SELECT sp.*, dm.ten_dm, ncc.ten_ncc 
        FROM sanpham sp 
        LEFT JOIN danhmuc dm ON sp.ma_dm = dm.ma_dm 
        LEFT JOIN nhacungcap ncc ON sp.ma_ncc = ncc.ma_ncc";

if ($search !== '') {
    $sql .= " WHERE sp.ten_sp LIKE ?";
}

// Sử dụng Prepared Statement của PDO
$stmt = $conn->prepare($sql);

if ($search !== '') {
    // Thực thi và truyền tham số tìm kiếm với ký tự %
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt->execute();
}

// Lấy tất cả kết quả dưới dạng mảng kết hợp
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_rows = count($products);

// Lấy danh sách danh mục và nhà cung cấp phục vụ cho form thêm sản phẩm
$danhmuc_rs = $conn->query("SELECT * FROM danhmuc");
$ncc_rs = $conn->query("SELECT * FROM nhacungcap");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cửa Hàng Thiết Bị Tin Học</title>
    <!-- <link rel="stylesheet" href="style.css"> File CSS tùy chỉnh giao diện -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background: #004085; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .search-box input { padding: 8px; width: 250px; border: none; border-radius: 4px; }
        .search-box button { padding: 8px 15px; background: #0062cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .nav-menu { background: #0056b3; display: flex; list-style: none; padding: 0 20px; margin: 0; }
        .nav-menu li a { color: white; padding: 12px 20px; display: block; text-decoration: none; }
        .nav-menu li a:hover { background: #004085; }
        .container { padding: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .product-card { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
        .price { color: #dc3545; font-weight: bold; font-size: 16px; }
        .btn-group { margin-top: 10px; display: flex; justify-content: space-between; }
        .btn-edit { background: #007bff; color: white; padding: 6px 12px; text-decoration: none; border-radius: 3px; flex: 1; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 6px 12px; text-decoration: none; border-radius: 3px; flex: 1; margin-left: 5px; }
        .form-section { background: white; padding: 20px; margin-top: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .form-actions { margin-top: 20px; }
        .btn-submit { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-reset { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <!-- Header & Menu -->
    <div class="header">
        <h2>CỬA HÀNG THIẾT BỊ TIN HỌC</h2>
        <form class="search-box" method="GET" action="index.php">
            <input type="text" name="search" placeholder="Nhập tên sản phẩm..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    <ul class="nav-menu">
        <li><a href="index.php">Trang chủ</a></li>
        <li><a href="#">Sản phẩm</a></li>
        <li><a href="#">Danh mục</a></li>
        <li><a href="#">Nhà cung cấp</a></li>
        <li><a href="#">Liên hệ</a></li>
    </ul>

    <div class="container">
        <h3>DANH SÁCH SẢN PHẨM</h3>
        
        <!-- Danh sách sản phẩm dạng Thẻ (Cards) -->
        <div class="product-grid">
            <?php if ($total_rows > 0): ?>
                <?php foreach($products as $row): ?>
                    <div class="product-card">
                        <img src="uploads/<?= htmlspecialchars($row['hinh_anh']) ?>" alt="<?= htmlspecialchars($row['ten_sp']) ?>">
                        <h4><?= htmlspecialchars($row['ten_sp']) ?></h4>
                        <div class="price"><?= number_format($row['gia'], 0, ',', '.') ?> VNĐ</div>
                        <p style="font-size: 13px; text-align: left; margin: 5px 0; line-height: 1.4;">
                            Danh mục: <?= htmlspecialchars($row['ten_dm']) ?><br>
                            Nhà cung cấp: <?= htmlspecialchars($row['ten_ncc']) ?><br>
                            Số lượng: <?= htmlspecialchars($row['so_luong']) ?>
                        </p>
                        <div class="btn-group">
                            <a href="sua.php?id=<?= $row['ma_sp'] ?>" class="btn-edit">Sửa</a>
                            <a href="xoa.php?id=<?= $row['ma_sp'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">Xóa</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>

        <!-- Form Thêm Sản Phẩm Mới -->
        <div class="form-section">
            <h3>THÊM SẢN PHẨM MỚI</h3>
            <form action="them.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div>
                        <div class="form-group">
                            <label>Tên sản phẩm:</label>
                            <input type="text" name="ten_sp" required placeholder="Nhập tên sản phẩm...">
                        </div>
                        <div class="form-group">
                            <label>Giá (VNĐ):</label>
                            <input type="number" name="gia" step="0.01" required placeholder="Nhập giá...">
                        </div>
                        <div class="form-group">
                            <label>Số lượng:</label>
                            <input type="number" name="so_luong" required placeholder="Nhập số lượng...">
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label>Danh mục:</label>
                            <select name="ma_dm" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php while($dm = $danhmuc_rs->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $dm['ma_dm'] ?>"><?= htmlspecialchars($dm['ten_dm']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhà cung cấp:</label>
                            <select name="ma_ncc" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php while($ncc = $ncc_rs->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $ncc['ma_ncc'] ?>"><?= htmlspecialchars($ncc['ten_ncc']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ảnh sản phẩm:</label>
                            <input type="file" name="hinh_anh" required>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Thêm sản phẩm</button>
                    <button type="reset" class="btn-reset">Nhập lại</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>