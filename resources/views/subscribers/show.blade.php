
@extends('layouts.app')

@section('title', 'عرض المشترك')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-user me-2"></i>
        {{ $subscriber->name }}
    </h1>
    <a href="{{ route('subscribers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        الرجوع لقائمة المشتركين
    </a>
</div>

<!-- معلومات المشترك -->
<div class="card mb-4 animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>رقم الهاتف:</strong> <span class="font-monospace">{{ $subscriber->phone }}</span>
            </div>
            <div class="col-md-4">
                <strong>الحالة:</strong> 
                @if($subscriber->is_active)
                    <span class="badge bg-success">نشط</span>
                @else
                    <span class="badge bg-secondary">غير نشط</span>
                @endif
            </div>
            <div class="col-md-4">
                <strong>عدد الرسائل:</strong> 
                <span class="badge bg-info">{{ $subscriber->messages()->count() }}</span>
            </div>
        </div>
    </div>
</div>

<!-- جدول الرسائل -->
<div class="card animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-envelope me-2"></i>
            الرسائل المرسلة للمشترك
        </h5>
    </div>
    <div class="card-body">
        @if($messages->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>الموضوع / المحتوى</th>
                            <th>الحالة</th>
                            <th>تاريخ الإرسال</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                        <tr>
                            <td>{{ $loop->iteration + ($messages->currentPage() - 1) * $messages->perPage() }}</td>
                            <td>{{ $message->message_content }}</td>
                            <td>
                                @if($message->status == 'sent')
                                    <span class="badge bg-success">مرسلة</span>
                                @elseif($message->status == 'failed')
                                    <span class="badge bg-danger">فشلت</span>
                                @else
                                    <span class="badge bg-secondary">{{ $message->status }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $message->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $messages->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد رسائل لهذا المشترك</h5>
            </div>
        @endif
    </div>
</div>
@endsection
