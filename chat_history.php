<?php
// chat_history.php - Trang hiển thị Lịch sử Chat của người dùng
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Bạn cần đăng nhập để xem lịch sử chat.";
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php'; 
include 'includes/header.php'; // Bao gồm phần mở đầu HTML và CSS
$user_id = $_SESSION['user_id'];
$chat_history = [];
$error_message = '';

// 2. LẤY DỮ LIỆU LỊCH SỬ CHAT
// ❗ ĐÃ SỬA: Chỉ truy vấn các cột 'sender' và 'message'
// Giả định bảng chat_history không có cột 'created_at'
$sql = "SELECT id, sender, message FROM chat_history WHERE user_id = ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // ❗ ĐÃ SỬA: Dùng array append ($chat_history[] = $row) thay vì array_unshift.
        // Điều này giúp giữ nguyên thứ tự "cũ nhất ở trên, mới nhất ở dưới" do SQL đã sắp xếp.
        $chat_history[] = $row; 
    }
    $stmt->close();
} else

$conn->close();

?>

<main>
    <div class="container">
        <div class="auth-container" style="max-width: 800px; margin-top: 50px; padding: 20px;">
            <h2 class="section-title">💬 LỊCH SỬ HỘI THOẠI</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (empty($chat_history)): ?>
                <div class="message info-message" style="margin-top: 20px;">
                    Bạn chưa có bất kỳ cuộc trò chuyện nào được lưu lại.
                </div>
            <?php else: ?>
                
                <div class="chat-display" style="border: 1px solid #ccc; border-radius: 8px; padding: 15px; max-height: 60vh; overflow-y: auto; background-color: #f9f9f9;">
                    <?php foreach ($chat_history as $message): ?>
                        <?php 
                            $sender_class = ($message['sender'] == 'user') ? 'user-message' : 'bot-message';
                            $sender_name = ($message['sender'] == 'user') ? 'Bạn' : 'ClothBot';
                            $alignment = ($message['sender'] == 'user') ? 'right' : 'left';
                        ?>
                        <div class="message-row" style="display: flex; justify-content: <?php echo $alignment; ?>; margin-bottom: 10px;">
                            <div class="<?php echo $sender_class; ?>" style="
                                max-width: 70%;
                                padding: 10px 15px;
                                border-radius: 18px;
                                line-height: 1.4;
                                background-color: <?php echo ($message['sender'] == 'user') ? '#3498db' : '#ecf0f1'; ?>;
                                color: <?php echo ($message['sender'] == 'user') ? 'white' : '#2c3e50'; ?>;
                                font-size: 0.95em;
                                text-align: left;
                                box-shadow: 0 1px 1px rgba(0,0,0,0.1);
                            ">
                                <strong><?php echo $sender_name; ?>:</strong>
                                <p style="margin: 0; white-space: pre-wrap;"><?php echo htmlspecialchars($message['message']); ?></p>
                                
                                </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

            <div class="action-links" style="padding-top: 20px;">
                 <a href="profile.php" class="btn-primary" style="background-color: #f39c12; display: inline-block;">
                    <i class="fas fa-user"></i> Quay lại Hồ sơ
                </a>
                <a href="index.php" class="btn-primary" style="background-color: #2ecc71; display: inline-block;">
                    <i class="fas fa-robot"></i> Trò chuyện mới
                </a>
            </div>
            
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>