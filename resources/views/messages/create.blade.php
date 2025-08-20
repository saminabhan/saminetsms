@extends('layouts.app')

@section('title', 'إرسال رسالة جديدة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paper-plane me-2"></i>
                    إرسال رسالة جديدة
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('messages.store') }}" id="messageForm">
                    @csrf
                    
                    <!-- اختيار المشتركين -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-users me-1"></i>
                            اختيار المشتركين
                        </label>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        تحديد الكل
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted" id="selectedCount">
                                    لم يتم تحديد أي مشترك
                                </small>
                            </div>
                        </div>
                        
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @if($subscribers->count() > 0)
                                <div class="row">
                                    @foreach($subscribers as $subscriber)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input subscriber-checkbox" 
                                                       type="checkbox" 
                                                       name="subscriber_ids[]" 
                                                       value="{{ $subscriber->id }}" 
                                                       id="subscriber_{{ $subscriber->id }}">
                                                <label class="form-check-label" for="subscriber_{{ $subscriber->id }}">
                                                    <strong>{{ $subscriber->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $subscriber->phone }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">لا توجد مشتركين نشطين</p>
                                    <a href="{{ route('subscribers.create') }}" class="btn btn-sm btn-primary mt-2">
                                        إضافة مشترك جديد
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        @error('subscriber_ids')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- نظام الذكاء الاصطناعي المتطور -->
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-robot me-2"></i>
                                    مساعد الذكاء الاصطناعي
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- الاقتراحات السريعة -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">اقتراحات سريعة:</label>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100 suggestion-btn" data-suggestion="internet">
                                                <i class="fas fa-wifi me-1"></i> عروض الإنترنت
                                            </button>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <button type="button" class="btn btn-outline-success btn-sm w-100 suggestion-btn" data-suggestion="payment">
                                                <i class="fas fa-credit-card me-1"></i> تذكير بالدفع
                                            </button>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <button type="button" class="btn btn-outline-info btn-sm w-100 suggestion-btn" data-suggestion="maintenance">
                                                <i class="fas fa-tools me-1"></i> أعمال صيانة
                                            </button>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100 suggestion-btn" data-suggestion="thank">
                                                <i class="fas fa-heart me-1"></i> شكر وامتنان
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- كتابة طلب مخصص -->
                                <div class="mb-3">
                                    <label for="aiPrompt" class="form-label fw-bold">
                                        <i class="fas fa-edit me-1"></i>
                                        أو اكتب طلبك بنفسك:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="aiPrompt" 
                                               placeholder="مثال: مشترك عليه 40 شيكل">
                                        <button type="button" class="btn btn-primary" id="generateBtn">
                                            <i class="fas fa-magic me-1"></i> إنشاء
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        أمثلة: "عرض خصم 20%"، "مشكلة في الشبكة"، "عيد سعيد"، "باقة جديدة 50 شيكل"
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- نص الرسالة -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="message_content" class="form-label mb-0">
                                <i class="fas fa-comment me-1"></i>
                                نص الرسالة
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn">
                                <i class="fas fa-eraser me-1"></i> مسح
                            </button>
                        </div>

                        <textarea class="form-control @error('message_content') is-invalid @enderror" 
                                  id="message_content" name="message_content" rows="5" 
                                  placeholder="اكتب نص الرسالة هنا أو استخدم مساعد الذكاء الاصطناعي أعلاه..." required>{{ old('message_content') }}</textarea>

                        <div class="d-flex justify-content-between mt-2">
                            <div>
                                @error('message_content')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex gap-3">
                                <small class="text-muted">
                                    <span id="charCount">0</span> / 1000 حرف
                                </small>
                                <small class="text-info" id="smsCount">
                                    رسالة واحدة
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- معاينة الرسالة -->
                    <div class="mb-4" id="previewSection" style="display: none;">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye me-1"></i>
                                    معاينة الرسالة
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="phone-mockup p-3 border rounded" style="background: #f8f9fa;">
                                    <div class="message-bubble bg-primary text-white p-3 rounded" style="max-width: 300px;">
                                        <p class="mb-0" id="messagePreview"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary btn-lg" id="sendButton" disabled>
                            <i class="fas fa-paper-plane me-1"></i>
                            إرسال الرسالة
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="sendingSpinner"></span>
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-1"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>جاري إنشاء الرسالة...</h5>
                <p class="text-muted mb-0">يرجى الانتظار بينما نقوم بإنشاء رسالة مناسبة لك</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const subscriberCheckboxes = document.querySelectorAll('.subscriber-checkbox');
    const selectedCountElement = document.getElementById('selectedCount');
    const messageTextarea = document.getElementById('message_content');
    const charCountElement = document.getElementById('charCount');
    const smsCountElement = document.getElementById('smsCount');
    const sendButton = document.getElementById('sendButton');
    const messageForm = document.getElementById('messageForm');
    const sendingSpinner = document.getElementById('sendingSpinner');
    const suggestionBtns = document.querySelectorAll('.suggestion-btn');
    const aiPrompt = document.getElementById('aiPrompt');
    const generateBtn = document.getElementById('generateBtn');
    const clearBtn = document.getElementById('clearBtn');
    const previewSection = document.getElementById('previewSection');
    const messagePreview = document.getElementById('messagePreview');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

    // تحديد/إلغاء تحديد الكل
    selectAllCheckbox.addEventListener('change', function() {
        subscriberCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
        updateSendButton();
    });

    // تحديث عداد المحددين عند تغيير أي checkbox
    subscriberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updateSendButton();
            
            const checkedCount = document.querySelectorAll('.subscriber-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === subscriberCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < subscriberCheckboxes.length;
        });
    });

    // عداد الأحرف وعدد الرسائل
    messageTextarea.addEventListener('input', function() {
        const length = messageTextarea.value.length;
        charCountElement.textContent = length;
        
        if (length > 1000) {
            charCountElement.classList.add('text-danger');
        } else {
            charCountElement.classList.remove('text-danger');
        }
        
        // حساب عدد الرسائل (160 حرف للرسالة الواحدة)
        const smsCount = Math.ceil(length / 160) || 1;
        smsCountElement.textContent = smsCount === 1 ? 'رسالة واحدة' : `${smsCount} رسائل`;
        
        updateSendButton();
        updatePreview();
    });

    // الاقتراحات السريعة
    suggestionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const suggestion = btn.dataset.suggestion;
            generateMessage(suggestion);
        });
    });

    // زر إنشاء مخصص
    generateBtn.addEventListener('click', function() {
        const prompt = aiPrompt.value.trim();
        if (prompt) {
            generateMessage(prompt);
        } else {
            alert('يرجى كتابة طلبك أولاً');
        }
    });

    // زر المسح
    clearBtn.addEventListener('click', function() {
        messageTextarea.value = '';
        aiPrompt.value = '';
        messageTextarea.dispatchEvent(new Event('input'));
    });

    // Enter في حقل الطلب المخصص
    aiPrompt.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            generateBtn.click();
        }
    });

    // دالة إنشاء الرسالة
    async function generateMessage(prompt) {
        loadingModal.show();
        generateBtn.disabled = true;
        suggestionBtns.forEach(btn => btn.disabled = true);

        try {
            const response = await fetch("{{ route('messages.aiSuggest') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ prompt: prompt })
            });

            const data = await response.json();

            if (data.suggestion) {
                messageTextarea.value = data.suggestion;
                messageTextarea.dispatchEvent(new Event('input'));
                
                // مسح حقل الطلب المخصص
                aiPrompt.value = '';
            } else {
                alert(data.error || "لم يتمكن النظام من توليد نص.");
            }
        } catch (error) {
            console.error(error);
            alert("حدث خطأ أثناء طلب المساعدة من الذكاء الاصطناعي.");
        } finally {
            loadingModal.hide();
            generateBtn.disabled = false;
            suggestionBtns.forEach(btn => btn.disabled = false);
        }
    }

    // تحديث عداد المحددين
    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.subscriber-checkbox:checked').length;
        if (checkedCount === 0) {
            selectedCountElement.textContent = 'لم يتم تحديد أي مشترك';
        } else if (checkedCount === 1) {
            selectedCountElement.textContent = 'تم تحديد مشترك واحد';
        } else {
            selectedCountElement.textContent = `تم تحديد ${checkedCount} مشترك`;
        }
    }

    // تحديث حالة زر الإرسال
    function updateSendButton() {
        const checkedCount = document.querySelectorAll('.subscriber-checkbox:checked').length;
        const messageLength = messageTextarea.value.length;
        sendButton.disabled = checkedCount === 0 || messageLength === 0 || messageLength > 1000;
    }

    // تحديث معاينة الرسالة
    function updatePreview() {
        const text = messageTextarea.value.trim();
        if (text) {
            messagePreview.textContent = text;
            previewSection.style.display = 'block';
        } else {
            previewSection.style.display = 'none';
        }
    }

    // إظهار spinner عند الإرسال
    messageForm.addEventListener('submit', function() {
        sendButton.disabled = true;
        sendingSpinner.classList.remove('d-none');
        sendButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i> جاري الإرسال... <span class="spinner-border spinner-border-sm ms-2"></span>';
    });

    // التحديث الأولي
    updateSelectedCount();
    updateSendButton();
    updatePreview();
});
</script>
@endsection