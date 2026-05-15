<?php
// app/models/UserModel.php
require_once 'app/models/BaseModel.php';

class UserModel extends BaseModel {
    public function checkEmailExists($email) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ? true : false;
    }

    public function checkUsernameExists($username) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ? true : false;
    }

    public function createUser($data) {
        // Mặc định role_id 4 = Khách hàng (theo SQL mẫu)
        return $this->createUserWithRole($data, 4);
    }

    public function createUserWithRole($data, $roleId) {
        $sql = "INSERT INTO users (username, password_hash, full_name, email, phone, address, role_id) 
                VALUES (:username, :password_hash, :full_name, :email, :phone, :address, :role_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $data['username'],
            'password_hash' => $data['password_hash'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? '',
            'role_id' => $roleId
        ]);
    }

    public function getUserByEmailOrUsername($credential) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :cred OR username = :cred LIMIT 1");
        $stmt->execute(['cred' => $credential]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cập nhật thời gian đăng nhập cuối cùng
     */
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
        return $stmt->execute(['id' => $userId]);
    }
    
    // ==========================================
    // CÁC HÀM XỬ LÝ QUÊN MẬT KHẨU (PASSWORD RESET)
    // ==========================================

    // Lưu mã OTP vào bảng password_resets
    public function saveOTP($email, $otp_code) {
        // Cập nhật lại ngày hết hạn là 15 phút từ lúc gởi
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Xóa các OTP cũ của email này trước khi thêm mới
        $del = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
        $del->execute([$email]);

        // Lưu mới
        $sql = "INSERT INTO password_resets (email, otp_code, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $otp_code, $expiresAt]);
    }

    // Kiểm tra tính hợp lệ của OTP
    public function verifyOTP($email, $otp_code) {
        $sql = "SELECT * FROM password_resets 
                WHERE email = ? AND otp_code = ? AND expires_at > NOW() 
                ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $otp_code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    /**
     * Kiểm tra quyền của người dùng (RBAC) (Ref point 🚨)
     */
    public function hasPermission($userId, $permissionName) {
        $sql = "SELECT COUNT(*) 
                FROM users u
                JOIN role_permissions rp ON u.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.permission_id
                WHERE u.user_id = :user_id AND p.permission_name = :perm";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'perm' => $permissionName]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Cập nhật mật khẩu mới và xóa mã OTP đã dùng
    public function updatePassword($email, $new_password_hash) {
        try {
            $this->db->beginTransaction();
            
            // 1. Cập nhật user
            $sql = "UPDATE users SET password_hash = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$new_password_hash, $email]);

            // 2. Xóa OTP
            $del = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->execute([$email]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}