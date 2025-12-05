// js/chat.js - Cập nhật chức năng Chatbot và thêm chức năng Hiện/Ẩn Mật khẩu

let isChatBoxOpen = false;

// --- CHỨC NĂNG CHATBOT ---

function openChat() {
    const chatBox = document.getElementById("chatBox");
    const chatButton = document.getElementById("chatButton");

    if (!chatBox || !chatButton) return; // Bảo vệ nếu các phần tử không tồn tại

    if (isChatBoxOpen) {
        chatBox.style.display = "none";
        chatButton.innerHTML = '<i class="fas fa-comment-dots"></i>'; 
        isChatBoxOpen = false;
    } else {
        chatBox.style.display = "flex"; 
        chatButton.innerHTML = '<i class="fas fa-times"></i>'; 
        isChatBoxOpen = true;
        // Kiểm tra để tránh lỗi nếu input không tồn tại (chỉ nên xảy ra nếu không có chatbot)
        const userInput = document.getElementById("userInput");
        if (userInput) {
            userInput.focus();
        }
    }
}

function sendMessage() {
    const userInput = document.getElementById("userInput");
    const messages = document.getElementById("chatMessages");
    
    if (!userInput || !messages) return;

    let text = userInput.value.trim();
    if (text === "") return; // Chặn gửi tin nhắn rỗng

    // 1. Hiển thị tin nhắn người dùng
    messages.innerHTML += '<div class="message user-message">' + text + '</div>'; 
    messages.scrollTop = messages.scrollHeight;

    userInput.value = "";
    userInput.disabled = true;

    // 2. Hiển thị thông báo đang gõ
    const loadingMessageId = 'loading-' + Date.now();
    messages.innerHTML += `<div id="${loadingMessageId}" class="message bot-message loading-message">Đang gõ...</div>`; 
    messages.scrollTop = messages.scrollHeight;

    // 3. Gọi API Chatbot
    fetch("chatbot/chatbot_api.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({message: text})
    })
    .then(res => res.json())
    .then(data => {
        // Xóa thông báo đang gõ
        const loadingElement = document.getElementById(loadingMessageId);
        if (loadingElement) {
            loadingElement.remove();
        }

        // Hiển thị phản hồi của Bot
        messages.innerHTML += '<div class="message bot-message">' + data.reply + '</div>';
        messages.scrollTop = messages.scrollHeight;
    })
    .catch(error => {
        console.error('Lỗi khi gửi tin nhắn:', error);
        // Xóa thông báo đang gõ
        const loadingElement = document.getElementById(loadingMessageId);
        if (loadingElement) {
            loadingElement.remove();
        }
        // Hiển thị lỗi
        messages.innerHTML += '<div class="message error-message">Xin lỗi, có lỗi kết nối. Vui lòng kiểm tra console log.</div>'; 
        messages.scrollTop = messages.scrollHeight;
    })
    .finally(() => {
        // Hoàn tất và chuẩn bị cho tin nhắn tiếp theo
        userInput.disabled = false;
        userInput.focus();
    });
}

// Lắng nghe sự kiện Enter để gửi tin nhắn
document.addEventListener('DOMContentLoaded', () => {
    const userInput = document.getElementById("userInput");
    if (userInput) {
        userInput.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendMessage();
            }
        });
    }
});



// --- CHỨC NĂNG HIỆN/ẨN MẬT KHẨU ---

/**
 * Hàm chuyển đổi hiển thị mật khẩu
 * @param {string} inputId - ID của trường input (ví dụ: 'password_login')
 * @param {HTMLElement} iconElement - Biểu tượng con mắt được click (tham chiếu 'this')
 */
function togglePasswordVisibility(inputId, iconElement) {
    const passwordInput = document.getElementById(inputId);
    
    if (!passwordInput) return;

    // Chuyển đổi loại input: password <-> text
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Chuyển đổi biểu tượng (mắt mở <-> mắt đóng)
    if (type === 'text') {
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash'); // Mắt đóng (ẩn mật khẩu)
    } else {
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye'); // Mắt mở (hiện mật khẩu)
    }
}