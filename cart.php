<?php
// cart.php - Trang hiển thị Giỏ hàng
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === 1. LOGIC XỬ LÝ DỮ LIỆU & SESSION ===

// Biến giỏ hàng: Giỏ hàng sử dụng khóa kết hợp 'product_id_size'
// Cấu trúc item: ['product_id', 'name', 'price', 'quantity', 'size', 'image']
$cart_items = $_SESSION['cart'] ?? []; 
$is_logged_in = isset($_SESSION['user_id']);
$user_link = $is_logged_in ? 'profile.php' : 'login.php'; 
$total_price = 0;
$success_message = '';
$error_message = '';

// Lấy và xóa thông báo Session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// === LOGIC XÓA SẢN PHẨM ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    // Nhận item key (product_id_size) để xóa item cụ thể
    // Lưu ý: Tên input đã được đổi từ 'product_id_to_remove' sang 'item_key_to_remove'
    $item_key_to_remove = $_POST['item_key_to_remove'] ?? null; 

    if ($item_key_to_remove && isset($cart_items[$item_key_to_remove])) {
        unset($cart_items[$item_key_to_remove]);
        $_SESSION['cart'] = $cart_items; // Cập nhật lại Session
        $_SESSION['success_message'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
        // Chuyển hướng POST-Redirect-GET để tránh gửi lại form
        header("Location: cart.php");
        exit;
    } else {
        $error_message = "Không tìm thấy sản phẩm cần xóa.";
    }
}


// Tính tổng tiền và cập nhật biến giỏ hàng (Cấu trúc đã được giữ nguyên)
// Lưu ý: Cần đảm bảo logic add_to_cart.php lưu key giỏ hàng là ID_SIZE
foreach ($cart_items as $item_key => $item) {
    $price = floatval($item['price']);
    $quantity = intval($item['quantity']);
    $total_price += ($price * $quantity);
    // Gán lại item_key vào item để tiện xử lý trong form Xóa
    $cart_items[$item_key]['item_key'] = $item_key; 
}


// === 2. BẮT ĐẦU PHẦN HIỂN THỊ HTML ===
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng – SHOP QUẦN ÁO</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS riêng cho giỏ hàng (Đã giữ nguyên) */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .cart-table th {
            background-color: #34495e;
            color: white;
            text-align: center;
        }
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .cart-total {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }
        .checkout-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-checkout {
            background-color: #e74c3c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-checkout:hover {
            background-color: #c0392b;
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
        <h2 class="section-title">🛒 GIỎ HÀNG CỦA BẠN</h2>

        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($cart_items)): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Size</th> <th>Giá</th>
                        <th>Số Lượng</th>
                        <th>Tổng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item_key => $item): 
                        $item_total = floatval($item['price']) * intval($item['quantity']);
                        // Đảm bảo các trường tồn tại (image có thể thiếu nếu logic add_to_cart cũ)
                        $image_path = htmlspecialchars($item['image'] ?? 'default.png'); 
                        $size = htmlspecialchars($item['size'] ?? 'N/A'); // Lấy size, mặc định N/A nếu chưa có
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <img src='images/<?php echo $image_path; ?>' alt='<?php echo htmlspecialchars($item['name']); ?>' class="cart-item-img">
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="text-align: center; font-weight: bold;"><?php echo $size; ?></td> <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                        <td style="text-align: center;"><?php echo intval($item['quantity']); ?></td>
                        <td><?php echo number_format($item_total, 0, ',', '.'); ?> VNĐ</td>
                        <td style="text-align: center;">
                            <form method="POST" action="cart.php" style="display: inline;">
                                <input type="hidden" name="item_key_to_remove" value="<?php echo $item_key; ?>"> 
                                <button type="submit" name="remove_item" class="btn-sm" style="color: red; border: none; background: none;">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-total">
                Tổng cộng: <?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ
            </div>
            
            <div class="checkout-actions">
                <a href="checkout.php" class="btn-checkout">TIẾN HÀNH THANH TOÁN</a>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 50px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <i class="fas fa-box-open fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                <p style="font-size: 1.2em; color: #666;">Giỏ hàng của bạn hiện đang trống. Hãy thêm sản phẩm ngay!</p>
                <a href="index.php" style="display: inline-block; margin-top: 20px; color: #3498db; font-weight: bold;">← Tiếp tục mua sắm</a>
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