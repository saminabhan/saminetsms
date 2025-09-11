@extends('layouts.app')

@section('title', 'إنشاء فاتورة جديدة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    إنشاء فاتورة جديدة
                </h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-2"></i>
                    العودة للقائمة
                </a>
            </div>

            <div class="card-body">
                <form action="{{ route('invoices.store') }}" method="POST">
                    @csrf

                    <!-- اختيار نوع العميل -->
                    <div class="mb-3">
                        <label class="form-label">نوع العميل <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="client_type" id="subscriber_type" value="subscriber" checked>
                            <label class="btn btn-outline-primary" for="subscriber_type">مشترك</label>

                            <input type="radio" class="btn-check" name="client_type" id="distributor_type" value="distributor">
                            <label class="btn btn-outline-primary" for="distributor_type">موزع / نقطة بيع</label>
                        </div>
                    </div>

                    <!-- قسم المشترك -->
                    <div id="subscriber_section">
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
                    </div>

                    <!-- قسم الموزع -->
                    <div id="distributor_section" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="distributor_id" class="form-label">الموزع / نقطة البيع <span class="text-danger">*</span></label>
                                <select name="distributor_id" id="distributor_id" class="form-select">
                                    <option value="">اختر الموزع</option>
                                    @foreach($distributors as $distributor)
                                        <option value="{{ $distributor->id }}">{{ $distributor->name }} - {{ $distributor->type == 'distributor' ? 'موزع' : 'نقطة بيع' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="distributor_card_id" class="form-label">الكروت المتاحة <span class="text-danger">*</span></label>
                                <select name="distributor_card_id" id="distributor_card_id" class="form-select" disabled>
                                    <option value="">اختر الموزع أولاً</option>
                                </select>
                            </div>
                        </div>

                        <div id="card_info" class="alert alert-info" style="display:none;">
                            <div class="row">
                                <div class="col-md-4"><strong>الكمية المتاحة:</strong> <span id="card_available_quantity">0</span></div>
                                <div class="col-md-4"><strong>سعر الكرت:</strong> <span id="card_price">0</span> ش.ج</div>
                                <div class="col-md-4"><strong>المبلغ المتبقي:</strong> <span id="card_remaining">0</span> ش.ج</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="distributor_quantity" class="form-label">كمية البيع <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_distributor" id="distributor_quantity" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-6">
                                <label for="distributor_total" class="form-label">إجمالي المبلغ</label>
                                <input type="number" id="distributor_total" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="distributor_notes" class="form-label">ملاحظات</label>
                            <textarea name="notes_distributor" id="distributor_notes" rows="3" class="form-control"></textarea>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات (مشتركة) -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>إلغاء</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>إنشاء الفاتورة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // منطق تبديل الأقسام حسب نوع العميل
    const subscriberSection = document.getElementById('subscriber_section');
    const distributorSection = document.getElementById('distributor_section');
    const clientRadios = document.querySelectorAll('input[name="client_type"]');

    clientRadios.forEach(radio => radio.addEventListener('change', function() {
        if(this.value === 'subscriber') {
            subscriberSection.style.display = '';
            distributorSection.style.display = 'none';
            // إزالة required من حقول الموزع
            document.getElementById('distributor_id').removeAttribute('required');
            document.getElementById('distributor_card_id').removeAttribute('required');
            document.getElementById('distributor_quantity').removeAttribute('required');
            // إضافة required لحقول المشترك
            document.getElementById('subscriber_id').setAttribute('required', 'required');
            document.getElementById('service_id').setAttribute('required', 'required');
        } else {
            subscriberSection.style.display = 'none';
            distributorSection.style.display = '';
            // إزالة required من حقول المشترك
            document.getElementById('subscriber_id').removeAttribute('required');
            document.getElementById('service_id').removeAttribute('required');
            // إضافة required لحقول الموزع
            document.getElementById('distributor_id').setAttribute('required', 'required');
            document.getElementById('distributor_card_id').setAttribute('required', 'required');
            document.getElementById('distributor_quantity').setAttribute('required', 'required');
        }
    }));

    // منطق الموزع
    const distributorSelect = document.getElementById('distributor_id');
    const distributorCardSelect = document.getElementById('distributor_card_id');
    const cardInfo = document.getElementById('card_info');
    const cardAvailableQuantity = document.getElementById('card_available_quantity');
    const cardPrice = document.getElementById('card_price');
    const cardRemaining = document.getElementById('card_remaining');
    const distributorQuantity = document.getElementById('distributor_quantity');
    const distributorTotal = document.getElementById('distributor_total');

    distributorSelect.addEventListener('change', function() {
        const distributorId = this.value;
        distributorCardSelect.disabled = true;
        distributorCardSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        cardInfo.style.display = 'none';
        distributorQuantity.value = 1;
        distributorTotal.value = '';
        
        if(distributorId) {
            fetch(`/invoices/get-distributor-cards?distributor_id=${distributorId}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`خطأ في الشبكة: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Cards data:', data);
                    
                    distributorCardSelect.innerHTML = '<option value="">اختر الكرت</option>';
                    
                    if(data.error) {
                        distributorCardSelect.innerHTML = `<option value="">${data.error}</option>`;
                        distributorCardSelect.disabled = true;
                        return;
                    }
                    
                    if(data.cards && data.cards.length > 0) {
                        data.cards.forEach(card => {
                            const opt = document.createElement('option');
                            opt.value = card.id;
                            opt.textContent = `${card.service_name} (متاح: ${card.quantity_available})`;
                            opt.dataset.availableQuantity = card.quantity_available;
                            opt.dataset.cardPrice = card.card_price;
                            opt.dataset.remainingAmount = card.remaining_amount;
                            distributorCardSelect.appendChild(opt);
                        });
                        distributorCardSelect.disabled = false;
                    } else {
                        distributorCardSelect.innerHTML = '<option value="">لا توجد كروت متاحة لهذا الموزع</option>';
                        distributorCardSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching cards:', error);
                    distributorCardSelect.innerHTML = '<option value="">حدث خطأ في جلب الكروت</option>';
                    distributorCardSelect.disabled = true;
                    alert(`خطأ في جلب البيانات: ${error.message}`);
                });
        } else {
            distributorCardSelect.innerHTML = '<option value="">اختر الموزع أولاً</option>';
            distributorCardSelect.disabled = true;
        }
    });

    distributorCardSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if(opt.value) {
            const availableQty = parseInt(opt.dataset.availableQuantity);
            const cardPriceValue = parseFloat(opt.dataset.cardPrice);
            const remainingValue = parseFloat(opt.dataset.remainingAmount);
            
            cardAvailableQuantity.textContent = availableQty;
            cardPrice.textContent = cardPriceValue.toFixed(2);
            cardRemaining.textContent = remainingValue.toFixed(2);
            cardInfo.style.display = 'block';
            
            // تحديد الحد الأقصى للكمية
            distributorQuantity.setAttribute('max', availableQty);
            
            // التأكد من أن الكمية المدخلة لا تتجاوز المتاح
            if(parseInt(distributorQuantity.value) > availableQty) {
                distributorQuantity.value = Math.min(availableQty, 1);
            }
            
            calculateDistributorTotal();
        } else {
            cardInfo.style.display = 'none';
            distributorTotal.value = '';
            distributorQuantity.removeAttribute('max');
        }
    });

    distributorQuantity.addEventListener('input', function() {
        const maxQty = parseInt(this.getAttribute('max'));
        const currentValue = parseInt(this.value);
        
        // التأكد من أن القيمة لا تقل عن 1
        if(currentValue < 1) {
            this.value = 1;
        }
        
        // التأكد من أن القيمة لا تتجاوز المتاح
        if(maxQty && currentValue > maxQty) {
            this.value = maxQty;
            alert(`الحد الأقصى للكمية المتاحة هو ${maxQty}`);
        }
        
        calculateDistributorTotal();
    });

    // منع كتابة أرقام سالبة أو صفر
    distributorQuantity.addEventListener('keydown', function(e) {
        // منع المفاتيح: - + e E . ,
        if (['-', '+', 'e', 'E', '.', ','].includes(e.key)) {
            e.preventDefault();
        }
    });

    function calculateDistributorTotal() {
        const opt = distributorCardSelect.options[distributorCardSelect.selectedIndex];
        if(opt.value && distributorQuantity.value) {
            const cardPriceValue = parseFloat(opt.dataset.cardPrice);
            const quantity = parseInt(distributorQuantity.value) || 1;
            const total = cardPriceValue * quantity;
            distributorTotal.value = total.toFixed(2);
        } else {
            distributorTotal.value = '';
        }
    }

    // منطق المشترك (باقي الكود كما هو)
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
@endsection