<?php
// phukien.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 

// Định nghĩa thông tin trang
$page_title = "PHỤ KIỆN THỜI TRANG";
$category_filter = "Accessory";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> – CHATBOT AI</title>
    
    <link rel="stylesheet" href="css/style.css"> 
    
    <link rel="stylesheet" href="css/category.css"> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="#"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                <a href="#"><i class="fas fa-user"></i> Tài khoản</a>
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
    <div class="container main-content-layout">
        
        <aside id="productSidebar">
            <h4 class="filter-title">🔎 Lọc sản phẩm</h4>
            <div class="filter-group">
                <h5>Loại sản phẩm</h5>
                <ul>
                    <li><label><input type="checkbox"> Nón</label></li>
                    <li><label><input type="checkbox"> Giày</label></li>
                </ul>
            </div>
        </aside>

        <section id="productContent">
            <h2 class="section-title"><?php echo $page_title; ?></h2>
            
            <div class="product-grid">
                <?php
                if (isset($conn)) {
                    global $conn; 
                    // TRUY VẤN LỌC THEO CATEGORY
                    $sp = mysqli_query($conn, "SELECT * FROM products WHERE category = '$category_filter' ORDER BY id DESC"); 
                    if (mysqli_num_rows($sp) > 0) {
                       // CODE MỚI ĐÃ SỬA LỖI (Thay thế trong vòng lặp while)
while ($row = mysqli_fetch_assoc($sp)) {
    // ... bên trong vòng lặp while ($row = mysqli_fetch_assoc($sp)) ...

 $product_id = $row['id'];
                            $product_name = htmlspecialchars($row['name']);
                            $product_image = htmlspecialchars($row['image']);
                            $product_price = number_format($row['price'], 0, ',', '.');

                            echo "
                            <div class='product-card'>
                                <a href='product_detail.php?id={$product_id}'> 
                                    <img src='images/{$product_image}' alt='{$product_name}'>
                                </a>
                                <div class='product-info'>
                                    <a href='product_detail.php?id={$product_id}'> 
                                        <h3>{$product_name}</h3>
                                    </a>
                                    <p class='price'>{$product_price} VNĐ</p>
                                    
                                    <div class='action-buttons'>
                                        <a href='product_detail.php?id={$product_id}' class='btn-view-detail'>
                                            <i class='fas fa-eye'></i> Xem Sản Phẩm
                                        </a>

                                        <form method='POST' action='add_to_cart.php'>
                                            <input type='hidden' name='product_id' value='{$product_id}'>
                                            <input type='hidden' name='size' value='M'>
                                            <input type='hidden' name='quantity' value='1'>
                                            <button type='submit' name='add_to_cart' class='btn-add-to-cart'><i class='fas fa-cart-plus'></i> Thêm</button>
                                        </form>
                                    </div>
                                </div>
                            </div>";

// ... kết thúc vòng lặp ...
}
                        echo "<p style='text-align: center; grid-column: 1 / -1;'>Không có sản phẩm nào thuộc danh mục **Phụ kiện** để hiển thị.</p>";
                    }
                } else {
                    echo "<p style='text-align: center; grid-column: 1 / -1; color: red;'>Lỗi: Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra includes/db.php.</p>";
                }
                ?>
            </div>
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
        &copy;
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