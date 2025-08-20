{{-- resources/views/messages/index.blade.php --}}
@extends('layouts.app')

@section('title', 'سجل الرسائل')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-envelope me-2"></i>
        سجل الرسائل ({{ $messages->total() }})
    </h1>
    <a href="{{ route('messages.create') }}" class="btn btn-primary">
        <i class="fas fa-paper-plane me-1"></i>
        إرسال رسالة جديدة
    </a>
</div>

<div class="card animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="card-body">
        @if($messages->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>المشترك</th>
                            <th>رقم الهاتف</th>
                            <th>نص الرسالة</th>
                            <th>الحالة</th>
                            <th>تاريخ الإرسال</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                        <tr>
                            <td>{{ $loop->iteration + ($messages->currentPage() - 1) * $messages->perPage() }}</td>
                            <td>
                                <strong>{{ $message->subscriber->name }}</strong>
                            </td>
                            <td>
                                <span class="font-monospace">{{ $message->subscriber->phone }}</span>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    {{ Str::limit($message->message_content, 50) }}
                                </div>
                            </td>
                            <td>
                                {!! $message->status_badge !!}
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $message->created_at->format('Y-m-d H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('messages.show', $message) }}" 
                                       class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($message->status === 'failed')
                                        <form method="POST" action="{{ route('messages.resend', $message) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                    title="إعادة الإرسال">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
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
                <h5 class="text-muted">لا توجد رسائل حالياً</h5>
                <p class="text-muted">ابدأ بإرسال الرسائل للمشتركين</p>
                <a href="{{ route('messages.create') }}" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>
                    إرسال رسالة جديدة
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
