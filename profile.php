<?php
// profile.php - Trang Hồ sơ người dùng
session_start();
// Khởi tạo các biến lỗi/thành công từ session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Bạn cần đăng nhập để truy cập trang hồ sơ.";
    header("Location: login.php");
    exit();
}

// 2. LẤY DỮ LIỆU NGƯỜI DÙNG TỪ DB
include 'includes/header.php'; // Đã bao gồm kết nối DB ($conn)
// ⚠️ LƯU Ý: Dòng này đã được loại bỏ ở các bước trước để tránh xung đột DB, 
// nhưng nếu bạn giữ nó, hãy đảm bảo rằng file `includes/header.php` KHÔNG chứa nó.
// require_once 'includes/db.php'; 

// Chỉ giữ lại require_once 'includes/db.php' nếu bạn chưa include nó trong header.php
if (!isset($conn)) {
    require_once 'includes/db.php'; 
}

$user_id = $_SESSION['user_id'];
$user_info = null;

// Lấy thông tin người dùng từ DB (chú ý: cột ID trong DB phải là 'id' hoặc 'user_id')
$sql = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user_info = $result->fetch_assoc();
    }
    $stmt->close();
}

// Đóng kết nối DB nếu đã mở
if (isset($conn)) {
    $conn->close();
}
?>

<main>
    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="auth-container" style="max-width: 600px; margin-top: 50px;">
            <h2 class="section-title">👤 HỒ SƠ CÁ NHÂN</h2>
            
            <?php if ($user_info): ?>
                <div class="user-details" style="text-align: left; padding: 20px;">
                    <p style="font-size: 1.1em; margin-bottom: 20px;">
                        Chào mừng, <strong><?php echo htmlspecialchars($user_info['username']); ?></strong>! Dưới đây là thông tin tài khoản của bạn.
                    </p>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 30%;">Tên đăng nhập:</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user_info['username']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Email:</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user_info['email']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Ngày tham gia:</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>

            <?php else: ?>
                <div class="error-message">Không thể tải thông tin người dùng. Vui lòng thử lại.</div>
            <?php endif; ?>
            
            <hr style="margin: 20px 0; border-top: 1px solid #eee;">

            <div class="action-links" style="padding-bottom: 10px;">
                <h4 style="color: #34495e; margin-bottom: 10px;">Các hành động nhanh:</h4>
                
                <button id="openChangePasswordModal" class="btn-primary" style="display: inline-block; width: 45%; margin: 5px; background-color: #f39c12; border: none; cursor: pointer;">
                    <i class="fas fa-lock"></i> Đổi Mật khẩu
                </button>
                
                <a href="chat_history.php" class="btn-primary" style="display: inline-block; width: 45%; margin: 5px; background-color: #2ecc71;">
                    <i class="fas fa-history"></i> Lịch sử Chat
                </a>
                
                <a href="logout.php" class="btn-primary" style="display: block; width: 93%; margin: 5px auto; background-color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
            
        </div>
        
        <div id="changePasswordModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 400px; border-radius: 8px;">
                <span class="close-btn" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                <h3 style="margin-top: 0; color: #3498db;">Đổi Mật khẩu</h3>
                
                <form action="change_password.php" method="POST">
                    <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                        <label for="current_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Mật khẩu cũ:</label>
                        <input type="password" id="current_password" name="current_password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                        <label for="new_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                        <label for="confirm_new_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Xác nhận Mật khẩu mới:</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; background-color: #3498db; border: none; color: white; border-radius: 4px; cursor: pointer;">Cập nhật Mật khẩu</button>
                </form>
            </div>
        </div>
        </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('changePasswordModal');
    var btn = document.getElementById('openChangePasswordModal');
    var span = document.getElementsByClassName('close-btn')[0];

    // Mở modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Đóng modal khi click vào nút X
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>