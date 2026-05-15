<?php
require_once __DIR__ . '/../config/app_config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giả lập SePay Webhook</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f7f6; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        #result { margin-top: 20px; padding: 10px; border-radius: 5px; display: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🚀 Giả lập SePay</h2>
        <p>Nhập mã đơn hàng để xác nhận thanh toán (Dùng cho Demo)</p>
        <input type="text" id="orderId" placeholder="Ví dụ: 25">
        <button onclick="simulate()">Xác nhận thanh toán ngay</button>
        <div id="result"></div>
    </div>

    <script>
        function simulate() {
            const orderId = document.getElementById('orderId').value;
            if (!orderId) return alert('Vui lòng nhập ID đơn hàng');

            const payload = {
                content: "DH" + orderId,
                transferAmount: 100000,
                id: Math.floor(Math.random() * 1000000)
            };

            fetch('/Pharmacy/webhook/sepay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Apikey MFAKZJOVRLEQPWTXS9W9DJ7JIUBEDGUTLBYR3PC3FATRYRKHK1J5TL5IXYMSV8G6'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                const resDiv = document.getElementById('result');
                resDiv.style.display = 'block';
                resDiv.style.background = '#d4edda';
                resDiv.style.color = '#155724';
                resDiv.innerHTML = '✅ Đã gửi tín hiệu! Hãy kiểm tra trang Success của đơn hàng #' + orderId;
            })
            .catch(err => {
                alert('Lỗi: ' + err);
            });
        }
    </script>
</body>
</html>
