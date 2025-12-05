<?php
// quan.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 
global $conn; // Lấy biến kết nối DB

// Định nghĩa thông tin trang
$page_title = "QUẦN JEANS & QUẦN TÂY";
// Biến lọc, sử dụng cho Prepared Statement
$category_filter = "Jean"; 
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

<?php 
// FIX: Sử dụng include/header.php để đồng bộ cấu trúc
include 'includes/header.php'; 
?>

<main>
    <div class="container main-content-layout">
        
        <aside id="productSidebar">
            <h4 class="filter-title">🔎 Lọc sản phẩm</h4>
            <div class="filter-group">
                <h5>Loại sản phẩm</h5>
                <ul>
                    <li><label><input type="checkbox" checked> Quần Jean</label></li>
                    <li><label><input type="checkbox"> Quần Tây</label></li>
                </ul>
            </div>
        </aside>

        <section id="productContent">
            <h2 class="section-title"><?php echo htmlspecialchars($page_title); ?></h2>
            
            <div class="product-grid">
                <?php
                if (isset($conn)) {
                    // FIX BẢO MẬT: Chuyển sang Prepared Statements để chống SQL Injection
                    $sql = "SELECT id, name, image, price FROM products WHERE category = ? ORDER BY id DESC";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("s", $category_filter);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                
                                // Dùng htmlspecialchars() để tránh lỗi XSS khi hiển thị dữ liệu
                                $product_id = htmlspecialchars($row['id']);
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
                            }
                        } else {
                            echo "<p style='text-align: center; grid-column: 1 / -1;'>Không có sản phẩm nào thuộc danh mục **Quần** để hiển thị.</p>";
                        }
                        $stmt->close();
                    } else {
                        // Lỗi chuẩn bị câu lệnh SQL (Nếu câu lệnh SQL bị sai cú pháp)
                        echo "<p style='text-align: center; grid-column: 1 / -1; color: red;'>Lỗi truy vấn: Vui lòng kiểm tra câu lệnh SQL.</p>";
                    }

                } else {
                    echo "<p style='text-align: center; grid-column: 1 / -1; color: red;'>Lỗi: Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra includes/db.php.</p>";
                }
                ?>
            </div>
        </section>
    </div> 
</main>

<?php 
// FIX: Sử dụng include/footer.php để đồng bộ cấu trúc
include 'includes/footer.php'; 
?>

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