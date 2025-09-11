@extends('layouts.app')

@section('title', 'إنشاء فاتورة جديدة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        إنشاء فاتورة جديدة
                    </h5>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-2"></i>
                        العودة للقائمة
                    </a>
                </div>
            </div>
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

                    <!-- الكمية ومعلومات السعر -->
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
                        <textarea name="notes" id="notes" rows="3" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="أدخل أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            إنشاء الفاتورة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // البيانات من الخادم
    const servicesData = @json($servicesByCategory);
    console.log('Services Data:', servicesData);
    
    const serviceCategorySelect = document.getElementById('service_category');
    const serviceSelect = document.getElementById('service_id');
    const originalPriceInput = document.getElementById('original_price');
    const discountAmountInput = document.getElementById('discount_amount');
    const finalAmountInput = document.getElementById('final_amount');
    const quantityInput = document.getElementById('quantity');
    const quantityWrapper = document.getElementById('quantity_wrapper');
    
    // عند تغيير فئة الخدمة
    serviceCategorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        // مسح خيارات الخدمة وإعادة تعيين الأسعار
        serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';
        serviceSelect.disabled = true;
        originalPriceInput.value = '';
        finalAmountInput.value = '';
        
        if (categoryId && servicesData[categoryId]) {
            console.log('Selected category:', categoryId);
            console.log('Services for category:', servicesData[categoryId]);
            
            // إضافة خيارات الخدمة
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
    
    // عند تغيير الخدمة
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.dataset.price;
        const allowQuantity = selectedOption.dataset.allowQuantity === '1';
        
        if (price) {
            originalPriceInput.value = parseFloat(price).toFixed(2);
            if (allowQuantity) {
                quantityWrapper.style.display = '';
            } else {
                quantityWrapper.style.display = 'none';
                quantityInput.value = 1;
            }
            calculateFinalAmount();
        } else {
            originalPriceInput.value = '';
            finalAmountInput.value = '';
            quantityWrapper.style.display = 'none';
            quantityInput.value = 1;
        }
    });
    
    // عند تغيير مبلغ الخصم
    discountAmountInput.addEventListener('input', function() {
        calculateFinalAmount();
    });

    // عند تغيير الكمية
    quantityInput.addEventListener('input', function() {
        if (parseInt(quantityInput.value || '1') < 1) {
            quantityInput.value = 1;
        }
        calculateFinalAmount();
    });
    
    // دالة حساب المبلغ النهائي
    function calculateFinalAmount() {
        const basePrice = parseFloat(originalPriceInput.value) || 0;
        const quantity = parseInt(quantityInput.value || '1');
        const originalPrice = basePrice * Math.max(1, quantity);
        const discountAmount = parseFloat(discountAmountInput.value) || 0;
        
        let finalAmount = originalPrice - discountAmount;
        
        // التأكد من أن المبلغ النهائي لا يقل عن صفر
        if (finalAmount < 0) {
            finalAmount = 0;
        }
        
        finalAmountInput.value = finalAmount.toFixed(2);
    }
    
    // حساب المبلغ النهائي عند تحميل الصفحة إذا كان هناك قيم محفوظة
    if (originalPriceInput.value || discountAmountInput.value) {
        calculateFinalAmount();
    }
});
</script>
@endpush

@endsection