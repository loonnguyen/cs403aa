<?php

session_start(); 
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 

// 1. Tải Khóa API và Kết nối DB
require_once __DIR__ . '/../includes/config.php'; 
require_once __DIR__ . '/../includes/db.php'; // Kết nối cơ sở dữ liệu

// Lấy User ID đã đăng nhập
$user_id = $_SESSION['user_id'] ?? null;

// --- HÀM LƯU LỊCH SỬ CHAT (GIỮ NGUYÊN) ---
/**
 * Lưu tin nhắn vào bảng chat_history.
 * Chỉ hoạt động nếu user_id tồn tại.
 */
function save_chat_message($user_id, $sender, $message) {
    global $conn;
    
    // Nếu không có kết nối DB hoặc không có user_id, bỏ qua việc lưu
    if (!$conn || !$user_id) {
        return false; 
    }
    
    // Dùng Prepared Statement
    $stmt = $conn->prepare("INSERT INTO chat_history (user_id, sender, message) VALUES (?, ?, ?)");
    
    // Dọn dẹp message (chặn XSS/HTML) trước khi lưu vào DB
    $clean_message = strip_tags($message); 
    $stmt->bind_param("iss", $user_id, $sender, $clean_message);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
// --- KẾT THÚC HÀM LƯU LỊCH SỬ CHAT ---


// =================================================================
// 🆕 PHẦN BỔ SUNG: HÀM TOOL TRUY VẤN SẢN PHẨM TỪ DATABASE
// =================================================================

/**
 * Tìm kiếm chi tiết sản phẩm (giá, size, mô tả) theo tên sản phẩm.
 * Đây là "Tool" mà mô hình AI sẽ gọi (Function Calling).
 * @param string $product_name Tên sản phẩm cần tìm.
 * @return string JSON string chứa danh sách sản phẩm tìm thấy.
 */
function find_product_details_by_name($product_name) {
    global $conn;
    
    if (!$conn) {
        return json_encode(['error' => 'Database connection failed']);
    }

    // Tối ưu hóa tìm kiếm bằng LIKE (tìm kiếm một phần tên)
    $search_term = "%" . trim($product_name) . "%";
    
    // Giả định bảng sản phẩm của bạn là 'products'
    $sql = "SELECT name, price, description, size, category FROM products WHERE name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return json_encode(['error' => 'SQL prepare failed: ' . $conn->error]);
    }
    
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $products_found = [];

    while ($row = $result->fetch_assoc()) {
        $products_found[] = $row;
    }

    $stmt->close();
    
    if (empty($products_found)) {
        return json_encode(['status' => 'success', 'data' => [], 'message' => "Không tìm thấy sản phẩm nào khớp với tên: " . $product_name]);
    }

    // Trả về kết quả dưới dạng JSON
    return json_encode(['status' => 'success', 'data' => $products_found]);
}
// =================================================================


// 2. Kiểm tra Khóa API
if (empty(OPENAI_API_KEY) || strpos(OPENAI_API_KEY, 'KHÓA_API_THẬT_CỦA_BẠN') !== false) {
    echo json_encode(["reply" => "Lỗi: Khóa API chưa được thiết lập đúng cách."]);
    exit;
}

// 3. Chuẩn bị Dữ liệu
$data = json_decode(file_get_contents("php://input"), true);
$user_message = $data["message"] ?? '';

if (empty($user_message)) {
    echo json_encode(["reply" => "Lỗi: Tin nhắn trống."]);
    exit;
}

// 4. LƯU TIN NHẮN USER TRƯỚC KHI GỌI API
if ($user_id) {
    save_chat_message($user_id, 'user', $user_message);
}


// 5. System Prompt, Tool Declaration và Payload cho Gemini API

$system_instruction = "Bạn là ClothBot, một chuyên gia tư vấn thời trang, phong cách và size quần áo cho shop ABC. Bạn có kiến thức về FAQ, chính sách, và sản phẩm của shop.

