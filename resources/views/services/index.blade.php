@extends('layouts.app')

@section('title', 'إدارة الخدمات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-cogs me-2"></i>
        إدارة الخدمات
    </h1>
    <div>
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            إضافة خدمة جديدة
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($services->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>الخدمة</th>
                            <th>الفئة</th>
                            <th>السعر</th>
                            <th>المدة</th>
                            <th>السرعة</th>
                            <th>حد البيانات</th>
                            <th>يسمح بالكمية</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td>
                                    <strong>{{ $service->name_ar }}</strong>
                                    @if($service->description)
                                        <br><small class="text-muted">{{ $service->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $service->category->name_ar }}</span>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($service->price, 2) }} ش.ج</strong>
                                </td>
                                <td>
                                    @if($service->duration_hours || $service->duration_days)
                                        <small>
                                            @if($service->duration_hours)
                                                {{ $service->duration_hours }} ساعة
                                            @endif
                                            @if($service->duration_days)
                                                @if($service->duration_hours) / @endif
                                                {{ $service->duration_days }} يوم
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $service->speed ?? '-' }}
                                </td>
                                <td>
                                    {{ $service->data_limit ?? '-' }}
                                </td>
                                <td>
                                    @if($service->allow_quantity)
                                        <span class="badge bg-info">نعم</span>
                                    @else
                                        <span class="badge bg-secondary">لا</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('services.show', $service) }}" 
                                           class="btn btn-outline-info btn-sm" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('services.edit', $service) }}" 
                                           class="btn btn-outline-warning btn-sm" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('services.toggle', $service) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-outline-{{ $service->is_active ? 'secondary' : 'success' }} btn-sm" 
                                                    title="{{ $service->is_active ? 'إلغاء تفعيل' : 'تفعيل' }}">
                                                <i class="fas fa-{{ $service->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        @if($service->invoices()->count() == 0)
                                            <form action="{{ route('services.destroy', $service) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف">
                                                    <i class="fas fa-trash"></i>
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
            <div class="d-flex justify-content-center">
                {{ $services->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-cogs fa-4x text-muted mb-3"></i>
                <p class="text-muted">لا توجد خدمات متاحة</p>
                <a href="{{ route('services.create') }}" class="btn btn-primary">
                    إضافة خدمة جديدة
                </a>
            </div>
        @endif
    </div>
</div>
@endsection