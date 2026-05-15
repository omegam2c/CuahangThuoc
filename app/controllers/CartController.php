<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/CartModel.php';
require_once __DIR__ . '/../Models/ProductModel.php';

class CartController extends BaseController {
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->cartModel = new CartModel();
        $this->productModel = new ProductModel();
    }
    
    /**
     * Xem giỏ hàng
     */
    public function index() {
        $cartItems = [];
        $total = 0;
        $interactions = [];
        
        if (isset($_SESSION['user_id'])) {
            $cartId = $this->cartModel->getCartByUserId($_SESSION['user_id'], session_id());
            if ($cartId) {
                $cartItems = $this->cartModel->getCartItems($cartId);
                // Kiểm tra tương tác thuốc
                $interactions = $this->cartModel->checkCartInteractions($cartId);
            } else {
                // Session user không còn hợp lệ trong DB -> logout mềm
                unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name'], $_SESSION['role_id'], $_SESSION['user']);
            }
        } else {
            // Xử lý giỏ hàng Guest trong Database
            $cartId = $this->cartModel->getCartBySessionId(session_id());
            if ($cartId) {
                $cartItems = $this->cartModel->getCartItems($cartId);
            }
        }
        
        // Tính tổng tiền
        foreach ($cartItems as $item) {
            $total += ($item['current_price'] ?? $item['price']) * $item['quantity'];
        }
        
        $this->loadView('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'interactions' => $interactions,
            'pageTitle' => 'Giỏ hàng - Nhà thuốc 1985'
        ]);
    }
    
    /**
     * Thêm vào giỏ (Normal POST)
     */
    public function add() {
        $productId = $_POST['product_id'] ?? null;
        $qty = (int)($_POST['quantity'] ?? 1);
        
        if (!$productId) {
            $this->redirect(BASE_URL);
        }

        // Kiểm tra sản phẩm tồn tại và đang kinh doanh
        $product = $this->productModel->getProductById($productId);
        if (!$product || !$product['is_active']) {
            $_SESSION['error_message'] = 'Sản phẩm không khả dụng hoặc đã bị ẩn.';
            $this->redirect(BASE_URL);
        }
        
        if (isset($_SESSION['user_id'])) {
            $cartId = $this->cartModel->getCartByUserId($_SESSION['user_id'], session_id());
            if ($cartId) {
                $this->cartModel->addItem($cartId, $productId, $qty);
            }
        } else {
            // Khách vãng lai - Lưu vào DB với Session ID
            $cartId = $this->cartModel->getCartBySessionId(session_id());
            $this->cartModel->addItem($cartId, $productId, $qty);
        }
        
        $_SESSION['success_message'] = 'Đã thêm vào giỏ hàng thành công!';
        $this->redirect(BASE_URL . 'cart');
    }
    
    /**
     * API: Thêm vào giỏ (AJAX)
     */
    public function addAjax() {
        $this->validateCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? $_POST['product_id'] ?? null;
        $qty = (int)($input['quantity'] ?? $_POST['quantity'] ?? 1);
        
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product || !$product['is_active']) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không khả dụng hoặc đã bị ẩn.']);
        }
        
        $cartId = null;
        if (isset($_SESSION['user_id'])) {
            $cartId = $this->cartModel->getCartByUserId($_SESSION['user_id'], session_id());
        } else {
            $cartId = $this->cartModel->getCartBySessionId(session_id());
        }

        if ($cartId) {
            $this->cartModel->addItem($cartId, $productId, $qty);
            
            // Recalculate everything fresh from DB
            $cartItems = $this->cartModel->getCartItems($cartId);
            $cartCount = 0;
            $total = 0;
            
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    $itemQty = (int)$item['quantity'];
                    $itemPrice = (float)$item['current_price'];
                    $cartCount += $itemQty;
                    $total += $itemPrice * $itemQty;
                }
            } else {
                // Fail-safe: if getCartItems returned empty but we just added something, 
                // there might be a replication lag or a session issue. 
                // Return at least the quantity we just added.
                $cartCount = (int)$qty;
                $total = (float)$product['price'] * (int)$qty;
            }
            
            $this->json([
                'success' => true, 
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!', 
                'cart_count' => (int)$cartCount,
                'cart_total' => number_format($total) . 'đ'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể kết nối giỏ hàng']);
        }
    }
    
    /**
     * API: Cập nhật số lượng (AJAX)
     */
    public function updateAjax() {
        $this->validateCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        $qty = (int)($input['quantity'] ?? 1);
        
        if (!$productId) $this->json(['success' => false]);

        $total = 0;
        $itemTotal = 0;
        $cartCount = 0;
        $success = false;

        if (isset($_SESSION['user_id'])) {
            $cartId = $this->cartModel->getCartByUserId($_SESSION['user_id'], session_id());
        } else {
            $cartId = $this->cartModel->getCartBySessionId(session_id());
        }

        if ($cartId) {
            $success = $this->cartModel->updateQuantity($cartId, $productId, $qty);
            $cartItems = $this->cartModel->getCartItems($cartId);
            $cartCount = 0;
            foreach ($cartItems as $item) {
                $cartCount += $item['quantity'];
                $total += $item['current_price'] * $item['quantity'];
                if ($item['product_id'] == $productId) {
                    $itemTotal = $item['current_price'] * $item['quantity'];
                }
            }
        }
        
        $this->json([
            'success' => $success, 
            'total' => number_format($total) . 'đ', 
            'item_total' => number_format($itemTotal) . 'đ',
            'cart_count' => $cartCount
        ]);
    }
    
    /**
     * API: Xóa khỏi giỏ (AJAX)
     */
    public function removeAjax() {
        $this->validateCsrf();
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        
        if (!$productId) $this->json(['success' => false]);

        $success = false;
        $total = 0;
        $cartCount = 0;

        if (isset($_SESSION['user_id'])) {
            $cartId = $this->cartModel->getCartByUserId($_SESSION['user_id'], session_id());
        } else {
            $cartId = $this->cartModel->getCartBySessionId(session_id());
        }

        if ($cartId) {
            $success = $this->cartModel->removeItem($cartId, $productId);
            $cartItems = $this->cartModel->getCartItems($cartId);
            $cartCount = 0;
            foreach ($cartItems as $item) {
                $cartCount += $item['quantity'];
                $total += $item['current_price'] * $item['quantity'];
            }
        }
        
        $this->json([
            'success' => $success, 
            'total' => number_format($total) . 'đ', 
            'cart_count' => $cartCount
        ]);
    }
    
    /**
     * Chuyển hướng sang trang thanh toán của OrderController
     */
    public function checkout() {
        $this->redirect(BASE_URL . 'order/checkout');
    }
}
