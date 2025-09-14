@extends('layouts.app')

@section('title', 'التقارير المالية التفصيلية')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>التقارير المالية التفصيلية</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" class="form-control form-control-sm">
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">تحديث</button>
            </form>
            <a href="{{ route('analytics.export-financial') }}" class="btn btn-success btn-sm">تصدير التقرير</a>
        </div>
    </div>

    <!-- الملخص المالي العام -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">الملخص المالي العام</h5>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 border-primary">
                <div class="small text-muted">إجمالي الفواتير</div>
                <div class="h4 text-primary">{{ number_format($summary['total_invoices'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 border-success">
                <div class="small text-muted">المدفوعات المستلمة</div>
                <div class="h4 text-success">{{ number_format($summary['total_payments'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 border-danger">
                <div class="small text-muted">إجمالي المصروفات</div>
                <div class="h4 text-danger">{{ number_format($summary['total_expenses'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 border-warning">
                <div class="small text-muted">إجمالي الخصومات</div>
                <div class="h4 text-warning">{{ number_format($summary['total_discounts'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 border-info">
                <div class="small text-muted">المبالغ المعلقة</div>
                <div class="h4 text-info">{{ number_format($summary['outstanding'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3 {{ $summary['net_profit'] >= 0 ? 'border-success' : 'border-danger' }}">
                <div class="small text-muted">صافي الربح</div>
                <div class="h4 {{ $summary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($summary['net_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- تفصيل المصروفات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>تفصيل المصروفات حسب النوع والفئة</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>نوع المصروف</th>
                                <th>الفئة</th>
                                <th>المبلغ</th>
                                <th>العدد</th>
                                <th>النسبة المئوية</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenseBreakdown as $expense)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $expense['type_color'] }}">{{ $expense['type_name'] }}</span>
                                </td>
                                <td>
                                    <strong>{{ $expense['category'] }}</strong>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($expense['amount'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $expense['count'] }}</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px; min-width: 100px;">
                                        <div class="progress-bar bg-{{ $expense['type_color'] }}" style="width: {{ $expense['percentage'] }}%">
                                            {{ number_format($expense['percentage'], 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($expense['percentage'] > 30)
                                        <span class="badge bg-danger">مرتفع</span>
                                    @elseif($expense['percentage'] > 15)
                                        <span class="badge bg-warning">متوسط</span>
                                    @else
                                        <span class="badge bg-success">منخفض</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- حركة الصناديق -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>حركة الصندوق النقدي</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">الرصيد الافتتاحي</div>
                            <div class="h6">{{ number_format($cashFlow['opening_balance'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">الرصيد الحالي</div>
                            <div class="h6 text-success">{{ number_format($cashFlow['current_cash'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-success">المقبوضات النقدية:</span>
                    <span class="fw-bold text-success">+{{ number_format($cashFlow['cash_in'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-danger">المدفوعات النقدية:</span>
                    <span class="fw-bold text-danger">-{{ number_format($cashFlow['cash_out'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <span class="fw-bold">صافي الحركة:</span>
                    <span class="fw-bold {{ ($cashFlow['cash_in'] - $cashFlow['cash_out']) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($cashFlow['cash_in'] - $cashFlow['cash_out'], 2) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>حركة الحساب البنكي</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">الرصيد الافتتاحي</div>
                            <div class="h6">{{ number_format($cashFlow['bank_opening_balance'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">الرصيد الحالي</div>
                            <div class="h6 text-info">{{ number_format($cashFlow['current_bank'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-success">المقبوضات البنكية:</span>
                    <span class="fw-bold text-success">+{{ number_format($cashFlow['bank_in'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-danger">المدفوعات البنكية:</span>
                    <span class="fw-bold text-danger">-{{ number_format($cashFlow['bank_out'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <span class="fw-bold">صافي الحركة:</span>
                    <span class="fw-bold {{ ($cashFlow['bank_in'] - $cashFlow['bank_out']) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($cashFlow['bank_in'] - $cashFlow['bank_out'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- أكبر المدفوعات -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>أكبر المدفوعات في الفترة</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>العميل</th>
                                <th>المبلغ</th>
                                <th>الطريقة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($biggestPayments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('Y-m-d') }}</td>
                                <td>
                                    @if($payment->invoice->subscriber)
                                        <strong>{{ $payment->invoice->subscriber->name }}</strong>
                                        <br><small class="text-muted">مشترك</small>
                                    @elseif($payment->invoice->distributor)
                                        <strong>{{ $payment->invoice->distributor->name }}</strong>
                                        <br><small class="text-muted">موزع</small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $payment->method == 'cash' ? 'bg-success' : 'bg-info' }}">
                                        {{ $payment->method == 'cash' ? 'نقدي' : 'بنكي' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>أكبر الخصومات الممنوحة</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>العميل</th>
                                <th>قيمة الخصم</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($biggestDiscounts as $invoice)
                            <tr>
                                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if($invoice->subscriber)
                                        <strong>{{ $invoice->subscriber->name }}</strong>
                                        <br><small class="text-muted">مشترك</small>
                                    @elseif($invoice->distributor)
                                        <strong>{{ $invoice->distributor->name }}</strong>
                                        <br><small class="text-muted">موزع</small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-warning">{{ number_format($invoice->discount_amount, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $totalBeforeDiscount = $invoice->final_amount + $invoice->discount_amount;
                                        $discountPercentage = $totalBeforeDiscount > 0 ? ($invoice->discount_amount / $totalBeforeDiscount) * 100 : 0;
                                    @endphp
                                    <span class="badge bg-warning">{{ number_format($discountPercentage, 1) }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- الفواتير المعلقة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>الفواتير المعلقة (غير مدفوعة بالكامل)</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>العميل</th>
                                <th>إجمالي الفاتورة</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>عمر الدين</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outstandingInvoices as $invoice)
                            <tr>
                                <td>
                                    <strong>#{{ $invoice->id }}</strong>
                                </td>
                                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if($invoice->subscriber)
                                        <strong>{{ $invoice->subscriber->name }}</strong>
                                        <br><small class="text-muted">{{ $invoice->subscriber->phone }}</small>
                                    @elseif($invoice->distributor)
                                        <strong>{{ $invoice->distributor->name }}</strong>
                                        <br><small class="text-muted">{{ $invoice->distributor->phone }}</small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>{{ number_format($invoice->final_amount, 2) }}</td>
                                <td class="text-success">{{ number_format($invoice->payments->sum('amount'), 2) }}</td>
                                <td class="fw-bold text-danger">
                                    {{ number_format($invoice->final_amount - $invoice->payments->sum('amount'), 2) }}
                                </td>
                                <td>
                                    @php
                                        $daysDiff = $invoice->created_at->diffInDays(now());
                                    @endphp
                                    <span class="badge {{ $daysDiff > 30 ? 'bg-danger' : ($daysDiff > 7 ? 'bg-warning' : 'bg-info') }}">
                                        {{ $daysDiff }} يوم
                                    </span>
                                </td>
                                <td>
                                    @if($invoice->payment_status == 'pending')
                                        <span class="badge bg-warning">معلقة</span>
                                    @elseif($invoice->payment_status == 'partial')
                                        <span class="badge bg-info">دفع جزئي</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $invoice->payment_status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- رسم بياني للمصروفات -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <h6>توزيع المصروفات</h6>
                <canvas id="expensesChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>مؤشرات مالية مهمة</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>معدل التحصيل:</span>
                        <span class="fw-bold text-success">
                            {{ $summary['total_invoices'] > 0 ? number_format(($summary['total_payments'] / $summary['total_invoices']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>نسبة الخصومات:</span>
                        <span class="fw-bold text-warning">
                            {{ $summary['total_invoices'] > 0 ? number_format(($summary['total_discounts'] / $summary['total_invoices']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>هامش الربح:</span>
                        <span class="fw-bold {{ $summary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['total_payments'] > 0 ? number_format(($summary['net_profit'] / $summary['total_payments']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>إجمالي السيولة:</span>
                        <span class="fw-bold text-info">
                            {{ number_format($cashFlow['current_cash'] + $cashFlow['current_bank'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم بياني للمصروفات
    const expensesCtx = document.getElementById('expensesChart').getContext('2d');
    
    const expensesLabels = @json($expenseBreakdown->pluck('category'));
    const expensesData = @json($expenseBreakdown->pluck('amount'));
@php
    $colorMap = [
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'success' => '#28a745'
    ];
    $expensesColors = $expenseBreakdown->pluck('type_color')->map(function($color) use ($colorMap) {
        return $colorMap[$color] ?? '#6c757d';
    })->toArray();
@endphp

const expensesColors = @json($expensesColors);
    new Chart(expensesCtx, {
        type: 'bar',
        data: {
            labels: expensesLabels,
            datasets: [{
                label: 'المبلغ',
                data: expensesData,
                backgroundColor: expensesColors,
                borderColor: expensesColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'توزيع المصروفات حسب الفئة'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'المبلغ'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'فئة المصروف'
                    }
                }
            }
        }
    });
});
</script>
@endsection