<?php

// 1. Khởi tạo session để sử dụng và lưu dữ liệu phiên
session_start();

// 2. Kết nối cơ sở dữ liệu
// Nạp file database.php để sử dụng biến $pdo
require_once __DIR__ . "/database-login.php";

// 3. Khởi tạo biến thông báo
// Dùng để lưu thông báo lỗi hoặc kết quả đăng ký
$message = "";


// 4. Kiểm tra người dùng có gửi form bằng phương thức POST không
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 5. Lấy dữ liệu từ form đăng ký
    // trim() loại bỏ khoảng trắng ở đầu và cuối tên đăng nhập
    $username = trim($_POST["username"]);

    // Lấy email và loại bỏ khoảng trắng ở đầu, cuối
    $email = trim($_POST["email"]);

    // Lấy mật khẩu người dùng nhập
    // Không dùng trim() để tránh thay đổi mật khẩu
    $password = $_POST["password"];


    // 6. Kiểm tra người dùng có bỏ trống trường nào không
    if ($username === "" || $email === "" || $password === "") {

        // Nếu thiếu thông tin thì hiển thị thông báo
        $message = "Vui lòng nhập đầy đủ thông tin.";

    // 7. Kiểm tra độ dài mật khẩu
    } elseif (strlen($password) < 6) {

        // Nếu mật khẩu ít hơn 6 byte thì thông báo lỗi
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";

    } else {

        // 8. Kiểm tra username hoặc email đã tồn tại chưa
        // Chỉ lấy cột id để xác định có tài khoản trùng hay không
        $check = $pdo->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        // Truyền username và email vào hai dấu ?
        $check->execute([$username, $email]);

        // 9. Kiểm tra kết quả truy vấn
        if ($check->fetch()) {

            // Nếu tìm thấy tài khoản trùng username hoặc email
            $message = "Tên đăng nhập hoặc email đã tồn tại.";

        } else {

            // 10. Mã hóa mật khẩu trước khi lưu vào database
            // PASSWORD_DEFAULT cho PHP lựa chọn thuật toán mặc định phù hợp
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // 11. Chuẩn bị câu lệnh thêm tài khoản mới vào bảng users
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            // 12. Thực thi câu lệnh INSERT
            // Lưu username, email và mật khẩu đã mã hóa
            $stmt->execute([
                $username,
                $email,
                $hashedPassword
            ]);

            // 13. Thông báo đăng ký thành công
            $message = "Đăng ký thành công! Bạn có thể đăng nhập.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
    <h2>Đăng ký tài khoản</h2>

    <?php if ($message): ?>
        <p class="message">
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
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Mật khẩu"
            required
        >

        <button type="submit">Đăng ký</button>
    </form>

    <p>
        Đã có tài khoản?
        <a href="login.php">Đăng nhập</a>
    </p>
</div>

</body>
</html>
