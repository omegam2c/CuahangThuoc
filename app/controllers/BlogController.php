<?php

require_once __DIR__ . '/BaseController.php';

class BlogController extends BaseController {
    
    // Dữ liệu bài viết tĩnh
    private function getStaticBlogs() {
        return [
            [
                'blog_id' => 1,
                'title' => '5 cách phòng ngừa cảm cúm hiệu quả khi giao mùa',
                'summary' => 'Cảm cúm là bệnh lý thường gặp khi thời tiết thay đổi. Việc chủ động phòng ngừa không chỉ giúp bảo vệ sức khỏe bản thân mà còn cho cả gia đình...',
                'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=800',
                'author' => 'Dược sĩ Lan Anh',
                'created_at' => '2026-04-25 09:00:00',
                'content' => "Cảm cúm là một bệnh nhiễm trùng đường hô hấp do virus gây ra. Dưới đây là 5 cách đơn giản nhưng cực kỳ hiệu quả để phòng ngừa:\n\n1. Rửa tay thường xuyên bằng xà phòng hoặc dung dịch sát khuẩn.\n2. Đeo khẩu trang khi đến nơi đông người hoặc khi tiếp xúc với người bệnh.\n3. Ăn uống đầy đủ chất dinh dưỡng, đặc biệt là Vitamin C để tăng sức đề kháng.\n4. Ngủ đủ giấc và tập thể dục nhẹ nhàng hàng ngày.\n5. Tiêm vaccine phòng cúm hàng năm theo khuyến cáo của bác sĩ."
            ],
            [
                'blog_id' => 2,
                'title' => 'Lợi ích bất ngờ của việc uống đủ nước mỗi ngày',
                'summary' => '70% cơ thể con người là nước. Việc uống đủ nước không chỉ giúp thanh lọc cơ thể mà còn mang lại những lợi ích tuyệt vời cho làn da và trí não...',
                'image_url' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?q=80&w=800',
                'author' => 'Dược sĩ Minh Đức',
                'created_at' => '2026-04-20 14:30:00',
                'content' => "Nước là thành phần thiết yếu của sự sống. Uống đủ 2 lít nước mỗi ngày mang lại những thay đổi tích cực:\n\n- Giúp da dẻ căng mịn, giảm mụn và các dấu hiệu lão hóa.\n- Hỗ trợ hệ tiêu hóa hoạt động trơn tru, ngăn ngừa táo bón.\n- Tăng cường khả năng tập trung của não bộ, giảm mệt mỏi.\n- Giúp thận lọc độc tố hiệu quả hơn.\n- Duy trì ổn định huyết áp và nhịp tim."
            ],
            [
                'blog_id' => 3,
                'title' => 'Chế độ dinh dưỡng cân bằng cho người cao tuổi',
                'summary' => 'Khi bước sang tuổi xế chiều, hệ tiêu hóa và hấp thụ của cơ thể thay đổi. Một chế độ ăn uống khoa học là chìa khóa để sống vui khỏe mỗi ngày...',
                'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=800',
                'author' => 'Dược sĩ Hoàng Long',
                'created_at' => '2026-04-15 10:15:00',
                'content' => "Người cao tuổi cần một chế độ ăn đặc biệt để duy trì sức khỏe:\n\n- Ưu tiên thực phẩm dễ tiêu hóa: Cháo, súp, các loại hạt xay nhuyễn.\n- Bổ sung nhiều rau xanh và trái cây tươi để cung cấp chất xơ và vitamin.\n- Hạn chế muối và đường để phòng ngừa huyết áp cao và tiểu đường.\n- Chia nhỏ các bữa ăn trong ngày để cơ thể dễ hấp thu.\n- Bổ sung Canxi và Vitamin D thông qua sữa hoặc thực phẩm chức năng để bảo vệ xương khớp."
            ]
        ];
    }
    
    public function index() {
        $blogs = $this->getStaticBlogs();
        
        $this->loadView('blog/index', [
            'blogs' => $blogs,
            'pageTitle' => 'Góc sức khỏe - Kiến thức y khoa'
        ]);
    }
    
    public function detail() {
        $id = intval($_GET['id'] ?? 0);
        $blogs = $this->getStaticBlogs();
        $blog = null;
        
        foreach ($blogs as $b) {
            if ($b['blog_id'] === $id) {
                $blog = $b;
                break;
            }
        }
        
        if (!$blog) {
            $this->redirect(BASE_URL . 'blog');
        }
        
        $this->loadView('blog/detail', [
            'blog' => $blog,
            'pageTitle' => $blog['title']
        ]);
    }
}
