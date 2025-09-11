@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- تفاصيل الفاتورة -->
        <div class="card">
            <div class="card-header">
                <div class="print-only text-center mb-2" style="display: none;">
                    <img src="{{ asset('assets/images/sami-logo.png') }}" alt="Logo" style="height: 70px;">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        فاتورة رقم: {{  $invoice->invoice_number }}
                    </h5>
                    <div>
                        @switch($invoice->payment_status)
                            @case('unpaid')
                                <span class="badge bg-danger fs-6">غير مدفوعة</span>
                                @break
                            @case('partial')
                                <span class="badge bg-warning fs-6">مدفوعة جزئياً</span>
                                @break
                            @case('paid')
                                <span class="badge bg-success fs-6">مدفوعة</span>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
            <div class="card-body">
               
                <div class="row">
                    <!-- معلومات المشترك -->
                    <div class="col-md-6 mb-4">
                        <h6 class="text-muted mb-3">معلومات المشترك</h6>
                        <div class="mb-2">
                            <strong>الاسم:</strong>
                            <a href="{{ route('subscribers.show', $invoice->subscriber) }}" class="text-decoration-none">
                                {{ $invoice->subscriber->name }}
                            </a>
                        </div>
                        <div class="mb-2">
                            <strong>رقم الهاتف:</strong> {{ $invoice->subscriber->phone }}
                        </div>
                    </div>

                    <!-- معلومات الخدمة -->
                    <div class="col-md-6 mb-4">
                        <h6 class="text-muted mb-3">معلومات الخدمة</h6>
                        <div class="mb-2">
                            <strong>الخدمة:</strong> {{ $invoice->service->name_ar }}
                        </div>
                        <div class="mb-2">
                            <strong>الفئة:</strong> {{ $invoice->service->category->name_ar }}
                        </div>
                        <div class="mb-2">
                            <strong>تاريخ البداية:</strong> {{ $invoice->service_start_date->format('Y-m-d') }}
                        </div>
                        <div class="mb-2">
                            <strong>تاريخ الانتهاء:</strong> {{ $invoice->service_end_date->format('Y-m-d') }}
                        </div>
                    </div>
                </div>

                <!-- معلومات الفاتورة -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">تفاصيل الفاتورة</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <td class="bg-light" width="200"><strong>السعر الأصلي</strong></td>
                                    <td>
                                        @if(($invoice->quantity ?? 1) > 1)
                                            {{ number_format(($invoice->original_price / max(1,$invoice->quantity)), 2) }} × {{ $invoice->quantity }} =
                                        @endif
                                        {{ number_format($invoice->original_price, 2) }} ش.ج
                                    </td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td class="bg-light"><strong>مبلغ الخصم</strong></td>
                                    <td class="text-success">- {{ number_format($invoice->discount_amount, 2) }} ش.ج</td>
                                </tr>
                                @endif
                                <tr class="table-info">
                                    <td class="bg-info text-white"><strong>المبلغ النهائي</strong></td>
                                    <td><strong>{{ number_format($invoice->final_amount, 2) }} ش.ج</strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td class="bg-success text-white"><strong>المبلغ المدفوع</strong></td>
                                    <td><strong>{{ number_format($invoice->paid_amount, 2) }} ش.ج</strong></td>
                                </tr>
                                @if($invoice->remaining_amount > 0)
                                <tr class="table-danger">
                                    <td class="bg-danger text-white"><strong>المبلغ المتبقي</strong></td>
                                    <td><strong>{{ number_format($invoice->remaining_amount, 2) }} ش.ج</strong></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">ملاحظات</h6>
                        <div class="alert alert-light">
                            {{ $invoice->notes }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- الدفعات -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">سجل الدفعات</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الطريقة</th>
                                        <th>المبلغ</th>
                                        <th>المستخدم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->paid_at->format('Y-m-d') }}</td>
                                            <td>
                                                @if($payment->method === 'cash')
                                                    <span class="badge bg-info">نقدي</span>
                                                @else
                                                    <span class="badge bg-primary">بنكي</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->user->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">لا توجد دفعات بعد</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- معلومات إضافية -->
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>تم الإنشاء بواسطة:</strong> {{ $invoice->user->name ?? 'غير محدد' }}<br>
                            <strong>تاريخ الإنشاء:</strong> {{ $invoice->created_at->format('Y-m-d H:i') }}
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        @if($invoice->updated_at != $invoice->created_at)
                        <small class="text-muted">
                            <strong>آخر تعديل:</strong> {{ $invoice->updated_at->format('Y-m-d H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                    <div>
                        @if($invoice->status !== 'paid')
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>
                                تعديل الفاتورة
                            </a>
                        @endif
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            طباعة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- إضافة دفعة -->
        @if($invoice->remaining_amount > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-money-bill me-2"></i>
                    إضافة دفعة
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.payment', $invoice) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">طريقة الدفع</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="cash">نقدي</option>
                            <option value="bank">بنكي</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">مبلغ الدفعة</label>
                        <div class="input-group">
                            <input type="number" name="payment_amount" id="payment_amount" 
                                   class="form-control @error('payment_amount') is-invalid @enderror" 
                                   min="0.01" max="{{ $invoice->remaining_amount }}" step="0.01" 
                                   placeholder="{{ $invoice->remaining_amount }}">
                            <span class="input-group-text">ش.ج</span>
                        </div>
                        <small class="text-muted">الحد الأقصى: {{ number_format($invoice->remaining_amount, 2) }} ش.ج</small>
                        @error('payment_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-plus me-2"></i>
                        إضافة الدفعة
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- إجراءات سريعة -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($invoice->remaining_amount > 0)
                        <button type="button" class="btn btn-success" 
                                onclick="document.getElementById('payment_amount').value = '{{ $invoice->remaining_amount }}'; document.getElementById('payment_amount').focus();">
                            <i class="fas fa-money-check me-2"></i>
                            دفع كامل ({{ number_format($invoice->remaining_amount, 2) }} ش.ج)
                        </button>
                    @endif
                    
                    <a href="{{ route('subscribers.show', $invoice->subscriber) }}" class="btn btn-outline-info">
                        <i class="fas fa-user me-2"></i>
                        عرض ملف المشترك
                    </a>
                    
                    <a href="{{ route('invoices.create') }}?subscriber_id={{ $invoice->subscriber_id }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>
                        فاتورة جديدة لنفس المشترك
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .card-footer, .col-md-4, .no-print, .navbar, .sidebar { display: none !important; }
    .col-md-8 { width: 100% !important; max-width: 100% !important; }
    .card { border: none !important; box-shadow: none !important; }
    .print-only { display: block !important; }
    body { background: #fff !important; }
}
</style>
@endpush
@endsection