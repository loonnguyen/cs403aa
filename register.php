<?php
// register.php - Logic Đăng ký tài khoản
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/db.php"; 

$error = '';
$success = '';
$username = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lọc và làm sạch đầu vào
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Kiểm tra đầu vào
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ tất cả các trường.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Địa chỉ email không hợp lệ.";
    } else {
        // Sử dụng $conn từ db.php
        global $conn; 
        
        // 2. Kiểm tra email đã tồn tại chưa (Sử dụng Prepared Statements)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email này đã được đăng ký.";
        } else {
            // 3. Hash mật khẩu và chèn vào DB
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt_insert->execute()) {
                // Đăng ký thành công, tự động đăng nhập
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                
                $success = "Đăng ký thành công! Bạn đã được đăng nhập và có thể sử dụng Chatbot.";
                // Hoặc chuyển hướng: header("Location: index.php"); exit();
            } else {
                $error = "Lỗi đăng ký: " . $conn->error;
            }
            $stmt_insert->close();
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
    <title>Đăng ký tài khoản – CHATBOT AI</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css"> 
</head>
<body>

<?php 
// FIX: Sử dụng include/header.php để đồng bộ cấu trúc
include 'includes/header.php'; 
?>

<main>
    <div class="auth-container">
        <h2>📝 TẠO TÀI KHOẢN MỚI</h2>
        <p style="color: #666; margin-bottom: 20px;">Nhanh chóng và dễ dàng.</p>
        
        <?php if ($error): ?>
            <p class="message error-message"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p class="message success-message"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <form action="register.php" method="POST" class="auth-form">
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Tên người dùng (Username)" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
           
            <div class="form-group password-container">
                <input type="password" id="password_reg" name="password" 
                placeholder="Mật khẩu (ít nhất 6 ký tự)" required>
                <i class="fas fa-eye toggle-password" 
                onclick="togglePasswordVisibility('password_reg', this)"></i>
            </div>

            <div class="form-group password-container">
                <input type="password" id="confirm_password_reg" name="confirm_password" 
                placeholder="Xác nhận Mật khẩu" required>
                <i class="fas fa-eye toggle-password" 
                onclick="togglePasswordVisibility('confirm_password_reg', this)"></i>
            </div>
            
            <button type="submit" class="btn-primary">Đăng ký</button>
        </form>
        <p class="switch-link">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</main>

<?php 
// FIX: Sử dụng include/footer.php để đồng bộ cấu trúc
include 'includes/footer.php'; 
?>

<script>
function togglePasswordVisibility(fieldId, iconElement) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}
</script>
<script src="js/chat.js"></script> 

</body>
</html>