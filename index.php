<?php
session_start(); 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 

global $conn;
$products = [];
$error_msg = '';

// --- Khai báo Danh mục sản phẩm (Dùng cho Menu và Sidebar) ---
$categories = [
    'Tshirt' => 'Áo thun/Áo khoác',
    'Jean' => 'Quần',
    'Accessory' => 'Phụ kiện'
];

// 1. Logic Lọc Sản Phẩm (Nếu có tham số 'category' được gửi từ form/menu)
$where_clauses = [];
$params = [];
$types = '';
$current_category = '';

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = trim($_GET['category']);
    
    // Kiểm tra tính hợp lệ của danh mục
    if (array_key_exists($category_filter, $categories)) {
        $where_clauses[] = "category = ?";
        $params[] = $category_filter;
        $types .= 's';
        $current_category = $category_filter;
    }
    
}

// 2. Xây dựng và Thực hiện truy vấn (Sử dụng Prepared Statements)
$sql = "SELECT * FROM products";
if (!empty($where_clauses)) {
    // Nếu có lọc, áp dụng điều kiện WHERE và sắp xếp mới nhất
    $sql .= " WHERE " . implode(' AND ', $where_clauses) . " ORDER BY id DESC";
} else {
    // Nếu không có lọc, chỉ lấy 8 sản phẩm mới nhất
    $sql .= " ORDER BY id DESC LIMIT 8";
}

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $error_msg = "Lỗi thực thi truy vấn: " . $stmt->error;
    }
    $stmt->close();
} else {
    $error_msg = "Lỗi chuẩn bị truy vấn SQL: " . $conn->error;
}
// Đóng kết nối DB sau khi truy vấn hoàn tất
// Cần đảm bảo rằng các file khác (như cart.php, add_to_cart.php) có logic kết nối riêng nếu cần.
if ($conn->connect_error === null) {
    $conn->close();
}

// Logic kiểm tra người dùng đăng nhập (Đã chuyển sang profile.php)
$is_logged_in = isset($_SESSION['user_id']);
$user_link = $is_logged_in ? 'profile.php' : 'login.php'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ – SHOP QUẦN ÁO</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/category.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Đảm bảo liên kết click được trên ảnh và tên */
        .product-card a {
            position: relative; 
            z-index: 10; 
            cursor: pointer;
            text-decoration: none; 
            color: inherit; 
            display: block; 
        }
        .product-card a:hover h3 {
            color: #e74c3c; 
            text-decoration: underline; 
        }

        /* --- STYLES CHO HAI NÚT HÀNH ĐỘNG --- */
        .product-info .action-buttons {
            display: flex;
            gap: 10px; /* Khoảng cách giữa hai nút */
            margin-top: 10px;
        }
        
        .product-info .action-buttons > * {
            flex-grow: 1; /* Chia đều không gian */
            text-align: center;
        }

        /* Nút Xem Sản Phẩm */
        .btn-view-detail {
            display: block; 
            text-align: center;
            background-color: #3498db; /* Màu xanh */
            color: white;
            padding: 8px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
            box-sizing: border-box;
        }
        .btn-view-detail:hover {
            background-color: #2980b9;
        }
        
        /* Nút Thêm vào giỏ */
        .btn-add-to-cart {
            width: 100%; 
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            background-color: #2ecc71;
            color: white;
            transition: background-color 0.3s;
            box-sizing: border-box;
        }
        .btn-add-to-cart:hover {
            background-color: #27ae60;
        }
        /* Đảm bảo form chỉ chiếm 50% */
        .product-info .action-buttons form {
            width: 50%;
        }
    </style>
</head>
<body>

