// public/js/rtc_call.js
document.addEventListener('DOMContentLoaded', () => {
    // Requires PeerJS script included in HTML
    if (typeof Peer === 'undefined') {
        console.error("Thiếu thư viện PeerJS. Vui lòng kiểm tra CDN trong HTML.");
        return;
    }

    const currentUserId = window.CURRENT_USER_ID;
    
    // Khởi tạo PeerJS kết nối WebRTC bằng target IDs
    // Khuyến nghị: Prefix ID để tránh đụng độ
    const peer = new Peer('user-' + currentUserId, {
        debug: 2 // Log để tiện phát triển
    });

    const videoModal = document.getElementById('videoCallModal');
    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    const startCallBtn = document.getElementById('startCallBtn');
    const endCallBtn = document.getElementById('endCallBtn');

    let localStream = null;
    let currentCall = null;

    peer.on('open', function(id) {
        console.log('Khởi tạo PeerJS thành công với ID: ' + id);
    });

    // Lắng nghe lúc đối tác gọi đến mình
    peer.on('call', function(call) {
        if (confirm("Bạn có cuộc gọi tư vấn. Nhấn OK để nghe?")) {
            navigator.mediaDevices.getUserMedia({video: true, audio: true})
            .then(function(stream) {
                localStream = stream;
                localVideo.srcObject = stream;
                localVideo.play();
                
                call.answer(stream); // Trả lời gửi kèm stream cam/mic của mình
                setupCallEvents(call);
                videoModal.style.display = 'flex';
            })
            .catch(function(err) {
                console.error('Không thể lấy quyền truy cập Camera/Mic' ,err);
                alert("Lỗi phân quyền Camera/Mic");
            });
        }
    });

    // Chủ động bắt đầu cuộc gọi (ví dụ gọi Bác sĩ)
    if (startCallBtn) {
        startCallBtn.addEventListener('click', () => {
            const partnerId = window.PARTNER_USER_ID;
            if (!partnerId) {
                alert("Lưu ý: Không tìm thấy Đối tác hiện tại.");
                return;
            }

            navigator.mediaDevices.getUserMedia({video: true, audio: true})
            .then(function(stream) {
                localStream = stream;
                localVideo.srcObject = stream;
                localVideo.play();
                
                videoModal.style.display = 'flex';
                // Kết nối tới target Peer ID
                const call = peer.call('user-' + partnerId, stream);
                setupCallEvents(call);
            })
            .catch(function(err) {
                console.error('Không thể lấy quyền truy cập Camera/Mic' ,err);
            });
        });
    }

    // Nút tắt
    if (endCallBtn) {
        endCallBtn.addEventListener('click', () => {
            if (currentCall) {
                currentCall.close();
            }
            closeMedia();
        });
    }

    // Hàm thiết lập event call
    function setupCallEvents(call) {
        currentCall = call;
        
        // Nhận stream của đối tác
        call.on('stream', function(remoteStream) {
            remoteVideo.srcObject = remoteStream;
            remoteVideo.play();
        });
        
        // Nếu đối tác tắt gọi
        call.on('close', function() {
            closeMedia();
        });
    }

    function closeMedia() {
        videoModal.style.display = 'none';
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        remoteVideo.srcObject = null;
        localVideo.srcObject = null;
        currentCall = null;
    }
});
