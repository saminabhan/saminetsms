@extends('layouts.app')

@section('title', 'الموزعين ونقاط البيع')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4>
                <i class="fas fa-store me-2"></i>
                الموزعين ونقاط البيع
            </h4>
            <a href="{{ route('distributors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                إضافة موزع جديد
            </a>
        </div>
    </div>
</div>

<!-- فلاتر البحث -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('distributors.index') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label">البحث</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="اسم الموزع أو رقم الهاتف">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="type" class="form-label">النوع</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">الكل</option>
                                <option value="distributor" {{ request('type') == 'distributor' ? 'selected' : '' }}>موزع</option>
                                <option value="sales_point" {{ request('type') == 'sales_point' ? 'selected' : '' }}>نقطة بيع</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="is_active" class="form-label">الحالة</label>
                            <select name="is_active" id="is_active" class="form-select">
                                <option value="">الكل</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                                <a href="{{ route('distributors.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    مسح
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- جدول الموزعين -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @if($distributors->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>الهاتف</th>
                                    <th>إجمالي الكروت</th>
                                    <th>الكروت المتاحة</th>
                                    <th>إجمالي المبلغ</th>
                                    <th>المبلغ المتبقي</th>
                                    <th>حالة الدفع</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($distributors as $distributor)
                                <tr>
                                    <td>
                                        <strong>{{ $distributor->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $distributor->type == 'distributor' ? 'primary' : 'info' }}">
                                            {{ $distributor->type == 'distributor' ? 'موزع' : 'نقطة بيع' }}
                                        </span>
                                    </td>
                                    <td>{{ $distributor->phone ?? '-' }}</td>
                                    <td>{{ number_format($distributor->total_cards) }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($distributor->available_cards) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($distributor->total_amount, 2) }} ش.ج</td>
                                    <td>
                                        <strong class="text-danger">
                                            {{ number_format($distributor->remaining_amount, 2) }} ش.ج
                                        </strong>
                                    </td>
                                    <td>
                                        @php
                                            $paymentStatusColors = [
                                                'unpaid' => 'danger',
                                                'partial' => 'warning',
                                                'paid' => 'success'
                                            ];
                                            $paymentStatusTexts = [
                                                'unpaid' => 'غير مدفوع',
                                                'partial' => 'مدفوع جزئياً',
                                                'paid' => 'مدفوع'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $paymentStatusColors[$distributor->payment_status] }}">
                                            {{ $paymentStatusTexts[$distributor->payment_status] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $distributor->is_active ? 'success' : 'secondary' }}">
                                            {{ $distributor->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('distributors.show', $distributor) }}" 
                                               class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('distributors.add-cards', $distributor) }}" 
                                               class="btn btn-sm btn-outline-success" title="إضافة كروت">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="{{ route('distributors.edit', $distributor) }}" 
                                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $distributors->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد موزعين مسجلين</p>
                        <a href="{{ route('distributors.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            إضافة موزع جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection