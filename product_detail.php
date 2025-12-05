<?php
// product_detail.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 

$product = null;
$error_message = '';
$available_sizes = ['S', 'M', 'L', 'XL', 'XXL']; // Các kích cỡ mặc định

// 1. Lấy ID Sản Phẩm
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    global $conn;
    
    if ($conn) {
        // Sử dụng Prepared Statement để truy vấn sản phẩm theo ID
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            } else {
                $error_message = "Không tìm thấy sản phẩm có ID này.";
            }
        } else {
            $error_message = "Lỗi truy vấn cơ sở dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Lỗi kết nối cơ sở dữ liệu.";
    }
} else {
    $error_message = "Vui lòng cung cấp ID sản phẩm hợp lệ.";
}

// Logic kiểm tra người dùng đăng nhập
$is_logged_in = isset($_SESSION['user_id']);
$user_link = $is_logged_in ? 'profile.php' : 'login.php'; 

// Đóng kết nối DB sau khi truy vấn hoàn tất
if ($conn->connect_error === null) {
    // Chỉ đóng nếu kết nối được mở thành công
    // Đây là ví dụ cơ bản. Trong dự án thực tế, bạn nên sử dụng lớp DB để quản lý kết nối tốt hơn.
    // $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Chi Tiết Sản Phẩm'; ?> – CHATBOT AI</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/category.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* CSS CẦN THIẾT CHO BỐ CỤC 2 CỘT */
        .product-detail-layout {
            display: flex; /* Kích hoạt Flexbox cho bố cục chính */
            gap: 40px; /* Khoảng cách giữa ảnh và thông tin */
            padding-top: 30px;
        }

        .product-image-container {
            flex: 0 0 45%; /* Chiếm 45% chiều rộng, không co giãn */
            max-width: 45%;
            /* Tùy chọn: Đảm bảo ảnh chiếm hết không gian */
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }

        .product-image-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .product-info-container {
            flex: 1; /* Chiếm phần còn lại (55%), tự động co giãn */
        }

        .product-info-container h2 {
            font-size: 2.5em;
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
        }

        .product-info-container .price {
            font-size: 1.8em;
            color: #e74c3c; /* Màu đỏ nổi bật */
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        /* Bố cục cho Kích cỡ và Số lượng */
        .options-group {
            margin-bottom: 25px;
        }
        
        .options-group h4 {
            font-size: 1.1em;
            margin-bottom: 10px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .size-options {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .size-options label {
            /* Style cho ô chọn kích cỡ */
            display: block;
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            font-weight: bold;
            text-align: center;
        }

        .size-options input[type="radio"] {
            display: none; /* Ẩn nút radio mặc định */
        }

        .size-options input[type="radio"]:checked + label {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        /* Style cho Số lượng */
        .quantity-group input {
            width: 70px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Nút hành động */
        .action-buttons-detail {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn-add-to-cart-detail, .btn-wishlist {
            flex: 1; /* Chia đều không gian */
            padding: 15px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }

        .btn-add-to-cart-detail {
            background-color: #3498db;
            color: white;
        }
        .btn-add-to-cart-detail:hover {
            background-color: #2980b9;
        }

        .btn-wishlist {
            background-color: #2ecc71;
            color: white;
        }
        .btn-wishlist:hover {
            background-color: #27ae60;
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
                <li><a href="ao.php">Áo</a></li>
                <li><a href="quan.php">Quần</a></li>
                <li><a href="phukien.php">Phụ kiện</a></li>
                <li><a href="#">Giới thiệu</a></li>
                <li><a href="#">Liên hệ</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <?php if ($product): ?>
            <div class="product-detail-layout">
                
                <div class="product-image-container">
                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <div class="product-info-container">
                    
                    <h2 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="price">Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    
                    <form method="POST" action="add_to_cart.php">
                        <div class="options-group size-group">
                            <h4>Kích cỡ:</h4>
                            <div class="size-options">
                                <?php foreach ($available_sizes as $size): ?>
                                    <input type="radio" id="size_<?php echo $size; ?>" name="size" value="<?php echo $size; ?>" required <?php echo ($size === 'M') ? 'checked' : ''; ?>>
                                    <label for="size_<?php echo $size; ?>"><?php echo $size; ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="options-group quantity-group">
                            <h4>Số lượng:</h4>
                            <input type="number" name="quantity" value="1" min="1" max="100" required>
                        </div>

                        <div class="action-buttons-detail">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn-add-to-cart-detail">
                                <i class="fas fa-shopping-basket"></i> Thêm vào giỏ
                            </button>
                            <button type="button" class="btn-wishlist">
                                <i class="fas fa-heart"></i> Yêu thích
                            </button>
                        </div>
                    </form>

                    <div class="product-description" style="margin-top: 40px;">
                        <h4>Mô tả sản phẩm</h4>
                        <p><?php echo htmlspecialchars($product['description'] ?? 'Chưa có mô tả chi tiết cho sản phẩm này.'); ?></p>
                    </div>

                </div>
            </div>
        <?php else: ?>
            <div class="error-container" style="text-align: center; padding: 50px;">
                <h2 style="color: red;">Lỗi</h2>
                <p><?php echo $error_message; ?></p>
                <p>Vui lòng quay lại <a href="index.php">Trang Chủ</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <div class="footer-section"><h4>VỀ CHÚNG TÔI</h4><p>...</p></div>
        <div class="footer-section"><h4>LIÊN KẾT NHANH</h4><ul>...</ul></div>
        <div class="footer-section"><h4>LIÊN HỆ</h4><ul>...</ul></div>
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