<?php
// includes/footer.php

global $conn;

// 💡 SỬA LỖI TRIỆT ĐỂ: Bỏ hoàn toàn @$conn->close() khỏi file này
// thay vào đó, hãy xóa nó khỏi các file chính (Bước 2)
// Nếu bạn muốn giữ lệnh đóng kết nối ở đây, hãy bỏ @ và kiểm tra kỹ
// (Mặc dù tôi đã đề nghị bỏ hẳn, nhưng nếu bạn muốn giữ, đây là cách)

// ⚠️ Nếu bạn VẪN MUỐN ĐÓNG Ở FOOTER, HÃY SỬA THÀNH:
/*
if (isset($conn) && is_object($conn) && $conn->ping()) {
    $conn->close();
}
*/
// TÔI KHUYẾN NGHỊ BỎ HẲN VÀ CHỈ DỰA VÀO PHP TỰ ĐÓNG KẾT NỐI KHI SCRIPT KẾT THÚC.
// GIỮ NỘI DUNG HTML CÒN LẠI NHƯ CŨ
?>

<footer>
    <div class="container footer-content">
        <div class="footer-section">
            <h4>VỀ CHÚNG TÔI</h4>
            <p>ClothBot là trang TMĐT kết hợp Chatbot AI để tư vấn phong cách thời trang.</p>
        </div>
        <div class="footer-section">
            <h4>LIÊN KẾT NHANH</h4>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="cart.php">Giỏ hàng</a></li>
                <li><a href="profile.php">Hồ sơ</a></li>
                <li><a href="chat_history.php">Lịch sử Chat</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>LIÊN HỆ</h4>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> 123 Đường AI, TP. Hà Nội</li>
                <li><i class="fas fa-phone"></i> 098.765.4321</li>
                <li><i class="fas fa-envelope"></i> support@clothbot.com</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> Phát triển bởi nhóm 3 - Đồ án Chatbot AI.
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