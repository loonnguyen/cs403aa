<?php

// includes/header.php



// 1. Bắt đầu Session ở đầu trang

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}



// 2. KẾT NỐI DATABASE (RẤT QUAN TRỌNG CHO CÁC TRANG KHÁC)

require_once __DIR__ . "/db.php";





// --------------------------------------------------

// LOGIC HIỂN THỊ TRẠNG THÁI ĐĂNG NHẬP

// --------------------------------------------------



$account_link = 'login.php';

$account_text = 'Tài khoản';

$logout_html = '';



if (isset($_SESSION['user_id'])) {

    // Nếu đã đăng nhập, hiển thị tên người dùng và liên kết Đăng xuất

    $account_link = 'profile.php';

    $account_text = htmlspecialchars($_SESSION['username']);

    $logout_html = '<a href="logout.php" style="margin-left: 10px; color: #e74c3c; font-size: 0.9em;">(Đăng xuất)</a>';

}

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SHOP QUẦN ÁO – CHATBOT AI</title>

    <link rel="stylesheet" href="css/style.css">

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

                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>

                <a href="<?php echo $account_link; ?>"><i class="fas fa-user"></i> <?php echo $account_text; ?></a>

                <?php echo $logout_html; ?>

            </div>

            </div>

        <nav id="mainNav">

            <ul>

                <li><a href="index.php">Trang chủ</a></li>

                <li><a href="index.php?category=Tshirt">Áo</a></li>

                <li><a href="index.php?category=Jean">Quần</a></li>

                <li><a href="index.php?category=Accessory">Phụ kiện</a></li>

                <li><a href="#">Liên hệ</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li><a href="chat_history.php">Lịch sử Chat</a></li>

                <?php endif; ?>

            </ul>

        </nav>

    </div>

</header>



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

<?php

// Đóng các thẻ HTML trong footer.php

?>