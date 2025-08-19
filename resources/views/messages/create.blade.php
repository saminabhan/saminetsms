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
                    
                    <!-- نص الرسالة -->
                    <div class="mb-4">
                        <label for="message_content" class="form-label">
                            <i class="fas fa-comment me-1"></i>
                            نص الرسالة
                        </label>
                        <textarea class="form-control @error('message_content') is-invalid @enderror" 
                                  id="message_content" name="message_content" rows="5" 
                                  placeholder="اكتب نص الرسالة هنا..." required>{{ old('message_content') }}</textarea>
                        
                        <div class="d-flex justify-content-between mt-2">
                            <div>
                                @error('message_content')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">
                                <span id="charCount">0</span> / 1000 حرف
                            </small>
                        </div>
                    </div>
                    
                    <!-- أزرار الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary" id="sendButton" disabled>
                            <i class="fas fa-paper-plane me-1"></i>
                            إرسال الرسالة
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="sendingSpinner"></span>
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
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
    const sendButton = document.getElementById('sendButton');
    const messageForm = document.getElementById('messageForm');
    const sendingSpinner = document.getElementById('sendingSpinner');

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
            
            // تحديث حالة "تحديد الكل"
            const checkedCount = document.querySelectorAll('.subscriber-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === subscriberCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < subscriberCheckboxes.length;
        });
    });

    // عداد الأحرف
    messageTextarea.addEventListener('input', function() {
        const length = messageTextarea.value.length;
        charCountElement.textContent = length;
        
        if (length > 1000) {
            charCountElement.classList.add('text-danger');
        } else {
            charCountElement.classList.remove('text-danger');
        }
        
        updateSendButton();
    });

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

    // إظهار spinner عند الإرسال
    messageForm.addEventListener('submit', function() {
        sendButton.disabled = true;
        sendingSpinner.classList.remove('d-none');
        sendButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i> جاري الإرسال... <span class="spinner-border spinner-border-sm ms-2"></span>';
    });

    // التحديث الأولي
    updateSelectedCount();
    updateSendButton();
});
</script>
@endsection
