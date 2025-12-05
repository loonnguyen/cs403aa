<?php
// add_to_cart.php - Xử lý thêm sản phẩm (có Size) vào giỏ hàng
session_start();

// Đảm bảo kết nối database cho các thao tác sau này
require_once __DIR__ . "/includes/db.php"; 

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    // Nếu người dùng CHƯA đăng nhập, chuyển hướng họ đến trang đăng nhập
    $_SESSION['error_message'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.";
    header("Location: login.php");
    exit();
}

// 2. XỬ LÝ THÊM VÀO GIỎ HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'])) {
    // Lấy và làm sạch đầu vào
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    // Lấy size và đảm bảo nó không rỗng
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING) ?: 'M'; 
    // Lấy số lượng, mặc định là 1 nếu không có hoặc không hợp lệ
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1; 

    // Kiểm tra tính hợp lệ tối thiểu của đầu vào
    if ($product_id === false || $product_id <= 0 || $quantity <= 0 || empty($size)) {
        $_SESSION['error_message'] = "Thông tin sản phẩm, kích cỡ hoặc số lượng không hợp lệ.";
        header("Location: index.php");
        exit;
    }
    
    // Khóa duy nhất cho item trong giỏ hàng (ID_SIZE)
    $item_key = $product_id . '_' . $size;

    // 3. LẤY THÔNG TIN SẢN PHẨM TỪ DB (Bảo mật: Prepared Statements)
    global $conn;
    $product_info = null;

    $sql = "SELECT id, name, price, image FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_info = $result->fetch_assoc();
        $stmt->close();
    }
    
    // 4. THAO TÁC VỚI GIỎ HÀNG (SESSION)
    if (!$product_info) {
        $_SESSION['error_message'] = "Sản phẩm không tồn tại trong cơ sở dữ liệu.";
    } else {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Kiểm tra xem item_key (sản phẩm + size) đã tồn tại chưa
        if (isset($_SESSION['cart'][$item_key])) {
            // Tăng số lượng nếu sản phẩm (cùng size) đã tồn tại
            $_SESSION['cart'][$item_key]['quantity'] += $quantity;
            $_SESSION['success_message'] = "Đã cập nhật số lượng " . htmlspecialchars($product_info['name']) . " (Size: " . htmlspecialchars($size) . ") trong giỏ hàng.";
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            $_SESSION['cart'][$item_key] = [
                'product_id' => $product_info['id'],
                'name' => $product_info['name'],
                'price' => $product_info['price'],
                'image' => $product_info['image'],
                'size' => $size,             // LƯU SIZE
                'quantity' => $quantity
            ];
            $_SESSION['success_message'] = "Đã thêm " . $quantity . " sản phẩm " . htmlspecialchars($product_info['name']) . " (Size: " . htmlspecialchars($size) . ") vào giỏ hàng!";
        }
    }
    
    // Đóng kết nối DB và chuyển hướng về trang giỏ hàng
    $conn->close();
    header("Location: cart.php"); 
    exit();
}

// Nếu truy cập trực tiếp mà không có POST data
header("Location: index.php");
exit();
?>