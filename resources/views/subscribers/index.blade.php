@extends('layouts.app')

@section('title', 'قائمة المشتركين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-users me-2"></i>
        المشتركين ({{ $subscribers->total() }})
    </h1>
    <a href="{{ route('subscribers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        إضافة مشترك جديد
    </a>
</div>

<div class="card animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="card-body">
        @if($subscribers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>رقم الهاتف</th>
                            <th>الحالة</th>
                            <th>عدد الرسائل</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscribers as $subscriber)
                        <tr>
                            <td>{{ $loop->iteration + ($subscribers->currentPage() - 1) * $subscribers->perPage() }}</td>
                            <td>
                                <strong>{{ $subscriber->name }}</strong>
                            </td>
                            <td>
                                <span class="font-monospace">{{ $subscriber->phone }}</span>
                            </td>
                            <td>
                                @if($subscriber->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $subscriber->messages_count ?? 0 }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $subscriber->created_at->format('Y-m-d') }}</small>
                            </td>
                            <td>
    <div class="btn-group" role="group">
        <!-- عرض - أول زر -->
        <a href="{{ route('subscribers.show', $subscriber) }}" 
           class="btn btn-sm btn-outline-primary"
           style="border-top-right-radius: 10px; border-bottom-right-radius: 10px; border-top-left-radius: 0; border-bottom-left-radius: 0; transition: all 0.2s; padding: 0.45rem 0.9rem;"
           title="عرض">
            <i class="fas fa-eye"></i>
        </a>

        <!-- تعديل - زر وسط -->
        <a href="{{ route('subscribers.edit', $subscriber) }}" 
           class="btn btn-sm btn-outline-warning"
           style="border-radius: 0; transition: all 0.2s; padding: 0.45rem 0.9rem;"
           title="تعديل">
            <i class="fas fa-edit"></i>
        </a>

        <!-- تفعيل/إلغاء - آخر زر -->
        <form method="POST" action="{{ route('subscribers.toggle', $subscriber) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="btn btn-sm btn-outline-{{ $subscriber->is_active ? 'secondary' : 'success' }}"
                    style="border-top-left-radius: 10px; border-bottom-left-radius: 10px; border-top-right-radius: 0; border-bottom-right-radius: 0; transition: all 0.2s; padding: 0.45rem 0.9rem;"
                    title="{{ $subscriber->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}">
                <i class="fas fa-{{ $subscriber->is_active ? 'pause' : 'play' }}"></i>
            </button>
        </form>
    </div>
</td>

<style>
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }
</style>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $subscribers->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد مشتركين حالياً</h5>
                <p class="text-muted">ابدأ بإضافة المشتركين لتتمكن من إرسال الرسائل لهم</p>
                <a href="{{ route('subscribers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    إضافة مشترك جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
