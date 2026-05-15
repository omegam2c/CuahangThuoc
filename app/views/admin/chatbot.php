<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
/* ── Chatbot Layout ── */
.chatbot-wrap {
    display: flex;
    flex-direction: column;
    height: 70vh;
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
    overflow: hidden;
    border: 1px solid var(--border);
}

/* Header trang Chat */
.chat-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px 25px;
    background: linear-gradient(135deg, #00904a 0%, var(--blue) 100%);
    color: #fff;
}
.chat-header-avatar {
    width: 45px; height: 45px;
    border-radius: 12px;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
}
.chat-header-info h3 { margin: 0; font-size: 16px; font-weight: 600; color: #fff;}
.chat-header-info p  { margin: 2px 0 0; font-size: 13px; opacity: .8; }

/* Gợi ý câu hỏi nhanh (Chips) */
.chat-chips {
    display: flex;
    overflow-x: auto;
    gap: 10px;
    padding: 15px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}
.chat-chips::-webkit-scrollbar { height: 4px; }
.chat-chips::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.chip {
    padding: 8px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    font-size: 13px;
    color: #334155;
    background: #fff;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.chip:hover { 
    background: #00904a; 
    color: #fff; 
    border-color: #00904a;
    transform: translateY(-2px);
}

/* Vùng hiển thị tin nhắn */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    background: #ffffff;
}
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

/* Bong bóng chat */
.msg { display: flex; align-items: flex-start; gap: 12px; max-width: 85%; }
.msg.user  { align-self: flex-end; flex-direction: row-reverse; }
.msg.bot   { align-self: flex-start; }

.bubble {
    padding: 12px 18px;
    border-radius: 18px;
    font-size: 14.5px;
    line-height: 1.6;
    word-break: break-word;
}

/* Chat User */
.msg.user .bubble {
    background: #2563eb;
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

/* Chat Bot */
.msg.bot .bubble {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
}

/* Typing indicator */
.typing { display: flex; gap: 5px; padding: 12px 18px; background: #f1f5f9; border-radius: 18px; width: fit-content; }
.typing span { width: 8px; height: 8px; background: #94a3b8; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out; }
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-8px); } }

/* Thanh nhập liệu */
.chat-input-area {
    padding: 20px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 12px;
    align-items: flex-end;
}
#chatInput {
    flex: 1;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    resize: none;
    max-height: 150px;
    transition: border-color .2s;
}
#chatInput:focus { border-color: var(--primary); }

.send-btn {
    width: 48px; height: 48px;
    background: #00904a;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    transition: all .2s;
}
.send-btn:hover { background: var(--green); transform: scale(1.05); }
.send-btn:disabled { background: #cbd5e1; cursor: not-allowed; }
</style>

<div class="container section animate-fade-up">
    <h1 class="section-title" style="margin-bottom: 0;">Trợ lý AI Quản trị</h1>
    
    <div class="chatbot-wrap">
        <div class="chat-header">
            <div class="chat-header-avatar">🤖</div>
            <div class="chat-header-info">
                <h3>Trợ lý AI Nhà thuốc 1985</h3>
                <p>Đang trực tuyến - Hỏi tôi về tồn kho & doanh thu</p>
            </div>
        </div>

        <div class="chat-chips">
            <div class="chip" onclick="quickSend('Kiểm tra tồn kho')">📦 Tổng tồn kho</div>
            <div class="chip" onclick="quickSend('Doanh thu hôm nay là bao nhiêu?')">💰 Doanh thu hôm nay</div>
            <div class="chip" onclick="quickSend('Sản phẩm nào đã hết hàng?')">📉 Hết hàng</div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="msg bot">
                <div class="bubble">Xin chào Quản trị viên! Tôi là trợ lý AI của <strong>Nhà thuốc 1985</strong>. Tôi có quyền truy cập trực tiếp vào hệ thống dữ liệu để hỗ trợ bạn tra cứu tồn kho, doanh thu và đơn hàng một cách nhanh nhất. <br><br>Hôm nay bạn cần tôi báo cáo thông tin gì không?</div>
            </div>
        </div>

        <div class="chat-input-area">
            <textarea id="chatInput" placeholder="Nhập câu hỏi tại đây..." rows="1" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
            <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
        </div>
    </div>
</div>

<script>
const messagesContainer = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');

// Hàm gửi câu hỏi từ Chip
function quickSend(text) {
    chatInput.value = text;
    sendMessage();
}

// Hàm thêm tin nhắn vào màn hình
function appendMessage(role, content) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `msg ${role}`;
    
    const bubbleContent = role === 'bot' ? content.replace(/\n/g, '<br>') : escapeHTML(content).replace(/\n/g, '<br>');
    
    msgDiv.innerHTML = `
        ${role === 'bot' ? '<div class="chat-header-avatar" style="width:30px;height:30px;font-size:16px;background:#e2e8f0;color:var(--primary);align-self:flex-end;">🤖</div>' : ''}
        <div class="bubble">${bubbleContent}</div>
    `;
    
    messagesContainer.appendChild(msgDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showTyping() {
    const typingDiv = document.createElement('div');
    typingDiv.className = 'msg bot';
    typingDiv.id = 'typing-status';
    typingDiv.innerHTML = '<div class="typing"><span></span><span></span><span></span></div>';
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function hideTyping() {
    const el = document.getElementById('typing-status');
    if (el) el.remove();
}

function escapeHTML(str) {
    const p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
}

// Hàm chính gửi tin nhắn
async function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    chatInput.value = '';
    chatInput.style.height = 'auto';
    chatInput.disabled = true;
    sendBtn.disabled = true;

    appendMessage('user', message);
    showTyping();

    try {
        const response = await fetch('<?= BASE_URL ?>admin/chatbot/api', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken
            },
            body: JSON.stringify({ message: message })
        });

        const data = await response.json();
        hideTyping();

        appendMessage('bot', data.reply || 'Xin lỗi, tôi gặp chút trục trặc khi kết nối dữ liệu.');

    } catch (error) {
        hideTyping();
        appendMessage('bot', '❌ Lỗi kết nối server.');
    } finally {
        chatInput.disabled = false;
        sendBtn.disabled = false;
        chatInput.focus();
    }
}

chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
