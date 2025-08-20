@extends('layouts.app')

@section('title', 'تفاصيل الرسالة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card animate__animated animate__fadeInUp animate__delay-0.5s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    تفاصيل الرسالة
                </h5>
                <a href="{{ route('messages.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    العودة للقائمة
                </a>
            </div>
            <div class="card-body">
                <!-- معلومات المشترك -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">المشترك</h6>
                        <p class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            <strong>{{ $message->subscriber->name }}</strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">رقم الهاتف</h6>
                        <p class="mb-0">
                            <i class="fas fa-phone me-2"></i>
                            <span class="font-monospace">{{ $message->subscriber->phone }}</span>
                        </p>
                    </div>
                </div>

                <!-- نص الرسالة -->
                <div class="mb-4">
                    <h6 class="text-muted mb-2">نص الرسالة</h6>
                    <div class="border rounded p-3 bg-light">
                        {{ $message->message_content }}
                    </div>
                </div>

                <!-- معلومات الإرسال -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">الحالة</h6>
                        {!! $message->status_badge !!}
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">تاريخ الإرسال</h6>
                        <p class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $message->created_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">منذ</h6>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            {{ $message->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <!-- استجابة الـ API -->
                @if($message->api_response)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">استجابة الخادم</h6>
                        <div class="border rounded p-3 bg-light font-monospace">
                            <small>{{ $message->api_response }}</small>
                        </div>
                    </div>
                @endif

                <!-- الإجراءات -->
                @if($message->status === 'failed')
                    <div class="text-center">
                        <form method="POST" action="{{ route('messages.resend', $message) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-redo me-1"></i>
                                إعادة الإرسال
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection