<?php

// 1. Khởi tạo session để lưu thông tin người dùng
// Session giúp ghi nhớ trạng thái đăng nhập giữa các trang
session_start();

// 2. Kết nối đến cơ sở dữ liệu
// File database.php thường chứa thông tin kết nối PDO
require_once __DIR__ . "/database-login.php";

// 3. Khởi tạo biến thông báo rỗng
// Biến này dùng để hiển thị lỗi khi đăng nhập không thành công
$message = "";

// 4. Kiểm tra xem người dùng có gửi biểu mẫu bằng phương thức POST không
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 5. Lấy tên đăng nhập từ form và loại bỏ khoảng trắng ở đầu, cuối
    // Nếu không có username thì mặc định là chuỗi rỗng
    $username = trim($_POST["username"] ?? "");

    // Lấy mật khẩu từ form
    // Nếu không có password thì mặc định là chuỗi rỗng
    $password = $_POST["password"] ?? "";

    // 6. Kiểm tra người dùng đã nhập đủ tên đăng nhập và mật khẩu chưa
    if ($username === "" || $password === "") {

        // Nếu thiếu một trong hai thông tin, hiển thị thông báo
        $message = "Vui lòng nhập đầy đủ thông tin.";

    } else {

        // 7. Tạo câu lệnh SQL tìm người dùng theo username
        // Dấu ? là tham số sẽ được truyền vào khi thực thi câu lệnh
        $sql = "SELECT * FROM users WHERE username = ?";

        // 8. Chuẩn bị câu lệnh SQL bằng PDO
        // Prepared statement giúp hạn chế SQL Injection
        $stmt = $pdo->prepare($sql);

        // 9. Thực thi câu lệnh và truyền username vào dấu ?
        $stmt->execute([$username]);

        // 10. Lấy thông tin người dùng tìm được
        // PDO::FETCH_ASSOC trả kết quả dưới dạng mảng kết hợp
        // Ví dụ: $user["id"], $user["username"], $user["password"]
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 11. Kiểm tra tài khoản và mật khẩu
        // $user: có tồn tại người dùng hay không
        // password_verify(): so sánh mật khẩu nhập vào với mật khẩu đã mã hóa
        if ($user && password_verify($password, $user["password"])) {

            // 12. Đăng nhập thành công
            // Lưu ID người dùng vào session
            $_SESSION["user_id"] = $user["id"];

            // Lưu tên đăng nhập vào session
            $_SESSION["username"] = $user["username"];

            // Lưu email vào session
            $_SESSION["email"] = $user["email"];

            // 13. Chuyển người dùng sang trang index.php
            header("Location:index.php");

            // Dừng chương trình sau khi chuyển hướng
            exit;

        } else {

            // 14. Nếu không tìm thấy tài khoản hoặc mật khẩu sai
            // Hiển thị thông báo đăng nhập không thành công
            $message = "Tên đăng nhập hoặc mật khẩu không đúng.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h2>Đăng nhập</h2>

    <?php if ($message !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="username"
            placeholder="Tên đăng nhập"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Mật khẩu"
            required
        >

        <button type="submit">
            Đăng nhập
        </button>

    </form>

    <p>
        Chưa có tài khoản?
        <a href="register.php">Đăng ký</a>
    </p>

</div>

</body>
</html>
