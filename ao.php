<?php
// ao.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 

// Định nghĩa thông tin trang
$page_title = "ÁO THUN & ÁO KHOÁC";
$category_filter = "Tshirt";
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

    <style>
        .product-card a {
            position: relative; 
            z-index: 10; 
            cursor: pointer;
            text-decoration: none; 
            color: inherit; 
            display: block; 
        }
        .product-card img {
            transition: transform 0.3s ease;
        }
        .product-card a:hover img {
            transform: scale(1.03); 
        }
        .product-card a:hover h3 {
            color: #e74c3c; 
            text-decoration: underline; 
        }

        /* --- STYLES CHO HAI NÚT HÀNH ĐỘNG --- */
        .product-info .action-buttons {
            /* SỬ DỤNG FLEXBOX ĐỂ ĐẶT 2 NÚT CẠNH NHAU */
            display: flex;
            gap: 10px; /* Khoảng cách giữa hai nút */
            margin-top: 10px;
        }
        
        .product-info .action-buttons > * {
            flex-grow: 1; /* Cả hai nút (link và form) chiếm đều không gian */
            text-align: center;
        }

        /* Đảm bảo form thêm nhanh không bị giãn quá mức */
        .product-info .action-buttons form {
            width: 50%; /* Chiếm 50% trong flex container */
        }

        .btn-view-detail {
            /* Style cho nút Xem Sản Phẩm */
            display: block; /* Quan trọng để flexbox hoạt động */
            text-align: center;
            background-color: #3498db; 
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
        }

        .btn-view-detail:hover {
            background-color: #2980b9;
        }
        
        /* Style cho nút Thêm Nhanh */
        .btn-add-to-cart {
            width: 100%; /* Đảm bảo nút trong form chiếm 100% của form */
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
                <a href="login.php"><i class="fas fa-user"></i> Tài khoản</a>
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
                    <li><label><input type="checkbox"> Áo thun</label></li>
                    <li><label><input type="checkbox"> Áo khoác</label></li>
                </ul>
            </div>
        </aside>

        <section id="productContent">
            <h2 class="section-title"><?php echo $page_title; ?></h2>
            
            <div class="product-grid">
                <?php
                if (isset($conn)) {
                    global $conn; 
                    $sp = mysqli_query($conn, "SELECT * FROM products WHERE category = '$category_filter' ORDER BY id DESC LIMIT 10"); 
                    if ($sp && mysqli_num_rows($sp) > 0) {
                        while ($row = mysqli_fetch_assoc($sp)) {
                            
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
                        }
                    } else {
                        echo "<p style='text-align: center; grid-column: 1 / -1;'>Không có sản phẩm nào thuộc danh mục **Áo** để hiển thị.</p>";
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