<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="section-title">Quản lý Danh mục sản phẩm</h1>
        <button class="btn btn-premium" onclick="showAddModal()">+ Thêm danh mục mới</button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #e8f5e9; color: #2e7d32; border-radius: 8px;">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #c62828; border-radius: 8px;">
            <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="padding: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">Tên danh mục</th>
                    <th style="padding: 15px;">Danh mục cha</th>
                    <th style="padding: 15px;">Mô tả</th>
                    <th style="padding: 15px; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px;"><?= $c['category_id'] ?></td>
                        <td style="padding: 15px;">
                            <strong><?= htmlspecialchars($c['category_name']) ?></strong>
                        </td>
                        <td style="padding: 15px;">
                            <?php if ($c['parent_name']): ?>
                                <span style="padding: 4px 10px; background: #f0f4f8; border-radius: 15px; font-size: 0.85rem; color: var(--blue);">
                                    📁 <?= htmlspecialchars($c['parent_name']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">(Gốc)</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; color: var(--text-muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= htmlspecialchars($c['description'] ?: '---') ?>
                        </td>
                        <td style="padding: 15px; text-align: right;">
                            <button class="btn" style="color: var(--blue); padding: 5px 10px;" 
                                    onclick='editCategory(<?= json_encode($c) ?>)'>
                                ✏️ Sửa
                            </button>
                            <button class="btn" style="color: var(--red); padding: 5px 10px;" 
                                    onclick="deleteCategory(<?= $c['category_id'] ?>)">
                                🗑️ Xóa
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">Chưa có danh mục nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm/Sửa Danh mục -->
<div id="categoryModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
    <div style="background: white; width: 500px; margin: 100px auto; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 id="modalTitle" style="margin-bottom: 25px; color: var(--dark);">Thêm danh mục mới</h2>
        
        <form id="categoryForm" method="POST" action="<?= BASE_URL ?>admin/general/addCategory">
            <?= $csrf_field ?>
            <input type="hidden" name="category_id" id="edit_category_id">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Tên danh mục <span style="color: var(--red);">*</span></label>
                <input type="text" name="category_name" id="modal_name" class="form-control" required placeholder="VD: Thuốc kháng sinh">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Danh mục cha</label>
                <select name="parent_category_id" id="modal_parent" class="form-control">
                    <option value="">-- Không có (Danh mục gốc) --</option>
                    <?php foreach ($categories as $pc): ?>
                        <option value="<?= $pc['category_id'] ?>"><?= htmlspecialchars($pc['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Mô tả</label>
                <textarea name="description" id="modal_description" class="form-control" style="height: 100px;" placeholder="Mô tả ngắn gọn về danh mục..."></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn" onclick="closeModal()" style="background: #eee;">Hủy</button>
                <button type="submit" class="btn btn-premium">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<!-- Form ẩn để xóa -->
<form id="deleteForm" action="<?= BASE_URL ?>admin/general/deleteCategory" method="POST" style="display: none;">
    <?= $csrf_field ?>
    <input type="hidden" name="category_id" id="delete_category_id">
</form>

<script>
const modal = document.getElementById('categoryModal');
const form = document.getElementById('categoryForm');
const modalTitle = document.getElementById('modalTitle');

function showAddModal() {
    modalTitle.innerText = "Thêm danh mục mới";
    form.action = "<?= BASE_URL ?>admin/general/addCategory";
    document.getElementById('edit_category_id').value = "";
    document.getElementById('modal_name').value = "";
    document.getElementById('modal_description').value = "";
    document.getElementById('modal_parent').value = "";
    modal.style.display = 'block';
}

function editCategory(category) {
    modalTitle.innerText = "Chỉnh sửa danh mục";
    form.action = "<?= BASE_URL ?>admin/general/updateCategory";
    document.getElementById('edit_category_id').value = category.category_id;
    document.getElementById('modal_name').value = category.category_name;
    document.getElementById('modal_description').value = category.description;
    document.getElementById('modal_parent').value = category.parent_category_id || "";
    
    // Disable self-parenting
    Array.from(document.getElementById('modal_parent').options).forEach(opt => {
        opt.disabled = (opt.value == category.category_id);
    });
    
    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
}

function deleteCategory(id) {
    if(confirm('Bạn có chắc chắn muốn xóa danh mục này? Hệ thống sẽ kiểm tra ràng buộc sản phẩm trước khi xóa.')) {
        document.getElementById('delete_category_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Đóng modal khi click ra ngoài
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<style>
.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
}
.form-control:focus {
    border-color: var(--blue);
    outline: none;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}
.btn-premium {
    background: var(--blue);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-premium:hover {
    background: #1976D2;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
