@extends('layouts.app')

@section('title', 'إضافة كروت للموزع')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        إضافة كروت للموزع: {{ $distributor->name }}
                    </h5>
                    <a href="{{ route('distributors.show', $distributor) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-2"></i>
                        العودة للموزع
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- معلومات الموزع -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>الاسم:</strong> {{ $distributor->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>النوع:</strong> 
                            <span class="badge bg-{{ $distributor->type == 'distributor' ? 'primary' : 'info' }}">
                                {{ $distributor->type == 'distributor' ? 'موزع' : 'نقطة بيع' }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>إجمالي الكروت:</strong> {{ number_format($distributor->total_cards) }}
                        </div>
                        <div class="col-md-3">
                            <strong>المبلغ المتبقي:</strong> 
                            <span class="text-danger">{{ number_format($distributor->remaining_amount, 2) }} ش.ج</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('distributors.store-cards', $distributor) }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- فئة الخدمة -->
                        <div class="col-md-6 mb-3">
                            <label for="service_category" class="form-label">فئة الخدمة</label>
                            <select id="service_category" class="form-select">
                                <option value="">اختر فئة الخدمة</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name_ar ?? $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- الخدمة -->
                        <div class="col-md-6 mb-3">
                            <label for="service_id" class="form-label">الخدمة <span class="text-danger">*</span></label>
                            <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required disabled>
                                <option value="">اختر فئة الخدمة أولاً</option>
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- الكمية وسعر الكرت -->
                    <div class="row">
                        <div class="col-md-4 mb-3" id="quantity_wrapper" style="display: none;">
                            <label for="quantity" class="form-label">كمية الكروت</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="card_price" class="form-label">سعر الكرت الواحد</label>
                            <div class="input-group">
                                <input type="number" name="card_price" id="card_price" class="form-control" readonly step="0.01" value="0">
                                <span class="input-group-text">ش.ج</span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="total_amount" class="form-label">إجمالي المبلغ</label>
                            <div class="input-group">
                                <input type="number" id="total_amount" class="form-control" readonly step="0.01">
                                <span class="input-group-text">ش.ج</span>
                            </div>
                        </div>
                    </div>

                    <!-- تاريخ الاستلام -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="received_at" class="form-label">تاريخ الاستلام <span class="text-danger">*</span></label>
                            <input type="date" name="received_at" id="received_at" class="form-control" value="{{ old('received_at', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <!-- ملاحظات -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="أدخل أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('distributors.show', $distributor) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>
                            إضافة الكروت
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
    const serviceCategorySelect = document.getElementById('service_category');
    const serviceSelect = document.getElementById('service_id');
    const quantityWrapper = document.getElementById('quantity_wrapper');
    const quantityInput = document.getElementById('quantity');
    const cardPriceInput = document.getElementById('card_price');
    const totalAmountInput = document.getElementById('total_amount');

    const servicesData = @json($servicesByCategory);

    // تغيير فئة الخدمة
    serviceCategorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';
        serviceSelect.disabled = true;
        cardPriceInput.value = '0';
        totalAmountInput.value = '';

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
        if (!selectedOption.value) {
            cardPriceInput.value = '0';
            totalAmountInput.value = '';
            quantityWrapper.style.display = 'none';
            quantityInput.value = 1;
            return;
        }

        const price = parseFloat(selectedOption.dataset.price) || 0;
        const allowQuantity = selectedOption.dataset.allowQuantity === '1';

        cardPriceInput.value = price.toFixed(2);

        if (allowQuantity) {
            quantityWrapper.style.display = '';
            quantityInput.value = 1;
        } else {
            quantityWrapper.style.display = 'none';
            quantityInput.value = 1;
        }

        calculateTotal();
    });

    quantityInput.addEventListener('input', calculateTotal);

    function calculateTotal() {
        const quantity = parseInt(quantityInput.value) || 1;
        const price = parseFloat(cardPriceInput.value) || 0;
        totalAmountInput.value = (quantity * price).toFixed(2);
    }
});
</script>
@endpush

@endsection
