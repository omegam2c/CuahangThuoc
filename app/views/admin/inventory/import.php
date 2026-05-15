<?php include __DIR__ . '/../../layout/header.php'; ?>

<style>
    #itemsTable input, #itemsTable select {
        border: 1px solid var(--border, #ccc);
        border-radius: 4px;
        background: #fff;
    }
    #itemsTable input:focus, #itemsTable select:focus {
        border-color: var(--green, #10b981);
        outline: none;
    }
    /* Hide spin buttons for number inputs */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="section-title" style="margin: 0;">Phiếu nhập kho & Tạo lô hàng</h1>
        <a href="<?= BASE_URL ?>admin/inventory" class="btn" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; font-weight: 600;">
            ⬅️ Quay lại danh sách
        </a>
    </div>
    
    <form action="<?= BASE_URL ?>admin/inventory/handleImport" method="POST" id="importForm" style="margin-top: 30px;">
        <?= $csrf_field ?>
        <div class="glass-card" style="padding: 25px; margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px;">Thông tin chung</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px;">Nhà cung cấp</label>
                    <input type="text" name="supplier_name" list="suppliersList" required placeholder="Nhập tên nhà cung cấp..." style="width: 100%; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <datalist id="suppliersList">
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= htmlspecialchars($s['supplier_name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px;">Số hóa đơn</label>
                    <input type="text" name="invoice_number" required style="width: 100%; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px;">Ngày nhập</label>
                    <input type="date" name="receipt_date" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                </div>
            </div>
        </div>

        <div class="glass-card" style="padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Chi tiết lô hàng</h3>
                <button type="button" class="btn btn-outline" onclick="addRow()" style="font-size: 0.8rem;">+ Thêm sản phẩm</button>
            </div>
            
            <div id="itemsContainer">
                <table style="width: 100%; border-collapse: collapse;" id="itemsTable">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border); font-size: 0.85rem;">
                            <th style="padding: 10px;">Sản phẩm</th>
                            <th style="padding: 10px;">Số lô</th>
                            <th style="padding: 10px;">NSX / HSD</th>
                            <th style="padding: 10px;">SL</th>
                            <th style="padding: 10px;">Giá nhập</th>
                            <th style="padding: 10px;">Vị trí</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px;">
                                <select name="products[]" required style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                                    <?php 
                                    $preselectedId = $_GET['product_id'] ?? 0;
                                    foreach ($products as $p): 
                                    ?>
                                        <option value="<?= $p['product_id'] ?>" <?= $p['product_id'] == $preselectedId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['product_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 10px;"><input type="text" name="batch_numbers[]" required style="width: 80px; padding: 8px;"></td>
                            <td style="padding: 10px;">
                                <input type="date" name="manufacture_dates[]" required style="width: 120px; padding: 5px; font-size: 0.75rem;"><br>
                                <input type="date" name="expiry_dates[]" required style="width: 120px; padding: 5px; font-size: 0.75rem; margin-top: 5px;">
                            </td>
                            <td style="padding: 10px;"><input type="number" name="quantities[]" step="any" required style="width: 70px; padding: 8px;"></td>
                            <td style="padding: 10px;"><input type="number" name="purchase_prices[]" step="any" required style="width: 100px; padding: 8px;"></td>
                            <td style="padding: 10px;"><input type="text" name="storage_locations[]" style="width: 80px; padding: 8px;" placeholder="Kệ A1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 25px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Tổng giá trị phiếu (VNĐ):</label>
                    <input type="number" name="total_amount" id="total_amount" readonly style="border: none; background: transparent; font-weight: bold; font-size: 1.5rem; color: var(--green); width: 250px; outline: none;">
                </div>
                <div style="display: flex; gap: 15px;">
                    <a href="<?= BASE_URL ?>admin/inventory" class="btn btn-outline" onclick="return confirm('Bạn có chắc muốn hủy thao tác? Các dữ liệu đã nhập sẽ bị xóa.');">Hủy bỏ</a>
                    <button type="submit" class="btn btn-premium">Hoàn tất nhập kho</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function calculateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="purchase_prices[]"]').value) || 0;
        total += (qty * price);
    });
    document.getElementById('total_amount').value = total;
}

function addRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const firstRow = tbody.querySelector('tr');
    const newRow = firstRow.cloneNode(true);
    // Clear inputs
    newRow.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.addEventListener('input', calculateTotal);
    });
    tbody.appendChild(newRow);
}

// Attach event listeners to initial row
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#itemsTable tbody input[name="quantities[]"], #itemsTable tbody input[name="purchase_prices[]"]').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
