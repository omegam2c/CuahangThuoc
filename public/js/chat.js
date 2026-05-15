// public/js/chat.js
document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo Pusher
    // Lưu ý: Cần thêm Pusher JS qua thẻ <script> từ CDN trong HTML và truyền các biến môi trường
    if (typeof PUSHER_KEY === 'undefined') {
        console.error("Cần cấu hình PUSHER_KEY trong file giao diện.");
        return;
    }

    const pusher = new Pusher(PUSHER_KEY, {
        cluster: PUSHER_CLUSTER,
        authEndpoint: (window.BASE_URL || '/') + 'api/pusher_auth.php'
    });

    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    
    // CURRENT_CONVERSATION_ID và CURRENT_USER_ID lấy từ backend (truyền vào HTML)
    const conversationId = window.CURRENT_CONVERSATION_ID;
    const currentUserId = window.CURRENT_USER_ID;

    // Kênh subscribe
    if (conversationId) {
        const channel = pusher.subscribe('private-chat-' + conversationId);
        
        channel.bind('new-message', function(data) {
            // Kiểm tra xem tin nhắn có phải do mình bị lặp không
            if (data.sender_id != currentUserId) {
                appendMessage(data.content, 'received');
            }
        });
        
        // --- XỬ LÝ LẮNG NGHE KHÔNG GIAN BỊ HỦY BỞI ĐỐI TÁC ---
        channel.bind('conversation-ended', function(data) {
            alert("Đã kết thúc quá trình tư vấn. Hệ thống tự động chuyển hướng làm việc...");
            window.location.href = window.THE_HOME_URL || (window.BASE_URL || '/');
        });

        // --- XỬ LÝ KHI DƯỢC SĨ THAM GIA ---
        channel.bind('doctor-joined', function(data) {
            // Đổi Avatar & Tên
            const headers = document.querySelectorAll('.chat-sidebar .conversation-info h3, .chat-header h3');
            headers.forEach(h => h.textContent = "BS. " + data.doctor_name);
            
            const avatars = document.querySelectorAll('.chat-sidebar .avatar, .chat-header .avatar');
            avatars.forEach(av => av.textContent = data.doctor_name.charAt(0).toUpperCase());
            
            // Ẩn nút tìm bác sĩ nếu đang có
            const findBtn = document.getElementById('findDoctorBtn');
            if(findBtn) findBtn.style.display = 'none';

            // Thông báo
            appendMessage("Dược sĩ " + data.doctor_name + " đã tham gia cuộc trò chuyện.", "received");
        });

        // Tải tin nhắn cũ
        loadMessages();
    }

    // Nút Kết Nối Dược Sĩ
    const findDoctorBtn = document.getElementById('findDoctorBtn');
    if (findDoctorBtn) {
        findDoctorBtn.addEventListener('click', function() {
            this.textContent = "Đang tìm kiếm...";
            this.disabled = true;
            this.style.opacity = '0.7';

            fetch((window.BASE_URL || '/') + 'api/handle_request.php', { 
                method: 'POST', 
                body: new URLSearchParams({action: 'start'}) 
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    appendMessage("Yêu cầu của bạn đang được chuyển đến Dược sĩ trực tuyến. Vui lòng đợi trong giây lát...", "received");
                    
                    // Lắng nghe trạng thái từ kênh khách hàng
                    const customerChannel = pusher.subscribe('private-customer-' + currentUserId);
                    customerChannel.bind('request-status', function(resData) {
                        if (resData.status === 'exhausted') {
                            appendMessage("Hiện tại tất cả Dược sĩ đều đang bận. Vui lòng thử lại sau.", "received");
                            findDoctorBtn.textContent = "Kết nối Dược sĩ";
                            findDoctorBtn.disabled = false;
                            findDoctorBtn.style.opacity = '1';
                        }
                    });

                } else {
                    alert(data.error || "Gặp sự cố, vui lòng thử lại.");
                    this.textContent = "Kết nối Dược sĩ";
                    this.disabled = false;
                    this.style.opacity = '1';
                }
            });
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function loadMessages() {
        fetch((window.BASE_URL || '/') + `api/get_messages.php?conversation_id=${conversationId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    chatMessages.innerHTML = ''; // reset
                    data.messages.forEach(msg => {
                        const type = msg.sender_id == currentUserId ? 'sent' : 'received';
                        appendMessage(msg.content, type);
                    });
                }
            })
            .catch(err => console.error("Lỗi khi tải tin nhắn: ", err));
    }

    function appendMessage(content, type) {
        const row = document.createElement('div');
        row.className = `message-row ${type}`;
        
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        bubble.textContent = content; // textContent tự escape HTML (an toàn XSS)
        
        row.appendChild(bubble);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text || !conversationId) return;

        chatInput.value = '';
        appendMessage(text, 'sent'); // Cập nhật UI nhanh

        const formData = new FormData();
        formData.append('conversation_id', conversationId);
        formData.append('content', text);

        fetch((window.BASE_URL || '/') + 'api/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error("Gửi lỗi:", data.error);
                // Có thể xử lý logic xóa tin nhắn ảo nếu gửi thất bại
            }
        })
        .catch(err => console.error("Disconnect or error:", err));
    }
});

// Hàm gọi ra từ HTML nút bấm Kết Thúc Tư Vấn
window.endConsultation = function() {
    if (!confirm("Bạn xác nhận kết thúc chứ?")) return;
    
    fetch((window.BASE_URL || '/') + 'api/end_consult.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ conversation_id: window.CURRENT_CONVERSATION_ID })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            window.location.href = window.THE_HOME_URL || (window.BASE_URL || '/');
        } else {
            alert(data.error || "Có lỗi xảy ra khi kết thúc.");
        }
    });
};
