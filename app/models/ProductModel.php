<?php
require_once __DIR__ . '/BaseModel.php';

class ProductModel extends BaseModel {
    
    /**
     * Lấy sản phẩm bán chạy nhất (dựa vào view v_bestsellers)
     * @param int $limit Số lượng sản phẩm cần lấy
     * @return array Danh sách sản phẩm
     */
    public function getBestSellers($limit = 8) {
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.image_url,
                    p.price,
                    p.discount_percent,
                    m.manufacturer_name as brand,
                    COALESCE(bs.total_sold, 0) as total_sold,
                    COALESCE(bs.avg_rating, 4.5) as rating,
                    COALESCE(bs.review_count, 0) as reviews,
                    CASE 
                        WHEN p.discount_percent > 15 THEN CONCAT('Sale ', ROUND(p.discount_percent), '%')
                        WHEN COALESCE(bs.total_sold, 0) > 100 THEN 'Bán chạy'
                        ELSE ''
                    END as badge,
                    CASE 
                        WHEN p.discount_percent > 0 THEN ROUND(p.price * (100 - p.discount_percent) / 100)
                        ELSE p.price
                    END as current_price,
                    p.price as old_price,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                LEFT JOIN v_bestsellers bs ON p.product_id = bs.product_id
                LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
                WHERE p.is_active = 1
                ORDER BY COALESCE(bs.total_sold, 0) DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy sản phẩm đang khuyến mãi
     */
    public function getSaleProducts($limit = 8, $sort = null, $offset = 0) {
        $orderClause = "ORDER BY p.discount_percent DESC";
        if ($sort === 'price_asc') $orderClause = "ORDER BY current_price ASC";
        if ($sort === 'price_desc') $orderClause = "ORDER BY current_price DESC";
        
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.image_url,
                    p.price,
                    p.discount_percent,
                    m.manufacturer_name as brand,
                    ROUND(p.price * (100 - p.discount_percent) / 100) as current_price,
                    p.price as old_price,
                    CONCAT('Sale ', ROUND(p.discount_percent), '%') as badge,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
                WHERE p.is_active = 1 AND p.discount_percent > 0
                $orderClause
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy sản phẩm mới nhất
     */
    public function getNewProducts($limit = 8, $sort = null, $offset = 0) {
        $orderClause = "ORDER BY p.created_at DESC";
        if ($sort === 'price_asc') $orderClause = "ORDER BY current_price ASC";
        if ($sort === 'price_desc') $orderClause = "ORDER BY current_price DESC";
        
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.image_url,
                    p.price,
                    p.discount_percent,
                    m.manufacturer_name as brand,
                    CASE 
                        WHEN p.discount_percent > 0 THEN ROUND(p.price * (100 - p.discount_percent) / 100)
                        ELSE p.price
                    END as current_price,
                    p.price as old_price,
                    'Mới' as badge,
                    4.5 as rating,
                    0 as reviews,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
                WHERE p.is_active = 1
                $orderClause
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy sản phẩm theo danh mục (bao gồm cả danh mục con)
     */
    public function getProductsByCategory($categoryId, $limit = 20, $sort = null, $offset = 0) {
        $orderClause = "ORDER BY p.created_at DESC";
        if ($sort === 'price_asc') $orderClause = "ORDER BY current_price ASC";
        if ($sort === 'price_desc') $orderClause = "ORDER BY current_price DESC";
        
        $sql = "SELECT 
                    p.*,
                    CASE 
                        WHEN p.discount_percent > 0 THEN ROUND(p.price * (100 - p.discount_percent) / 100)
                        ELSE p.price
                    END as current_price,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                WHERE (p.category_id = :cat_id OR p.category_id IN (SELECT category_id FROM categories WHERE parent_category_id = :cat_id)) 
                  AND p.is_active = 1
                $orderClause
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy sản phẩm theo loại (Rx hoặc OTC) dựa trên sale_type (Ref point 1)
     */
    public function getProductsByType($type, $limit = 20, $sort = null, $offset = 0) {
        $orderClause = "ORDER BY p.created_at DESC";
        if ($sort === 'price_asc') $orderClause = "ORDER BY current_price ASC";
        if ($sort === 'price_desc') $orderClause = "ORDER BY current_price DESC";
        
        $condition = ($type === 'rx') ? "p.sale_type = 'prescription'" : "p.sale_type = 'otc'";
        
        $sql = "SELECT 
                    p.*,
                    CASE 
                        WHEN p.discount_percent > 0 THEN ROUND(p.price * (100 - p.discount_percent) / 100)
                        ELSE p.price
                    END as current_price,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                WHERE $condition AND p.is_active = 1
                $orderClause
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh mục hoạt chất của sản phẩm (Ref point 4)
     */
    public function getProductIngredients($productId) {
        $sql = "SELECT i.ingredient_name, pi.amount 
                FROM product_ingredients pi
                JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
                WHERE pi.product_id = :pid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Tìm kiếm sản phẩm
     */
    public function searchProducts($q, $sort = null, $limit = 20, $offset = 0) {
        $orderClause = "ORDER BY p.created_at DESC";
        if ($sort === 'price_asc') $orderClause = "ORDER BY current_price ASC";
        if ($sort === 'price_desc') $orderClause = "ORDER BY current_price DESC";
        
        // Tìm kiếm theo tên sản phẩm, tên gốc hoặc hoạt chất
        $sql = "SELECT 
                    p.*,
                    CASE 
                        WHEN p.discount_percent > 0 THEN ROUND(p.price * (100 - p.discount_percent) / 100)
                        ELSE p.price
                    END as current_price,
                    (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity
                FROM products p
                WHERE (p.product_name LIKE :q OR p.generic_name LIKE :q OR p.indications LIKE :q) AND p.is_active = 1
                $orderClause
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':q', "%$q%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy danh mục sản phẩm
     */
    public function getCategories() {
        $cacheFile = __DIR__ . '/../storage/cache/categories.json';
        $cacheTime = 3600; // 1 hour
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $sql = "SELECT 
                    category_id,
                    category_name,
                    description,
                    image_url
                FROM categories
                WHERE parent_category_id IS NULL
                ORDER BY category_id";
        
        $stmt = $this->db->query($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Save to cache
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($cacheFile, json_encode($categories));
        
        return $categories;
    }
    
    /**
     * Lấy sản phẩm theo ID
     */
    public function getProductById($id) {
        $sql = "SELECT p.*, c.category_name, 
                       m.manufacturer_name as brand,
                       m.manufacturer_name as manufacturer,
                       (SELECT SUM(quantity_remaining) FROM batches WHERE product_id = p.product_id AND quantity_remaining > 0 AND expiry_date > CURDATE()) as stock_quantity,
                       p.indications as description,
                       ROUND(p.price * (100 - p.discount_percent) / 100) as current_price
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
                WHERE p.product_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $product['ingredients'] = $this->getProductIngredients($id);
        }
        
        return $product;
    }

    /**
     * Đếm tổng số sản phẩm theo danh mục (bao gồm cả danh mục con)
     */
    public function countProductsByCategory($categoryId) {
        $sql = "SELECT COUNT(*) FROM products 
                WHERE (category_id = :cat_id OR category_id IN (SELECT category_id FROM categories WHERE parent_category_id = :cat_id))
                  AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Đếm tổng số sản phẩm theo loại (Rx hoặc OTC)
     */
    public function countProductsByType($type) {
        $condition = ($type === 'rx') ? "sale_type = 'prescription'" : "sale_type = 'otc'";
        $sql = "SELECT COUNT(*) FROM products WHERE $condition AND is_active = 1";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function countAllActiveProducts() {
        $sql = "SELECT COUNT(*) FROM products WHERE is_active = 1";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function countSaleProducts() {
        $sql = "SELECT COUNT(*) FROM products WHERE is_active = 1 AND discount_percent > 0";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function countSearchProducts($q) {
        $sql = "SELECT COUNT(*) 
                FROM products p
                WHERE (p.product_name LIKE :q OR p.indications LIKE :q OR p.generic_name LIKE :q)
                  AND p.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':q', "%$q%", PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}