<header id="mainHeader">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php"><h1>ClothBot</h1></a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Tìm kiếm sản phẩm...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="header-icons">
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                <a href="<?php echo $user_link; ?>"><i class="fas fa-user"></i> 
                    <?php echo $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'Tài khoản'; ?>
                </a>
                </div>
        </div>
        <nav id="mainNav">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="index.php?category=Tshirt">Áo</a></li>
                <li><a href="index.php?category=Jean">Quần</a></li>
                <li><a href="index.php?category=Accessory">Phụ kiện</a></li>
                <li><a href="#">Giới thiệu</a></li>
                <li><a href="#">Liên hệ</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container main-content-layout">
        
        <aside id="productSidebar">
            <h4 class="filter-title">🔎 Lọc sản phẩm</h4>
            <form action="index.php" method="GET" class="filter-form">
                <h5>Loại sản phẩm</h5>
                <div class="filter-group">
                    <?php foreach ($categories as $value => $label): ?>
                        <label class="filter-option">
                            <input type="radio" name="category" value="<?php echo $value; ?>" 
                                <?php echo ($current_category === $value) ? 'checked' : ''; ?>> 
                            <?php echo $label; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn-filter-submit">Áp dụng Lọc</button>
                <a href="index.php" class="btn-filter-reset">Xóa Lọc</a>
            </form>
        </aside>

        <section id="productContent">
            <h2 class="section-title">
                <?php echo $current_category ? "SẢN PHẨM: " . $categories[$current_category] : "✨ SẢN PHẨM MỚI NHẤT"; ?>
            </h2>
            
            <?php if (!empty($error_msg)): ?>
                <p class="message error-message" style="grid-column: 1 / -1;"><?php echo $error_msg; ?></p>
            <?php elseif (empty($products)): ?>
                <p style='text-align: center; grid-column: 1 / -1; color: #666;'>Không có sản phẩm nào để hiển thị theo tiêu chí này.</p>
            <?php else: ?>
                
<section id="productContent">

    <div class="product-grid">
        <?php foreach ($products as $row): 
            $product_id = $row['id'];
            $product_name = htmlspecialchars($row['name']);
            $product_image = htmlspecialchars($row['image']);
            $product_price = number_format($row['price'], 0, ',', '.');
        ?>
        <div class='product-card'>
            <a href='product_detail.php?id=<?php echo $product_id; ?>'>
                <img src='images/<?php echo $product_image; ?>' alt='<?php echo $product_name; ?>'>
            </a>
            <div class='product-info'>
                <a href='product_detail.php?id=<?php echo $product_id; ?>'>
                    <h3><?php echo $product_name; ?></h3>
                </a>
                <p class='price'><?php echo $product_price; ?> VNĐ</p>
                
                <div class='action-buttons'>
                    <a href='product_detail.php?id=<?php echo $product_id; ?>' class='btn-view-detail'>
                        <i class='fas fa-eye'></i> Xem Sản Phẩm
                    </a>

                    <form method='POST' action='add_to_cart.php'> 
                        <input type='hidden' name='product_id' value='<?php echo $product_id; ?>'>
                        <input type='hidden' name='size' value='M'> 
                        <input type='hidden' name='quantity' value='1'> 
                        <button type='submit' name='add_to_cart' class='btn-add-to-cart'><i class='fas fa-cart-plus'></i> Thêm</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>


                <?php endif; ?>
            </section>
        </div> 
</main>

<footer>
    <div class="container footer-content">
        <div class="footer-section">
            <h4>VỀ CHÚNG TÔI</h4>
            <p>ClothBot là cửa hàng quần áo trực tuyến hàng đầu, mang đến cho bạn những sản phẩm thời trang mới nhất với chất lượng tuyệt vời và giá cả phải chăng.</p>
        </div>
        <div class="footer-section">
            <h4>LIÊN KẾT NHANH</h4>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="#">Sản phẩm</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Điều khoản dịch vụ</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>LIÊN HỆ</h4>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> Phan Thanh, Đà Nẵng</li>
                <li><i class="fas fa-phone"></i> 07744 573 29</li>
                <li><i class="fas fa-envelope"></i> dinhdungdinhdung11@gmail.com</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> ClothBot. All rights reserved.
    </div>
</footer>

<div id="chatButton" onclick="openChat()">
    <i class="fas fa-comment-dots"></i>
</div>

<div id="chatBox">
    <div id="chatHeader">
        AI Chatbot
        <span class="close-chat" onclick="openChat()"><i class="fas fa-times"></i></span>
    </div>
    <div id="chatMessages">
        <div class="message bot-message">Chào bạn, tôi là ClothBot. Tôi có thể giúp gì cho bạn hôm nay?</div>
    </div>
    
    <div id="chatInputArea">
        <input type="text" id="userInput" placeholder="Nhập tin nhắn..." autocomplete="off">
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script src="js/chat.js"></script> 
</body>
</html>