NGUYÊN TẮC VÀ YÊU CẦU ĐÁP ỨNG TEST CASE:
1. LƯU Ý BẢO MẬT (TC-SEC01 - XSS): Phản hồi của bạn CHỈ ĐƯỢC là **văn bản thuần túy (plain text)**. TUYỆT ĐỐI KHÔNG chứa HTML, Javascript, hoặc bất kỳ đoạn mã nào.
2. PHÂN LOẠI INTENT/FAQ (TC-NLP & TC-F):
    - **Đa ngôn ngữ:** Xử lý các câu hỏi về FAQ (chính sách, giờ làm, sản phẩm) bằng **tiếng Việt và tiếng Anh** (TC-F04-06).
    - **Xử lý lỗi:** Phải cố gắng nhận diện ý định (Intent) của khách hàng ngay cả khi có **lỗi chính tả** ('Đôi mk khẩu') hoặc **sai cấu trúc câu** (' muốn Tôi thanh toán') (TC-NLP02, TC-NLP06, TC-NLP07).
    - **Intent hỗn hợp:** Đối với các câu hỏi có nhiều ý định, ví dụ: 'Giờ làm việc và địa chỉ?', hãy trả lời **cả hai thành phần** một cách rõ ràng (TC-NLP03, TC-NLP08).
    - **Fallback:** Nếu câu hỏi không nằm trong phạm vi kiến thức, hãy đưa ra câu trả lời **'fallback' chuẩn mực** (TC-F07).
3. TƯ VẤN STYLE:
    - Luôn đề xuất trang phục giúp che khuyết điểm và tôn ưu điểm cơ thể.
    - Nếu người dùng hỏi về cân nặng/size, hãy hỏi thêm về chiều cao để tính BMI và đề xuất size gần đúng.
    - Luôn trả lời ngắn gọn, tập trung vào sản phẩm (ví dụ: Áo thun cổ V, Quần jean ống đứng).
    - **QUAN TRỌNG:** LUÔN LUÔN sử dụng công cụ **'find_product_details_by_name'** khi người dùng hỏi về giá, size, tồn kho, hoặc chi tiết của một sản phẩm cụ thể.";


// 🆕 PHẦN BỔ SUNG: KHAI BÁO TOOL TRONG PAYLOAD
$tools = [
    [
        'functionDeclarations' => [
            'name' => 'find_product_details_by_name',
            'description' => 'Tìm kiếm chi tiết (giá, size, mô tả) của các sản phẩm trong cửa hàng theo tên. Chỉ sử dụng khi người dùng hỏi về sản phẩm, giá, hoặc tồn kho.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product_name' => [
                        'type' => 'string',
                        'description' => 'Tên đầy đủ hoặc một phần của sản phẩm (ví dụ: "Áo thun Classic Đen" hoặc chỉ "Jean").',
                    ],
                ],
                'required' => ['product_name'],
            ],
        ],
    ],
];

// Payload cho API lần 1
$request_payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $user_message]
            ]
        ]
    ],
    "model" => CHATBOT_MODEL, 
    "systemInstruction" => [
        "parts" => [
            ["text" => $system_instruction]
        ]
    ],
    // 🆕 THÊM TOOLS VÀO PAYLOAD
    "tools" => $tools,
];

// Định nghĩa URL và hàm thực hiện cURL để tái sử dụng
$api_url = "https://generativelanguage.googleapis.com/v1beta/models/" . CHATBOT_MODEL . ":generateContent?key=" . OPENAI_API_KEY;

function execute_curl($url, $payload) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return ['response' => $response, 'http_code' => $http_code];
}

// 6. Thực hiện cURL để gọi Gemini API (Lần 1)
$curl_result = execute_curl($api_url, $request_payload);
$response = $curl_result['response'];
$http_code = $curl_result['http_code'];

