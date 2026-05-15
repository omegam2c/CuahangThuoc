<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ProductModel.php';

class ProductController extends BaseController {
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new ProductModel();
    }
    
    /**
     * Danh sách sản phẩm (có lọc & tìm kiếm)
     */
    public function index() {
        $category = $_GET['category'] ?? null;
        $q = $_GET['q'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $sale = isset($_GET['sale']);
        $pageSize = 32;
        $requestedPage = max(1, (int)($_GET['page'] ?? 1));
        $currentPage = $requestedPage;
        $totalProducts = 0;
        
        $categories = $this->productModel->getCategories();

        // Tính tổng trước để clamp trang hợp lệ
        if ($q) {
            $totalProducts = $this->productModel->countSearchProducts($q);
        } elseif (trim($category) === 'thuoc-ke-don') {
            $totalProducts = $this->productModel->countProductsByType('rx');
        } elseif (trim($category) === 'thuoc-otc') {
            $totalProducts = $this->productModel->countProductsByType('otc');
        } elseif ($category && is_numeric($category)) {
            $totalProducts = $this->productModel->countProductsByCategory((int)$category);
        } elseif ($sale) {
            $totalProducts = $this->productModel->countSaleProducts();
        } else {
            $totalProducts = $this->productModel->countAllActiveProducts();
        }

        $totalPages = max(1, (int)ceil($totalProducts / $pageSize));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $pageSize;

        if ($q) {
            $products = $this->productModel->searchProducts($q, $sort, $pageSize, $offset);
        } elseif (trim($category) === 'thuoc-ke-don') {
            $products = $this->productModel->getProductsByType('rx', $pageSize, $sort, $offset);
        } elseif (trim($category) === 'thuoc-otc') {
            $products = $this->productModel->getProductsByType('otc', $pageSize, $sort, $offset);
        } elseif ($category) {
            if (is_numeric($category)) {
                $products = $this->productModel->getProductsByCategory((int)$category, $pageSize, $sort, $offset);
            } else {
                $products = $this->productModel->getNewProducts($pageSize, $sort, $offset);
            }
        } elseif ($sale) {
            $products = $this->productModel->getSaleProducts($pageSize, $sort, $offset);
        } else {
            $products = $this->productModel->getNewProducts($pageSize, $sort, $offset);
        }
        
        $this->loadView('product/list', [
            'products' => $products,
            'categories' => $categories,
            'pageTitle' => 'Sản phẩm - Nhà thuốc 1985',
            'pagination' => [
                'current_page' => $currentPage,
                'page_size' => $pageSize,
                'total_products' => $totalProducts,
                'total_pages' => $totalPages
            ]
        ]);
    }
    
    /**
     * Chi tiết sản phẩm
     */
    public function detail() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/');
        }
        
        // Fetch the product by ID
        $product = $this->productModel->getProductById($id);
        
        if (!$product) {
            $this->redirect('/');
        }
        
        $this->loadView('product/detail', [
            'product' => $product,
            'pageTitle' => $product['product_name'] . ' - Nhà thuốc 1985'
        ]);
    }
}
