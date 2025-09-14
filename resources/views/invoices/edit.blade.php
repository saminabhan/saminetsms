@extends('layouts.app')

@section('title', 'تعديل الفاتورة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل الفاتورة: {{ $invoice->invoice_number }}
                    </h5>
                    <div>
                        <span class="badge bg-info me-2">{{ $invoice->status_text }}</span>
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-2"></i>
                            عرض الفاتورة
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- المشترك -->
                        <div class="col-md-6 mb-3">
                            <label for="subscriber_id" class="form-label">المشترك <span class="text-danger">*</span></label>
                            <select name="subscriber_id" id="subscriber_id" class="form-select @error('subscriber_id') is-invalid @enderror" required>
                                <option value="">اختر المشترك</option>
                                @foreach($subscribers as $subscriber)
                                    <option value="{{ $subscriber->id }}" 
                                            {{ (old('subscriber_id', $invoice->subscriber_id) == $subscriber->id) ? 'selected' : '' }}>
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
                                   value="{{ old('service_start_date', $invoice->service_start_date->format('Y-m-d')) }}" required>
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
                                <option value="{{ $category->id }}" 
                                        {{ $category->id == $invoice->service->service_category_id ? 'selected' : '' }}>
                                    {{ $category->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الخدمة -->
                    <div class="mb-3">
                        <label for="service_id" class="form-label">الخدمة <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">اختر الخدمة</option>
                            <!-- سيتم ملؤها بـ JavaScript -->
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الكمية ومعلومات السعر -->
                    <div class="row">
                        <div class="col-md-4 mb-3" id="quantity_wrapper" style="display: none;">
                            <label for="quantity" class="form-label">الكمية</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="{{ old('quantity', $invoice->quantity ?? 1) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="original_price" class="form-label">السعر الأصلي</label>
                            <div class="input-group">
                                <input type="number" id="original_price" class="form-control" 
                                       value="{{ $invoice->service ? $invoice->service->price : $invoice->original_price }}" readonly>
                                <span class="input-group-text">ش.ج</span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="discount_amount" class="form-label">مبلغ الخصم</label>
                            <div class="input-group">
                                <input type="number" name="discount_amount" id="discount_amount" 
                                       class="form-control @error('discount_amount') is-invalid @enderror" 
                                       min="0" step="0.01" value="{{ old('discount_amount', $invoice->discount_amount) }}">
                                <span class="input-group-text">ش.ج</span>
                            </div>
                            @error('discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="final_amount" class="form-label">المبلغ النهائي</label>
                            <div class="input-group">
                                <input type="number" id="final_amount" class="form-control" 
                                       value="{{ $invoice->final_amount }}" readonly>
                                <span class="input-group-text">ش.ج</span>
                            </div>
                        </div>
                    </div>

                    <!-- ملاحظات -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="أدخل أي ملاحظات إضافية...">{{ old('notes', $invoice->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- معلومات الدفع -->
                    @if($invoice->paid_amount > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تنبيه:</strong> هذه الفاتورة تحتوي على مدفوعات بقيمة {{ number_format($invoice->paid_amount, 2) }} ش.ج
                        <br>
                        تأكد من صحة التعديلات قبل الحفظ.
                    </div>
                    @endif

                    <!-- أزرار الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@php
$servicesByCategory = [];
foreach ($categories as $category) {
    $servicesByCategory[$category->id] = [];
    foreach ($category->activeServices as $service) {
        $servicesByCategory[$category->id][] = [
            'id' => $service->id,
            'name' => $service->name_ar,
            'price' => $service->price,
            'full_description' => $service->full_description,
            'allow_quantity' => $service->allow_quantity ? 1 : 0,
        ];
    }
}
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('service_category');
    const serviceSelect = document.getElementById('service_id');
    const originalPriceInput = document.getElementById('original_price');
    const discountAmountInput = document.getElementById('discount_amount');
    const finalAmountInput = document.getElementById('final_amount');
    const quantityInput = document.getElementById('quantity');
    const quantityWrapper = document.getElementById('quantity_wrapper');

    const currentServiceId = {{ $invoice->service_id }};
    const currentCategoryId = {{ $invoice->service->service_category_id }};

    const servicesByCategory = {!! json_encode($servicesByCategory, JSON_HEX_TAG) !!};

    function loadServicesForCategory(categoryId, selectedServiceId = null) {
        serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';

        if (categoryId && servicesByCategory[categoryId]) {
            servicesByCategory[categoryId].forEach(function(service) {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.full_description;
                option.dataset.price = service.price;
                option.dataset.allowQuantity = service.allow_quantity ? '1' : '0';

                if (selectedServiceId && service.id == selectedServiceId) {
                    option.selected = true;
                }

                serviceSelect.appendChild(option);
            });
            serviceSelect.disabled = false;
        } else {
            serviceSelect.disabled = true;
        }
    }

    function calculateFinalAmount() {
        const basePrice = parseFloat(originalPriceInput.value) || 0;
        const quantity = parseInt(quantityInput.value || '1');
        const originalPrice = basePrice * Math.max(1, quantity);
        const discountAmount = parseFloat(discountAmountInput.value) || 0;
        const finalAmount = Math.max(0, originalPrice - discountAmount);
        finalAmountInput.value = finalAmount.toFixed(2);
    }

    function resetPricing() {
        originalPriceInput.value = '';
        finalAmountInput.value = '';
    }

    // تحميل الخدمات للفئة الحالية عند التحميل
    loadServicesForCategory(currentCategoryId, currentServiceId);

    // إظهار الكمية إن كانت الخدمة الحالية تسمح بها
    (function initQuantityVisibility() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        if (selectedOption) {
            const allowQuantity = selectedOption.dataset.allowQuantity === '1';
            quantityWrapper.style.display = allowQuantity ? '' : 'none';
            if (!allowQuantity) quantityInput.value = 1;
        }
    })();

    // عند تغيير الفئة
    categorySelect.addEventListener('change', function() {
        loadServicesForCategory(this.value);
        resetPricing();
    });

    // عند تغيير الخدمة
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const allowQuantity = selectedOption.dataset.allowQuantity === '1';
        if (selectedOption.value && selectedOption.dataset.price) {
            originalPriceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
            quantityWrapper.style.display = allowQuantity ? '' : 'none';
            if (!allowQuantity) quantityInput.value = 1;
            calculateFinalAmount();
        } else {
            resetPricing();
            quantityWrapper.style.display = 'none';
            quantityInput.value = 1;
        }
    });

    // عند تغيير مبلغ الخصم
    discountAmountInput.addEventListener('input', calculateFinalAmount);

    // حساب المبلغ النهائي عند التحميل
    calculateFinalAmount();
});
</script>
@endpush

@endsection