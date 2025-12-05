<?php
// install.php - Công cụ thiết lập Database và bảng
ini_set('display_errors', 1);
error_reporting(E_ALL);

$server = "localhost";
$username = "root";
$password = "";
$dbname = "webtichhopchatbotai"; // Database name đã được giữ nguyên

// Kết nối server MySQL (dùng mysqli hướng đối tượng)
$conn = new mysqli($server, $username, $password);

if ($conn->connect_error) {
    die("Không kết nối được MySQL: " . $conn->connect_error);
}

// 1. TẠO DATABASE
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "✔ Đã tạo database **$dbname**<br>";
} else {
    die("Lỗi tạo DB: " . $conn->error);
}

// Chọn database
$conn->select_db($dbname);

// --- 2. XÓA VÀ TẠO LẠI BẢNG ---

// 2.1. Bảng users
$conn->query("DROP TABLE IF EXISTS chat_history"); // Xóa bảng phụ thuộc trước
$conn->query("DROP TABLE IF EXISTS users");
$sql_users_table = "
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_users_table) === TRUE) {
    echo "✔ Đã tạo bảng **users**<br>";
} else {
    die("Lỗi tạo bảng users: " . $conn->error);
}

// 2.2. Bảng chat_history
$conn->query("DROP TABLE IF EXISTS chat_history");
$sql_chat_table = "
CREATE TABLE chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender ENUM('user', 'bot') NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql_chat_table) === TRUE) {
    echo "✔ Đã tạo bảng **chat_history**<br>";
} else {
    die("Lỗi tạo bảng chat_history: " . $conn->error);
}


// 2.3. Bảng products 
$conn->query("DROP TABLE IF EXISTS products");
$sql_table_products = "
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, 
    description TEXT,
    image VARCHAR(255),
    size VARCHAR(50),
    category ENUM('Tshirt', 'Jean', 'Accessory') NOT NULL 
)";
if ($conn->query($sql_table_products) === TRUE) {
    echo "✔ Đã tạo lại bảng **products** (Đã tối ưu category)<br>";
} else {
    die("Lỗi tạo bảng products: " . $conn->error);
}

// 3. THÊM DỮ LIỆU MẪU VÀ USER MẪU
// Thêm user mẫu (password là 123456)
$hashed_password = password_hash('123456', PASSWORD_DEFAULT);
$sql_insert_user = "
INSERT INTO users (username, email, password_hash) 
VALUES ('test_user', 'test@example.com', '{$hashed_password}')
";

if ($conn->query($sql_insert_user) === TRUE) {
    echo "✔ Đã thêm user mẫu **test_user** (Pass: 123456)<br>";
} else {
    die("Lỗi thêm user mẫu: " . $conn->error);
}

// Thêm dữ liệu sản phẩm (6 sản phẩm mỗi loại)
$sql_insert_products = "
INSERT INTO products (name, price, description, image, size, category)
VALUES
/* --- 6 SẢN PHẨM ÁO (Tshirt) --- */
('Áo thun Classic Đen', 150000.00, 'Áo thun cotton 100% cơ bản, màu đen', 'aothun_black.jpg', 'M,L,XL', 'Tshirt'),
('Áo Polo Thể Thao Trắng', 250000.00, 'Chất liệu thun cá sấu co giãn', 'polo_sport.jpg', 'S,M,L', 'Tshirt'),
('Áo Khoác Bomber Xanh', 650000.00, 'Áo khoác kaki form rộng, màu xanh rêu', 'bomber_green.jpg', 'L,XL', 'Tshirt'),
('Áo Sơ Mi Denim Bạc', 380000.00, 'Sơ mi jeans phong cách bụi bặm', 'denim_shirt.jpg', 'S,M,L', 'Tshirt'),
('Áo Hoodie Nỉ Xám', 450000.00, 'Áo nỉ có mũ dày dặn, ấm áp', 'hoodie_grey.jpg', 'M,L', 'Tshirt'),
('Áo Len Cổ Tròn Xám', 320000.00, 'Áo len dệt kim cổ tròn, giữ ấm tốt', 'aolen_grey.jpg', 'S,M,L', 'Tshirt'),

/* --- 6 SẢN PHẨM QUẦN (Jean) --- */
('Quần jean Slimfit Xanh', 400000.00, 'Jean co giãn, ôm vừa vặn', 'jean_slim.jpg', '30,31,32,33', 'Jean'),
('Quần Kaki Ống Rộng Kem', 350000.00, 'Quần kaki ống suông thoải mái', 'kaki_wide.jpg', '29,30,31', 'Jean'),
('Quần Short Nỉ Xanh', 180000.00, 'Quần short mặc nhà hoặc tập luyện', 'short_jogger.jpg', 'M,L,XL', 'Jean'),
('Quần Jean Baggy Trắng', 450000.00, 'Jean form Baggy cạp cao, phong cách trẻ trung', 'jean_baggy_white.jpg', '28,29,30', 'Jean'),
('Quần Tây Caro Đen', 380000.00, 'Quần tây họa tiết caro lịch sự', 'tay_caro.jpg', '29,31,33', 'Jean'),
('Quần Jogger Thun Đen', 260000.00, 'Quần jogger chất liệu thun, năng động', 'jogger_black.jpg', 'M,L,XL', 'Jean'),

/* --- 6 SẢN PHẨM PHỤ KIỆN (Accessory) --- */
('Nón lưỡi trai Logo Đỏ', 85000.00, 'Nón lưỡi trai phong cách thể thao', 'non_logo.jpg', 'Freesize', 'Accessory'),
('Túi Đeo Chéo Canvas', 220000.00, 'Túi vải canvas nhỏ gọn', 'tui_canvas.jpg', 'One Size', 'Accessory'),
('Vớ Cổ Ngắn Trắng (3 đôi)', 50000.00, 'Vớ cotton cổ ngắn, thoáng khí', 'vo_trang.jpg', 'Freesize', 'Accessory')
";

if ($conn->query($sql_insert_products) === TRUE) {
    echo "✔ Đã thêm 18 dữ liệu mẫu (6 mỗi loại) vào bảng **products**!<br>";
} else {
    die("Lỗi thêm dữ liệu sản phẩm: " . $conn->error);
}

echo "<h3>🎉 Cài đặt Database hoàn tất!</h3>";

$conn->close();
?>