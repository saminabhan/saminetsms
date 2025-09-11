@extends('layouts.app')

@section('title', 'إدارة الفواتير')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-file-invoice me-2"></i>
        إدارة الفواتير
    </h1>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إنشاء فاتورة جديدة
    </a>
</div>

<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="subscriber_id" class="form-label">المشترك</label>
                <select name="subscriber_id" id="subscriber_id" class="form-select">
                    <option value="">جميع المشتركين</option>
                    @foreach($subscribers as $subscriber)
                        <option value="{{ $subscriber->id }}" 
                                {{ request('subscriber_id') == $subscriber->id ? 'selected' : '' }}>
                            {{ $subscriber->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">حالة الفاتورة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                    <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="payment_status" class="form-label">حالة الدفع</label>
                <select name="payment_status" id="payment_status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">من تاريخ</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- الفواتير -->
<div class="card">
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المشترك</th>
                            <th>الخدمة</th>
                            <th>المبلغ الأصلي</th>
                            <th>الخصم</th>
                            <th>المبلغ النهائي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة الدفع</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </td>
                                <td>{{ $invoice->subscriber->name }}</td>
                                <td>
                                    <small class="text-muted">{{ $invoice->service->name_ar }}</small>
                                </td>
                                <td>{{ number_format($invoice->original_price, 2) }} ش.ج</td>
                                <td>
                                    @if($invoice->discount_amount > 0)
                                        <span class="text-success">{{ number_format($invoice->discount_amount, 2) }} ش.ج</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($invoice->final_amount, 2) }} ش.ج</strong></td>
                                <td>{{ number_format($invoice->paid_amount, 2) }} ش.ج</td>
                                <td>
                                    @if($invoice->remaining_amount > 0)
                                        <span class="text-danger">{{ number_format($invoice->remaining_amount, 2) }} ش.ج</span>
                                    @else
                                        <span class="text-success">-</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($invoice->payment_status)
                                        @case('unpaid')
                                            <span class="badge bg-danger">غير مدفوعة</span>
                                            @break
                                        @case('partial')
                                            <span class="badge bg-warning">مدفوعة جزئياً</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-success">مدفوعة</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('invoices.show', $invoice) }}" 
                                           class="btn btn-outline-info btn-sm" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($invoice->status !== 'paid')
                                            <a href="{{ route('invoices.edit', $invoice) }}" 
                                               class="btn btn-outline-warning btn-sm" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($invoice->paid_amount == 0)
                                            <form action="{{ route('invoices.destroy', $invoice) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
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
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <p class="text-muted">لا توجد فواتير متاحة</p>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    إنشاء فاتورة جديدة
                </a>
            </div>
        @endif
    </div>
</div>
@endsection