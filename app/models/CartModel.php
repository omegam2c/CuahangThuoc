<?php

require_once __DIR__ . '/BaseModel.php';

class CartModel extends BaseModel {
    /**
     * Kiểm tra user có tồn tại và đang active không.
     */
    public function isValidUser($userId) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE user_id = :user_id AND is_active = 1 LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy giỏ hàng của người dùng (có hỗ trợ merge từ guest session)
     */
    public function getCartByUserId($userId, $sessionId = null) {
        if (!$this->isValidUser($userId)) {
            return null;
        }

        // 1. Tìm giỏ hàng gắn với userId
        $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $userCart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $userCartId = $userCart ? $userCart['cart_id'] : null;

        // 2. Nếu có sessionId, tìm xem có giỏ hàng guest không
        if ($sessionId) {
            $sql = "SELECT cart_id FROM carts WHERE session_id = :session_id AND user_id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            $guestCart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($guestCart) {
                $guestCartId = $guestCart['cart_id'];

                if ($userCartId) {
                    // MERGE: Chuyển items từ guest cart sang user cart
                    $this->mergeCarts($guestCartId, $userCartId);
                    // Xóa guest cart trống
                    $this->db->prepare("DELETE FROM carts WHERE cart_id = :id")->execute(['id' => $guestCartId]);
                } else {
                    // CHUYỂN ĐỔI: Gắn luôn user_id vào guest cart này
                    $sql = "UPDATE carts SET user_id = :user_id, session_id = NULL WHERE cart_id = :cart_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(['user_id' => $userId, 'cart_id' => $guestCartId]);
                    $userCartId = $guestCartId;
                }
            }
        }

        if (!$userCartId) {
            // Tạo giỏ hàng mới nếu chưa có
            $sql = "INSERT INTO carts (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($sql);
            try {
                $stmt->execute(['user_id' => $userId]);
                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                return null;
            }
        }
        
        return $userCartId;
    }

    /**
     * Lấy giỏ hàng cho khách vãng lai
     */
    public function getCartBySessionId($sessionId) {
        $sql = "SELECT cart_id FROM carts WHERE session_id = :session_id AND user_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['session_id' => $sessionId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $sql = "INSERT INTO carts (session_id) VALUES (:session_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            return $this->db->lastInsertId();
        }

        return $cart['cart_id'];
    }

    /**
     * Gộp 2 giỏ hàng
     */
    private function mergeCarts($fromCartId, $toCartId) {
        // Lấy items từ giỏ cũ
        $items = $this->getCartItems($fromCartId);
        foreach ($items as $item) {
            $this->addItem($toCartId, $item['product_id'], $item['quantity']);
        }
        // Xóa items ở giỏ cũ
        $this->db->prepare("DELETE FROM cart_items WHERE cart_id = :id")->execute(['id' => $fromCartId]);
    }
    
    /**
     * Lấy danh sách sản phẩm trong giỏ hàng
     */
    public function getCartItems($cartId) {
        $sql = "SELECT ci.*, p.product_name, p.price, p.image_url, p.discount_percent, p.is_prescription_required,
                       ROUND(p.price * (100 - p.discount_percent) / 100) as current_price
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                WHERE ci.cart_id = :cart_id AND p.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cart_id' => $cartId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addItem($cartId, $productId, $quantity = 1) {
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $sql = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cart_id' => $cartId, 'product_id' => $productId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Cập nhật số lượng
            $sql = "UPDATE cart_items SET quantity = quantity + :qty WHERE cart_item_id = :item_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['qty' => $quantity, 'item_id' => $item['cart_item_id']]);
        } else {
            // Thêm mới
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :qty)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['cart_id' => $cartId, 'product_id' => $productId, 'qty' => $quantity]);
        }
    }
    
    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeItem($cartId, $productId) {
        $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['cart_id' => $cartId, 'product_id' => $productId]);
    }
    
    /**
     * Cập nhật số lượng
     */
    public function updateQuantity($cartId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartId, $productId);
        }
        $sql = "UPDATE cart_items SET quantity = :qty WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['qty' => $quantity, 'cart_id' => $cartId, 'product_id' => $productId]);
    }
    
    /**
     * Kiểm tra tương tác thuốc trong giỏ hàng
     */
    public function checkCartInteractions($cartId) {
        $sql = "CALL check_cart_interactions(:cart_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cart_id' => $cartId]);
        $interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $interactions;
    }
}