// 7. Xử lý phản hồi API
if ($http_code !== 200) {
    $error_message = "Lỗi HTTP: " . $http_code . ". ";
    $error_details = json_decode($response, true);
    if (isset($error_details['error']['message'])) {
        $error_message .= "Chi tiết: " . $error_details['error']['message'];
    } else {
        $error_message .= "Phản hồi lỗi không xác định. Có thể do Khóa API chưa được kích hoạt.";
    }
    
    $reply_text = "Lỗi Kết nối API: " . $error_message;
    echo json_encode(["reply" => $reply_text]);
    exit;
}

$response_data = json_decode($response, true);
$reply_text = "Xin lỗi, tôi không hiểu. Vui lòng thử lại."; 

// =================================================================
// 🆕 PHẦN BỔ SUNG: XỬ LÝ TOOL CALLING
// =================================================================
$final_response_data = $response_data;

if (isset($response_data['candidates'][0]['content']['parts'][0]['functionCall'])) {
    // Gemini yêu cầu gọi một hàm (Tool Calling)
    $call_request = $response_data['candidates'][0]['content']['parts'][0]['functionCall'];
    $function_name = $call_request['name'] ?? null;
    
    if ($function_name === 'find_product_details_by_name') {
        $args = json_decode(json_encode($call_request['args']), true);
        $product_name = $args['product_name'] ?? '';
        
        // 1. Thực thi hàm PHP để lấy dữ liệu từ DB
        $tool_output = find_product_details_by_name($product_name);
        
        // 2. Xây dựng lại lịch sử hội thoại để gửi lại cho Gemini
        // Bắt buộc phải gửi lại System Instruction, User Message và Tool Response
        $second_request_payload = [
            "contents" => [
                // Gửi lại tin nhắn người dùng
                [
                    'role' => 'user', 
                    'parts' => [["text" => $user_message]]
                ],
                // Gửi phản hồi API lần 1 (yêu cầu gọi tool)
                [
                    'role' => 'model', 
                    'parts' => [
                        ['functionCall' => $call_request]
                    ]
                ],
                // Gửi phản hồi của Tool cho Gemini xử lý
                [
                    'role' => 'tool',
                    'parts' => [[
                        'functionResponse' => [
                            'name' => $function_name,
                            // Gửi output của hàm PHP (kết quả DB)
                            'response' => json_decode($tool_output, true), 
                        ]
                    ]]
                ]
            ],
            "model" => CHATBOT_MODEL,
            "tools" => $tools,
            "systemInstruction" => [
                "parts" => [
                    ["text" => $system_instruction]
                ]
            ],
        ];
        
        // 3. Thực hiện cURL để gọi Gemini API Lần 2
        $curl_result_2 = execute_curl($api_url, $second_request_payload);
        $http_code_2 = $curl_result_2['http_code'];

        if ($http_code_2 === 200) {
            $final_response_data = json_decode($curl_result_2['response'], true);
        } else {
            // Xử lý lỗi API lần 2
            $reply_text = "Lỗi Kết nối API trong quá trình xử lý dữ liệu sản phẩm. Vui lòng thử lại sau.";
            // Không exit, vẫn tiếp tục để lưu log lỗi nếu user_id tồn tại
        }
    }
}

// Lấy văn bản phản hồi cuối cùng
if (isset($final_response_data['candidates'][0]['content']['parts'][0]['text'])) {
    $reply_text = $final_response_data['candidates'][0]['content']['parts'][0]['text'];
} else if (isset($final_response_data['error'])) {
    $reply_text = "Lỗi API: " . $final_response_data['error']['message'];
}
// Nếu không có 'text' nào và không có lỗi, reply_text giữ nguyên giá trị fallback "Xin lỗi, tôi không hiểu..."
// =================================================================


// 8. LƯU TIN NHẮN BOT
if ($user_id) {
    save_chat_message($user_id, 'bot', $reply_text);
}

// 9. Trả về phản hồi cho JS (Client)
echo json_encode([
    "reply" => $reply_text
]);

// Đóng kết nối DB
if (isset($conn)) {
    $conn->close();
}
?>