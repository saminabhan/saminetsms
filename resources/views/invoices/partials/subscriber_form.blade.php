<div class="card-body">
    <form action="{{ route('invoices.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- المشترك -->
            <div class="col-md-6 mb-3">
                <label for="subscriber_id" class="form-label">المشترك <span class="text-danger">*</span></label>
                <select name="subscriber_id" id="subscriber_id" class="form-select @error('subscriber_id') is-invalid @enderror" required>
                    <option value="">اختر المشترك</option>
                    @foreach($subscribers as $subscriber)
                        <option value="{{ $subscriber->id }}" {{ old('subscriber_id') == $subscriber->id ? 'selected' : '' }}>
                            {{ $subscriber->name }} - {{ $subscriber->phone }}
                        </option>
                    @endforeach
                </select>
                @error('subscriber_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- تاريخ بداية الخدمة -->
            <div class="col-md-6 mb-3">
                <label for="service_start_date" class="form-label">تاريخ بداية الخدمة <span class="text-danger">*</span></label>
                <input type="date" name="service_start_date" id="service_start_date" 
                       class="form-control @error('service_start_date') is-invalid @enderror" 
                       value="{{ old('service_start_date', date('Y-m-d')) }}" required>
                @error('service_start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- فئة الخدمة -->
        <div class="mb-3">
            <label for="service_category" class="form-label">فئة الخدمة</label>
            <select id="service_category" class="form-select">
                <option value="">اختر فئة الخدمة</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_ar ?? $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- الخدمة -->
        <div class="mb-3">
            <label for="service_id" class="form-label">الخدمة <span class="text-danger">*</span></label>
            <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required disabled>
                <option value="">اختر فئة الخدمة أولاً</option>
            </select>
            @error('service_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- الكمية والسعر -->
        <div class="row">
            <div class="col-md-4 mb-3" id="quantity_wrapper" style="display: none;">
                <label for="quantity" class="form-label">الكمية</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1">
            </div>

            <div class="col-md-4 mb-3">
                <label for="original_price" class="form-label">السعر الأصلي</label>
                <div class="input-group">
                    <input type="number" id="original_price" class="form-control" readonly step="0.01">
                    <span class="input-group-text">ش.ج</span>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label for="discount_amount" class="form-label">مبلغ الخصم</label>
                <div class="input-group">
                    <input type="number" name="discount_amount" id="discount_amount" 
                           class="form-control @error('discount_amount') is-invalid @enderror" 
                           min="0" step="0.01" value="{{ old('discount_amount', 0) }}">
                    <span class="input-group-text">ش.ج</span>
                </div>
                @error('discount_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="final_amount" class="form-label">المبلغ النهائي</label>
                <div class="input-group">
                    <input type="number" id="final_amount" class="form-control" readonly step="0.01">
                    <span class="input-group-text">ش.ج</span>
                </div>
            </div>
        </div>

        <!-- ملاحظات -->
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" 
                      placeholder="أدخل أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
         <div class="d-flex justify-content-between">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">إلغاء</a>
        <button type="submit" class="btn btn-primary">إنشاء الفاتورة</button>
    </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicesData = @json($servicesByCategory);

    const serviceCategorySelect = document.getElementById('service_category');
    const serviceSelect = document.getElementById('service_id');
    const originalPriceInput = document.getElementById('original_price');
    const discountAmountInput = document.getElementById('discount_amount');
    const finalAmountInput = document.getElementById('final_amount');
    const quantityInput = document.getElementById('quantity');
    const quantityWrapper = document.getElementById('quantity_wrapper');

    // تغيير فئة الخدمة
    serviceCategorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';
        serviceSelect.disabled = true;
        originalPriceInput.value = '';
        finalAmountInput.value = '';

        if (categoryId && servicesData[categoryId]) {
            servicesData[categoryId].forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                option.dataset.price = service.price;
                option.dataset.allowQuantity = service.allow_quantity ? '1' : '0';
                serviceSelect.appendChild(option);
            });
            serviceSelect.disabled = false;
        }
    });

    // تغيير الخدمة
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.dataset.price;
        const allowQuantity = selectedOption.dataset.allowQuantity === '1';

        if (price) {
            originalPriceInput.value = parseFloat(price).toFixed(2);
            quantityWrapper.style.display = allowQuantity ? '' : 'none';
            if (!allowQuantity) quantityInput.value = 1;
            calculateFinalAmount();
        } else {
            originalPriceInput.value = '';
            finalAmountInput.value = '';
            quantityWrapper.style.display = 'none';
            quantityInput.value = 1;
        }
    });

    // تغيير الخصم أو الكمية
    discountAmountInput.addEventListener('input', calculateFinalAmount);
    quantityInput.addEventListener('input', function() {
        if (parseInt(quantityInput.value || '1') < 1) quantityInput.value = 1;
        calculateFinalAmount();
    });

    function calculateFinalAmount() {
        const basePrice = parseFloat(originalPriceInput.value) || 0;
        const quantity = parseInt(quantityInput.value || '1');
        const originalPrice = basePrice * Math.max(1, quantity);
        const discountAmount = parseFloat(discountAmountInput.value) || 0;
        finalAmountInput.value = Math.max(0, originalPrice - discountAmount).toFixed(2);
    }

    if (originalPriceInput.value || discountAmountInput.value) {
        calculateFinalAmount();
    }
});
</script>
@endpush
