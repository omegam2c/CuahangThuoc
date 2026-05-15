<?php
require_once __DIR__ . '/BaseController.php';

class PrescriptionController extends BaseController {
    
    public function index() {
        $this->upload();
    }
    
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf(); // Bảo mật CSRF (Ref point 🛡️)
            
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error_message'] = "Vui lòng đăng nhập để gửi đơn thuốc!";
                $this->redirect('auth/login');
                return;
            }
            
            $note = trim($_POST['note'] ?? '');
            
            // Xử lý file ảnh
            $uploadDir = __DIR__ . '/../../app/storage/prescriptions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0700, true); // Giới hạn quyền truy cập thư mục
            }
            
            if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['prescription_image']['tmp_name'];
                $fileName = $_FILES['prescription_image']['name'];
                $fileSize = $_FILES['prescription_image']['size'];
                
                // Kiểm tra thông tin bắt buộc (Ref point 🔐)
                if (empty($_POST['receiver_name']) || empty($_POST['receiver_phone']) || empty($_POST['shipping_address'])) {
                    $_SESSION['error_message'] = "Vui lòng điền đầy đủ các thông tin bắt buộc (*)";
                } 
                // 1. Kiểm tra kích thước file (Tối đa 5MB)
                elseif ($fileSize > 5 * 1024 * 1024) {
                    $_SESSION['error_message'] = "File quá lớn! Dung lượng tối đa là 5MB.";
                } else {
                    // 2. Kiểm tra MIME Type thực tế (Ref point 🔐)
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $fileTmpPath);
                    finfo_close($finfo);
                    
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                    
                    if (in_array($mimeType, $allowedMimes)) {
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        
                        // 3. Hash filename để tránh trùng lặp và an toàn (Ref point 🔐)
                        $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
                        $destPath = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            // Lưu vào Database
                            require_once __DIR__ . '/../Models/OrderModel.php';
                            $orderModel = new OrderModel();
                            
                            $orderData = [
                                'user_id' => $_SESSION['user_id'],
                                'receiver_name' => $_POST['receiver_name'] ?? $_SESSION['full_name'] ?? 'Khách hàng',
                                'receiver_phone' => $_POST['receiver_phone'] ?? '',
                                'shipping_address' => $_POST['shipping_address'] ?? '',
                                'note' => "Gửi từ trang Upload Đơn thuốc. Ghi chú: " . $note,
                                'payment_method' => 'cod',
                                'subtotal' => 0,
                                'total_amount' => 0,
                                'status' => 'pending',
                                'has_prescription' => 1,
                                'prescription_image' => $newFileName,
                                'prescription_verified' => 0
                            ];
                            
                            if ($orderModel->createPrescriptionOrder($orderData)) {
                                $_SESSION['success_message'] = "Gửi đơn thuốc thành công! Dược sĩ sẽ liên hệ lại với bạn sớm nhất.";
                                $this->redirect('prescription/success');
                                return;
                            } else {
                                $_SESSION['error_message'] = "Lỗi lưu dữ liệu đơn hàng.";
                            }
                        } else {
                            $_SESSION['error_message'] = "Lỗi khi di chuyển file tải lên.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Chỉ chấp nhận file ảnh (JPG, PNG, WEBP) hoặc PDF.";
                    }
                }
            } else {
                $_SESSION['error_message'] = "Vui lòng chọn một file ảnh đơn thuốc hợp lệ.";
            }
            
            $this->loadView('prescription/upload', [
                'pageTitle' => 'Tải lên đơn thuốc - Nhà thuốc 1985',
                'error' => $_SESSION['error_message'] ?? null
            ]);
            unset($_SESSION['error_message']);
            
        } else {
            $this->loadView('prescription/upload', [
                'pageTitle' => 'Tải lên đơn thuốc - Nhà thuốc 1985'
            ]);
        }
    }
    
    public function success() {
        $this->loadView('prescription/success', [
            'pageTitle' => 'Thành công - Nhà thuốc 1985'
        ]);
    }
    
    /**
     * Hiển thị ảnh đơn thuốc an toàn (Ref point 🔴 - Path Traversal)
     */
    public function view() {
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.0 403 Forbidden');
            exit('Forbidden');
        }
        
        $filename = basename($_GET['file'] ?? ''); // Sanitize filename (Ref point 3 - Path Traversal)
        
        if (empty($filename)) {
            header('HTTP/1.0 404 Not Found');
            exit('File not found');
        }

        // Kiểm tra Extension (Ref point 🔴 - Security)
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($ext, $allowedExts)) {
            header('HTTP/1.0 403 Forbidden');
            exit('Loại file không được phép truy cập');
        }
        
        $uploadDir = realpath(__DIR__ . '/../../app/storage/prescriptions/');
        $filepath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        
        // Kiểm tra xem file có thực sự nằm trong thư mục cho phép không (Path Traversal check)
        if (strpos(realpath($filepath), $uploadDir) !== 0) {
            header('HTTP/1.0 403 Forbidden');
            exit('Truy cập trái phép');
        }

        if (!file_exists($filepath)) {
            header('HTTP/1.0 404 Not Found');
            exit('File không tồn tại');
        }
        
        $hasAccess = false;
        
        if ($_SESSION['role_id'] != 4) { // Admin, Pharmacist, Doctor
            $hasAccess = true;
        } else {
            // Customer: Only if they own the order
            require_once __DIR__ . '/../Models/BaseModel.php';
            $db = (new BaseModel())->db;
            $stmt = $db->prepare("SELECT order_id FROM orders WHERE prescription_image = ? AND user_id = ?");
            $stmt->execute([$filename, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $hasAccess = true;
            }
        }
        
        if (!$hasAccess) {
            header('HTTP/1.0 403 Forbidden');
            exit('Forbidden: You do not have permission to view this prescription.');
        }
        
        if (ob_get_level()) ob_clean();
        
        $mime = 'image/jpeg'; // Default
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filepath) ?: 'image/jpeg';
        } else {
            $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            $mimes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            $mime = $mimes[$ext] ?? 'image/jpeg';
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=86400');
        readfile($filepath);
        exit;
    }
}
