<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/CartModel.php';

class HomeController extends BaseController {
    private $productModel;
    private $cartModel;
    
    public function __construct() {
        parent::__construct();
        $this->cartModel = new CartModel();
        $this->productModel = new ProductModel();
    }
    
    /**
     * Hiển thị trang chủ
     */
    public function index() {
        // Lấy dữ liệu từ Model
        $bestSellers = $this->productModel->getBestSellers(24);
        $saleProducts = $this->productModel->getSaleProducts(24);
        $newProducts = $this->productModel->getNewProducts(24);
        $categories = $this->productModel->getCategories();

        // Nếu chưa có nhiều dữ liệu bán chạy/khuyến mãi thì dùng sản phẩm mới để tránh trang chủ bị trống.
        if (count($bestSellers) < 8) {
            $bestSellers = $this->productModel->getNewProducts(24);
        }
        if (count($saleProducts) < 8) {
            $saleProducts = $this->productModel->getNewProducts(24);
        }
        
        // Truyền dữ liệu vào View
        $data = [
            'bestSellers' => $bestSellers,
            'saleProducts' => $saleProducts,
            'newProducts' => $newProducts,
            'categories' => $categories,
            'pageTitle' => 'Trang chủ - Nhà thuốc 1985'
        ];
        
        // Load view
        $this->loadView('home', $data);
    }
}
