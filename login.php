<?php
// login.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối DB
require_once __DIR__ . "/includes/db.php"; 

$error = '';
$email_phone = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_phone = trim($_POST['email_phone']);
    $password = $_POST['password'];

    // 1. Kiểm tra đầu vào
    if (empty($email_phone) || empty($password)) {
        $error = "Vui lòng điền đầy đủ Email hoặc Mật khẩu.";
    } else {
        global $conn;
        
        // 2. Truy vấn người dùng
        // Sử dụng email vì đây là trường UNIQUE mà bạn đã thiết lập trong install.php
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 3. Kiểm tra mật khẩu
            if (password_verify($password, $user['password_hash'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Chuyển hướng về trang chủ
                header("Location: index.php");
                exit();
            } else {
                $error = "Email hoặc Mật khẩu không đúng.";
            }
        } else {
            $error = "Email hoặc Mật khẩu không đúng.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – CHATBOT AI</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css"> 
</head>
<body>

<?php // Để đơn giản, tôi sẽ giả định bạn có includes/header.php
// Nếu không, hãy thay thế bằng nội dung header từ index.php
include 'includes/header.php'; 
?>

<main>
    <div class="auth-container">
        <h2>👋 ĐĂNG NHẬP</h2>
        
        <?php if ($error): ?>
            <p class="message error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="auth-form">
            <div class="form-group">
                <input type="text" id="email_phone" name="email_phone" 
                       placeholder="Email hoặc số điện thoại" required 
                       value="<?php echo htmlspecialchars($email_phone); ?>">
            </div>
            <div class="form-group password-container">
    <input type="password" id="password_login" name="password" 
           placeholder="Mật khẩu" required>
    <i class="fas fa-eye toggle-password" 
       onclick="togglePasswordVisibility('password_login', this)"></i>
</div>
            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>
        
        <div class="divider"></div>
        
        <a href="register.php" class="btn-secondary">Tạo tài khoản mới</a>
    </div>
</main>

<?php // Để đơn giản, tôi sẽ giả định bạn có includes/footer.php
// Nếu không, hãy thay thế bằng nội dung footer từ index.php
include 'includes/footer.php'; 
?>

<script src="js/chat.js"></script> 

</body>
</html>