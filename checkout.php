<?php
// checkout.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB (Cần thiết nếu bạn muốn lưu order vào DB)
require_once __DIR__ . "/includes/db.php"; 

// --- Dữ liệu MOCK Tạm thời (Thay thế bằng dữ liệu thực từ $_SESSION['cart']) ---
$cart_items = $_SESSION['cart'] ?? [
    ['id' => 1, 'name' => 'Áo Hoodie Ni Xám', 'size' => 'M', 'price' => 450000, 'quantity' => 1, 'image' => 'ao_hoodie.jpg'],
    ['id' => 2, 'name' => 'Áo Thun Classic Đen', 'size' => 'L', 'price' => 150000, 'quantity' => 2, 'image' => 'ao_den.jpg'],
    ['id' => 3, 'name' => 'Quần Jean Xanh', 'size' => '30', 'price' => 500000, 'quantity' => 1, 'image' => 'quan_jean.jpg'],
];

$shipping_fee = 30000;
$subtotal = array_reduce($cart_items, fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);
$total = $subtotal + $shipping_fee;

// Logic kiểm tra người dùng đăng nhập
$is_logged_in = isset($_SESSION['user_id']);
$user_link = $is_logged_in ? 'profile.php' : 'login.php'; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng – SHOP QUẦN ÁO</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/category.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* CSS cho trang Thanh toán */
        .checkout-layout {
            display: flex;
            gap: 30px;
            padding: 40px 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Cột 1: Thông tin khách hàng (Form) */
        .checkout-form-col {
            flex: 2; /* Chiếm 2 phần (khoảng 60-65%) */
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .checkout-form-col h3 {
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5em;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Phương thức Thanh toán */
        .payment-methods {
            margin-top: 25px;
            padding: 15px;
            border: 1px solid #f0f0f0;
            border-radius: 6px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .payment-option:hover {
            background-color: #f9f9f9;
        }

        .payment-option input[type="radio"] {
            margin-right: 10px;
            width: auto;
        }

        .payment-option i {
            margin-right: 10px;
            color: #3498db;
        }


        /* Cột 2: Tóm tắt đơn hàng */
        .order-summary-col {
            flex: 1; /* Chiếm 1 phần (khoảng 35-40%) */
            background-color: #f7f7f7;
            padding: 25px;
            border-radius: 8px;
            height: fit-content; /* Giữ kích thước theo nội dung */
            position: sticky; /* Giúp cột này cố định khi cuộn */
            top: 20px;
        }
        
        .order-summary-col h3 {
            font-size: 1.4em;
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }

        /* Danh sách sản phẩm */
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e0e0e0;
        }
        
        .item-info {
            flex: 1;
        }

        .item-info p {
            margin: 0;
            line-height: 1.3;
        }
        
        .item-price {
            font-weight: bold;
            text-align: right;
            color: #555;
        }

        /* Tổng tiền */
        .summary-total {
            padding-top: 15px;
            border-top: 2px solid #ddd;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-row.final-total {
            font-size: 1.3em;
            font-weight: bold;
            color: #e74c3c;
        }
        
        /* Nút Đặt hàng */
        .btn-place-order {
            width: 100%;
            padding: 15px;
            background-color: #2ecc71; /* Màu xanh lá cây */
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .btn-place-order:hover {
            background-color: #27ae60;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .checkout-layout {
                flex-direction: column;
            }
            .checkout-form-col, .order-summary-col {
                flex: none;
                max-width: 100%;
                width: 100%;
                position: static;
            }
        }
    </style>
</head>
<body>

<!-- HEADER VÀ NAVIGATION -->
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
                    <?php echo $is_logged_in ? htmlspecialchars($_SESSION['username'] ?? 'Tài khoản') : 'Tài khoản'; ?>
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
        <h1 style="text-align: center; margin-bottom: 15px; color: #333;">Xác nhận Thanh toán</h1>

        <div class="checkout-layout">
            
            <!-- CỘT 1: THÔNG TIN KHÁCH HÀNG VÀ THANH TOÁN -->
            <div class="checkout-form-col">
                <form action="process_order.php" method="POST">
                    
                    <!-- 1. THÔNG TIN NGƯỜI NHẬN -->
                    <h3>1. Thông tin Người nhận</h3>
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> Họ và Tên (*)</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Nhập tên người nhận">
                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Số điện thoại (*)</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Nhập số điện thoại">
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Địa chỉ nhận hàng (*)</label>
                        <input type="text" id="address" name="address" required placeholder="Số nhà, tên đường, Phường/Xã, Quận/Huyện, Tỉnh/Thành">
                    </div>
                    <div class="form-group">
                        <label for="note"><i class="fas fa-sticky-note"></i> Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea id="note" name="note" rows="3" placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."></textarea>
                    </div>

                    <!-- 2. PHƯƠNG THỨC THANH TOÁN -->
                    <h3>2. Phương thức Thanh toán</h3>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="COD" required checked>
                            <i class="fas fa-shipping-fast"></i>
                            Thanh toán khi nhận hàng (COD)
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="BANK_TRANSFER" required>
                            <i class="fas fa-university"></i>
                            Chuyển khoản Ngân hàng
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="MOMO" required disabled>
                            <i class="fas fa-wallet"></i>
                            Ví điện tử (Momo/ZaloPay) - Sắp ra mắt
                        </label>
                    </div>
                    
                    <!-- NÚT ĐẶT HÀNG -->
                    <button type="submit" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> HOÀN TẤT ĐẶT HÀNG
                    </button>
                    
                </form>
            </div>
            
            <!-- CỘT 2: TÓM TẮT ĐƠN HÀNG -->
            <div class="order-summary-col">
                <h3>Tóm tắt Đơn hàng</h3>
                
                <div class="summary-items-list">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="item-info">
                            <p><?php echo htmlspecialchars($item['name']); ?> (<?php echo htmlspecialchars($item['size']); ?>)</p>
                            <small>SL: <?php echo $item['quantity']; ?></small>
                        </div>
                        <div class="item-price">
                            <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-total">
                    <!-- Tạm tính -->
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    
                    <!-- Phí vận chuyển -->
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span><?php echo number_format($shipping_fee, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    
                    <!-- Tổng tiền cuối cùng -->
                    <div class="summary-row final-total">
                        <span>TỔNG CỘNG:</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- FOOTER VÀ CHATBOX -->
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