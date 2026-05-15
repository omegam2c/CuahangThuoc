<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    :root {
        --presc-bg: #f8fafc;
        --presc-accent: #00904a;
        --presc-text: #1e293b;
        --presc-muted: #64748b;
        --presc-border: #e2e8f0;
    }

    .prescription-layout { 
        display: grid; 
        grid-template-columns: 320px 1fr; 
        gap: 30px; 
        align-items: start; 
    }

    .presc-card { 
        background: #fff; 
        border-radius: 24px; 
        padding: 30px; 
        border: 1px solid var(--presc-border); 
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); 
        margin-bottom: 25px; 
    }

    .presc-card-title { 
        font-size: 1.15rem; 
        font-weight: 800; 
        color: var(--presc-text); 
        margin-bottom: 25px; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
    }

    .presc-card-title i {
        background: rgba(0, 144, 74, 0.1);
        color: var(--presc-accent);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-style: normal;
    }
    
    .patient-info-box { 
        background: var(--presc-bg); 
        border-radius: 18px; 
        padding: 20px; 
        border: 1px solid var(--presc-border); 
    }

    .patient-label { 
        font-size: 0.75rem; 
        color: var(--presc-muted); 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        font-weight: 700; 
        margin-bottom: 4px; 
        display: block; 
    }

    .patient-value { 
        font-size: 1rem; 
        color: var(--presc-text); 
        font-weight: 600; 
        margin-bottom: 15px; 
        display: block; 
        word-break: break-word;
    }

    /* Medicine Table UI */
    .med-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; }
    .med-table th { 
        padding: 10px 15px; 
        text-align: left; 
        font-size: 0.8rem; 
        color: var(--presc-muted); 
        text-transform: uppercase; 
        letter-spacing: 0.05em;
    }
    
    .med-row { background: #fff; border-radius: 12px; transition: transform 0.2s; }
    .med-row td { 
        padding: 15px; 
        border-top: 1px solid var(--presc-border); 
        border-bottom: 1px solid var(--presc-border); 
    }
    .med-row td:first-child { border-left: 1px solid var(--presc-border); border-radius: 12px 0 0 12px; }
    .med-row td:last-child { border-right: 1px solid var(--presc-border); border-radius: 0 12px 12px 0; }

    .input-field {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--presc-border);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: #fdfdfd;
    }
    .input-field:focus {
        border-color: var(--presc-accent);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(0, 144, 74, 0.08);
        outline: none;
    }

    .btn-add-item {
        background: #f1f5f9;
        color: #475569;
        border: 1px dashed #cbd5e1;
        width: 100%;
        padding: 15px;
        border-radius: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-add-item:hover {
        background: #fff;
        border-color: var(--presc-accent);
        color: var(--presc-accent);
        transform: translateY(-2px);
    }

    .remove-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: #fee2e2;
        color: #ef4444;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .remove-btn:hover { background: #ef4444; color: #fff; }

    @media (max-width: 1024px) {
        .prescription-layout { grid-template-columns: 1fr; }
    }
</style>

<div class="container section animate-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
        <div>
            <nav style="font-size: 0.85rem; margin-bottom: 10px; color: var(--presc-muted);">
                Dược sĩ / <span style="color: var(--presc-text);">Kê đơn trực tuyến</span>
            </nav>
            <h1 style="font-size: 2rem; font-weight: 900; color: var(--presc-text); margin: 0;">Kê đơn thuốc</h1>
        </div>
        <a href="<?= BASE_URL ?>doctor/prescriptions" class="btn" style="background: #f1f5f9; color: #475569; padding: 12px 20px; border-radius: 12px; font-weight: 600;">← Quay lại</a>
    </div>

    <form action="<?= BASE_URL ?>doctor/storePrescription" method="POST" id="prescriptionForm">
        <?= $csrf_field ?>
        <input type="hidden" name="order_id" value="<?= $order_id ?? '' ?>">
        
        <div class="prescription-layout">
            <!-- CỘT TRÁI: THÔNG TIN -->
            <aside>
                <div class="presc-card">
                    <div class="presc-card-title"><i>👤</i> Bệnh nhân</div>
                    
                    <div class="patient-info-box">
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <input type="hidden" name="customer_id" value="<?= $customer['user_id'] ?? '' ?>">
                            <div>
                                <label class="patient-label">Họ tên bệnh nhân *</label>
                                <input type="text" name="customer_name" class="input-field" 
                                       placeholder="Tên khách hàng..." 
                                       value="<?= htmlspecialchars($customer['full_name'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label class="patient-label">Số điện thoại *</label>
                                <input type="text" name="customer_phone" class="input-field" 
                                       placeholder="Nhập SĐT..." 
                                       value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label class="patient-label">Địa chỉ Email (để nhận đơn) *</label>
                                <input type="email" name="customer_email" class="input-field" 
                                       placeholder="ví dụ: khachhang@gmail.com" 
                                       value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label class="patient-label">Địa chỉ nhận hàng *</label>
                                <textarea name="customer_address" class="input-field" rows="3" 
                                          placeholder="Nhập địa chỉ..." required><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="presc-card">
                    <div class="presc-card-title"><i>🩺</i> Chẩn đoán & Lưu ý</div>
                    <textarea name="notes" class="input-field" rows="6" style="resize: none;" placeholder="Chẩn đoán bệnh, dặn dò uống thuốc, chế độ ăn uống..."></textarea>
                </div>
            </aside>

            <!-- CỘT PHẢI: TOA THUỐC -->
            <main>
                <div class="presc-card">
                    <div class="presc-card-title">
                        <i>💊</i> Chỉ định dược phẩm
                        <span style="margin-left: auto; font-size: 0.8rem; font-weight: 500; color: var(--presc-muted);">Thêm thuốc & Liều lượng</span>
                    </div>

                    <table class="med-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Thuốc / Sản phẩm</th>
                                <th style="width: 15%;">Số lượng</th>
                                <th style="width: 40%;">Hướng dẫn sử dụng</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            <tr class="med-row">
                                <td>
                                    <select name="products[]" class="input-field" required onchange="updateUnit(this)">
                                        <option value="">-- Chọn thuốc --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['product_id'] ?>" data-unit="<?= htmlspecialchars($p['unit']) ?>">
                                                <?= htmlspecialchars($p['product_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="number" name="quantities[]" class="input-field" value="1" min="1" style="text-align: center;">
                                        <span class="unit-label" style="font-size: 0.8rem; color: var(--presc-muted); white-space: nowrap;"></span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="dosages[]" class="input-field" placeholder="Sáng 1, chiều 1..." required>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="remove-btn" onclick="removeRow(this)">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn-add-item" onclick="addRow()">
                        <span>➕</span> Thêm thuốc vào toa
                    </button>
                    
                    <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid var(--presc-border); display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
                        <span style="color: var(--presc-muted); font-size: 0.9rem;">Kiểm tra kỹ thông tin trước khi gửi</span>
                        <button type="submit" class="btn btn-premium" style="background: var(--presc-accent); padding: 15px 45px; font-size: 1.1rem; border-radius: 18px; box-shadow: 0 10px 20px -5px rgba(0, 144, 74, 0.3);">
                            🚀 Hoàn tất & Xuất đơn
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </form>
</div>

<script>
function addRow() {
    const container = document.getElementById('itemsContainer');
    const firstRow = container.querySelector('.med-row');
    const newRow = firstRow.cloneNode(true);
    
    // Reset values
    newRow.querySelector('select').value = '';
    newRow.querySelector('input[type="number"]').value = '1';
    newRow.querySelector('input[type="text"]').value = '';
    newRow.querySelector('.unit-label').textContent = '';
    
    container.appendChild(newRow);
}

function removeRow(btn) {
    const container = document.getElementById('itemsContainer');
    if (container.querySelectorAll('.med-row').length > 1) {
        btn.closest('.med-row').remove();
    } else {
        alert('Đơn thuốc phải có ít nhất một loại thuốc.');
    }
}

function updateUnit(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const unit = selectedOption.getAttribute('data-unit') || '';
    const row = selectElem.closest('.med-row');
    row.querySelector('.unit-label').textContent = unit;
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
