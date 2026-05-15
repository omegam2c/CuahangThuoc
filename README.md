# Pharmacy Management System (Nhà thuốc 1985)

Hệ thống quản lý nhà thuốc toàn diện với các tính năng dành cho Khách hàng, Dược sĩ và Quản trị viên.

## 🚀 Tính năng chính
- **Khách hàng**: Tìm kiếm sản phẩm, đặt hàng, gửi đơn thuốc online, chat với dược sĩ.
- **Dược sĩ**: Phê duyệt đơn thuốc, kê đơn trực tuyến, tư vấn khách hàng.
- **Quản trị viên**: Quản lý kho dược (FEFO/Hạn dùng), báo cáo doanh thu AI, nhật ký hệ thống, quản lý người dùng & phân quyền.

## 🛠️ Công nghệ sử dụng
- **Backend**: PHP (MVC Architecture)
- **Database**: MySQL 8.0
- **Security**: CSRF Protection, Password Hashing (Bcrypt), Audit Logging.
- **Integrations**: PHPMailer, Google Gemini AI (Chatbot Phân tích), PayOS/VNPay.

## 📦 Hướng dẫn cài đặt nhanh (Local)
1. Clone repository.
2. Cấu hình `.env` từ `.env.example`.
3. Chạy `composer install`.
4. Import database từ thư mục `SQL/`.
5. Mở bằng XAMPP hoặc chạy `php -S localhost:8000 index.php`.

## 🌐 Triển khai (Cloud)
Hệ thống đã được cấu hình sẵn cho **Railway.app**. Chỉ cần kết nối repository và thêm dịch vụ MySQL.

## 🛡️ Bảo mật & Bảo trì
- Nhật ký hệ thống ghi lại mọi hành động nhạy cảm.
- Quy trình xuất kho tuân thủ nguyên tắc FEFO (Hết hạn trước - Xuất trước).
- Ảnh đơn thuốc được bảo vệ và chỉ truy cập được bởi người có thẩm quyền.

---
© 2026 Nhà thuốc 1985. All rights reserved.
