<?php
require_once __DIR__ . '/BaseController.php';

class PageController extends BaseController {
    public function show($page, $subpage = 'index') {
        $titleMap = [
            'about' => 'Giới thiệu về Nhà thuốc 1985',
            'stores' => 'Hệ thống cửa hàng',
            'careers' => 'Tuyển dụng nhân sự',
            'franchise' => 'Nhượng quyền thương hiệu',
            'news' => 'Tin tức nội bộ',
            'help' => 'Hỗ trợ khách hàng',
            'privacy' => 'Chính sách bảo mật',
            'terms' => 'Điều khoản sử dụng',
            'cookie' => 'Chính sách Cookie'
        ];
        
        $title = $titleMap[$page] ?? 'Trang thông tin';
        if ($page == 'help' && $subpage !== 'index' && $subpage !== '') {
            $helpTitles = [
                'faq' => 'Câu hỏi thường gặp',
                'shipping' => 'Chính sách giao hàng',
                'return' => 'Chính sách đổi trả',
                'payment' => 'Phương thức thanh toán'
            ];
            $title = 'Hỗ trợ: ' . ($helpTitles[$subpage] ?? ucfirst($subpage));
        }

        $this->loadView('pages/static_page', [
            'pageTitle' => $title . ' - Nhà thuốc 1985',
            'pageName' => $title,
            'page' => $page,
            'subpage' => $subpage
        ]);
    }
}
