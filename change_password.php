<?php
// change_password.php

session_start();
// Lưu ý: Không cần require header/footer ở đây vì đây là trang xử lý POST
// Tuy nhiên, nếu bạn muốn hiển thị thông báo lỗi trực tiếp trên trang này, bạn cần include header.

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.";
    header("Location: login.php");
    exit();
}

// 2. CHỈ XỬ LÝ KHI PHƯƠNG THỨC LÀ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php"); // Chuyển hướng nếu truy cập trực tiếp
    exit();
}

// 3. LẤY KẾT NỐI DB VÀ DỮ LIỆU
require_once 'includes/db.php'; // Chứa $conn (MySQLi connection)

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';
$error = '';

// 4. KIỂM TRA DỮ LIỆU ĐẦU VÀO
if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $error = "Vui lòng điền đầy đủ tất cả các trường.";
} elseif ($new_password !== $confirm_new_password) {
    $error = "Mật khẩu mới và xác nhận mật khẩu mới không khớp.";
} elseif (strlen($new_password) < 6) { // Phải khớp với minlength trong form
    $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
}

if ($error) {
    // Lưu lỗi vào session và chuyển hướng lại trang profile
    $_SESSION['error_message'] = $error;
    header('Location: profile.php');
    exit();
}

// 5. LẤY MẬT KHẨU BĂM (HASH) HIỆN TẠI TỪ DB
$sql = "SELECT password_hash FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Lỗi chuẩn bị câu lệnh SQL
    $_SESSION['error_message'] = "Lỗi hệ thống (DB 1). Vui lòng thử lại.";
    header('Location: profile.php');
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Không tìm thấy người dùng (nên không bao giờ xảy ra nếu đã đăng nhập)
    $_SESSION['error_message'] = "Lỗi bảo mật. Không tìm thấy tài khoản.";
    header('Location: profile.php');
    exit();
}

$user = $result->fetch_assoc();
$hashed_password = $user['password_hash'];
$stmt->close();

// 6. XÁC MINH MẬT KHẨU HIỆN TẠI
if (!password_verify($current_password, $hashed_password)) {
    $_SESSION['error_message'] = "Mật khẩu cũ không đúng.";
    header('Location: profile.php');
    exit();
}

// 7. TẠO VÀ CẬP NHẬT MẬT KHẨU BĂM (HASH) MỚI VÀO DB
$new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT); // 

$sql_update = "UPDATE users SET password_hash = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);

if ($stmt_update === false) {
    $_SESSION['error_message'] = "Lỗi hệ thống (DB 2). Vui lòng thử lại.";
    header('Location: profile.php');
    exit();
}

$stmt_update->bind_param("si", $new_hashed_password, $user_id);

if ($stmt_update->execute()) {
    // Cập nhật thành công
    $_SESSION['success_message'] = "Đã đổi mật khẩu thành công!";
    // Tùy chọn: Đăng xuất người dùng để họ đăng nhập lại bằng mật khẩu mới
    // session_destroy(); 
    // header('Location: login.php');
} else {
    // Cập nhật thất bại
    $_SESSION['error_message'] = "Không thể cập nhật mật khẩu. Lỗi cơ sở dữ liệu.";
}

$stmt_update->close();
$conn->close();

// 8. CHUYỂN HƯỚNG VỀ TRANG PROFILE
header('Location: profile.php');
exit();
?>