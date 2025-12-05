<?php
// includes/db.php - Cấu hình kết nối Database

// THÔNG TIN KẾT NỐI DATABASE
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');   // Tên người dùng MySQL của bạn (thường là 'root' trên XAMPP)
define('DB_PASSWORD', '');       // Mật khẩu MySQL của bạn (thường là trống '' trên XAMPP)
define('DB_NAME', 'webtichhopchatbotai'); // Tên database bạn đã tạo trong phpMyAdmin

// Khởi tạo kết nối MySQLi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Thất bại: Ngừng chương trình và báo lỗi
    die("Lỗi kết nối database: " . $conn->connect_error);
}

// Tùy chọn: Thiết lập mã hóa ký tự để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");

// Kết nối thành công, biến $conn đã sẵn sàng để sử dụng trong các file khác
?>