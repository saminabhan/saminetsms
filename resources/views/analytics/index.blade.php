@extends('layouts.app')

@section('title', 'لوحة التحليلات والإحصائيات')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>لوحة التحليلات والإحصائيات</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="year" class="form-select form-select-sm">
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">تحديث</button>
            </form>
        </div>
    </div>

    <!-- الملخص المالي -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">الملخص المالي ({{ $start->format('Y-m-d') }} - {{ $end->format('Y-m-d') }})</h5>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي الفواتير</div>
                <div class="h5 text-primary">{{ number_format($financialSummary['total_revenue'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">المدفوعات المستلمة</div>
                <div class="h5 text-success">{{ number_format($financialSummary['total_payments'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي المصروفات</div>
                <div class="h5 text-danger">{{ number_format($financialSummary['total_expenses'] + $financialSummary['total_withdrawals'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">صافي الربح</div>
                <div class="h5 {{ $financialSummary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($financialSummary['net_profit'], 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">رصيد نقدي</div>
                <div class="h5 text-info">{{ number_format($financialSummary['current_cash'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">رصيد بنكي</div>
                <div class="h5 text-info">{{ number_format($financialSummary['current_bank'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- نمو الإيرادات -->
    @if($revenueGrowth['growth_percentage'] !== null)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>نمو الإيرادات مقارنة بالفترة السابقة</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">الفترة الحالية</small>
                        <div class="h6">{{ number_format($revenueGrowth['current_revenue'], 2) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="h4 {{ $revenueGrowth['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $revenueGrowth['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($revenueGrowth['growth_percentage'], 1) }}%
                        </div>
                        <small class="text-muted">معدل النمو</small>
                    </div>
                    <div>
                        <small class="text-muted">الفترة السابقة</small>
                        <div class="h6">{{ number_format($revenueGrowth['previous_revenue'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>تحليل طرق الدفع</h6>
                @foreach($paymentMethodsAnalysis as $method)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $method['method'] }}</span>
                    <div>
                        <span class="badge bg-primary">{{ $method['count'] }} دفعة</span>
                        <span class="fw-bold">{{ number_format($method['total'], 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- تحليل المصروفات -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <h6>المصروفات التشغيلية</h6>
                <div class="mb-2 fw-bold text-danger">{{ number_format($expensesAnalysis['total_operational'], 2) }}</div>
                @foreach($expensesAnalysis['operational'] as $expense)
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $expense['category'] }}</span>
                    <span>{{ number_format($expense['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>المصروفات الرأسمالية</h6>
                <div class="mb-2 fw-bold text-warning">{{ number_format($expensesAnalysis['total_capital'], 2) }}</div>
                @foreach($expensesAnalysis['capital'] as $expense)
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $expense['category'] }}</span>
                    <span>{{ number_format($expense['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>سحوبات الشركاء</h6>
                <div class="mb-2 fw-bold text-info">{{ number_format($expensesAnalysis['total_partners'], 2) }}</div>
                @foreach($expensesAnalysis['partners'] as $withdrawal)
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $withdrawal['category'] }}</span>
                    <span>{{ number_format($withdrawal['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- تحليل الحملات والخدمات -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>تحليل الحملات حسب فئة الخدمة</h6>
                <canvas id="campaignChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>أكثر الخدمات طلبًا</h6>
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($popularServices as $service)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $service['service'] ? $service['service']->name_ar : 'غير محدد' }}</div>
                            <small class="text-muted">{{ $service['invoice_count'] }} فاتورة - {{ $service['total_quantity'] }} وحدة</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">{{ number_format($service['total_revenue'], 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- تحليل الخصومات -->
    @if($discountAnalysis['discount_count'] > 0)
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="small text-muted">إجمالي الخصومات</div>
                <div class="h5 text-warning">{{ number_format($discountAnalysis['total_discounts'], 2) }}</div>
                <small>{{ $discountAnalysis['discount_count'] }} فاتورة بخصم</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="small text-muted">متوسط الخصم</div>
                <div class="h5">{{ number_format($discountAnalysis['average_discount'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>أكبر الخصومات</h6>
                <div style="max-height: 200px; overflow-y: auto;">
                    @foreach($discountAnalysis['biggest_discounts']->take(5) as $invoice)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $invoice->subscriber ? $invoice->subscriber->name : 'غير محدد' }}</span>
                        <span class="text-warning">{{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- حالة المخزون -->
    @if(count($inventoryStatus) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>حالة مخزون البطاقات لدى الموزعين</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>الموزع</th>
                                <th>إجمالي البطاقات</th>
                                <th>البطاقات المتاحة</th>
                                <th>البطاقات المباعة</th>
                                <th>قيمة المتاح</th>
                                <th>معدل البيع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryStatus as $inventory)
                            <tr>
                                <td>{{ $inventory['distributor'] ? $inventory['distributor']->name : 'غير محدد' }}</td>
                                <td>{{ number_format($inventory['total_cards']) }}</td>
                                <td>
                                    <span class="badge {{ $inventory['available_cards'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ number_format($inventory['available_cards']) }}
                                    </span>
                                </td>
                                <td>{{ number_format($inventory['sold_cards']) }}</td>
                                <td>{{ number_format($inventory['available_value'], 2) }}</td>
                                <td>
                                    @php
                                        $saleRate = $inventory['total_cards'] > 0 
                                            ? ($inventory['sold_cards'] / $inventory['total_cards']) * 100 
                                            : 0;
                                    @endphp
                                    <span class="badge {{ $saleRate > 70 ? 'bg-success' : ($saleRate > 30 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ number_format($saleRate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- الإيراد الشهري -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>الإيراد الشهري للعام {{ $year }}</h6>
                <canvas id="monthlyRevenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- تحليل الحملات الفردية -->
    @if(count($campaignAnalysis) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>تفصيل الإيرادات حسب فئات الخدمات</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>فئة الخدمة</th>
                                <th>إجمالي الإيرادات</th>
                                <th>عدد الفواتير</th>
                                <th>متوسط قيمة الفاتورة</th>
                                <th>نسبة من الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalCampaignRevenue = $campaignAnalysis->sum('revenue');
                            @endphp
                            @foreach($campaignAnalysis as $campaign)
                            <tr>
                                <td>{{ $campaign['category'] }}</td>
                                <td class="fw-bold">{{ number_format($campaign['revenue'], 2) }}</td>
                                <td>{{ number_format($campaign['invoice_count']) }}</td>
                                <td>
                                    {{ $campaign['invoice_count'] > 0 ? number_format($campaign['revenue'] / $campaign['invoice_count'], 2) : '0.00' }}
                                </td>
                                <td>
                                    @php
                                        $percentage = $totalCampaignRevenue > 0 ? ($campaign['revenue'] / $totalCampaignRevenue) * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%" 
                                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ number_format($percentage, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم بياني للإيرادات الشهرية
    const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    const monthlyData = @json(array_column($monthlyRevenue, 'revenue'));
    const monthlyInvoices = @json(array_column($monthlyRevenue, 'invoice_count'));
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
            datasets: [{
                label: 'الإيرادات',
                data: monthlyData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'عدد الفواتير',
                data: monthlyInvoices,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'الإيرادات الشهرية وعدد الفواتير'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // رسم بياني دائري للحملات
    @if(count($campaignAnalysis) > 0)
    const campaignCtx = document.getElementById('campaignChart').getContext('2d');
    const campaignLabels = @json($campaignAnalysis->pluck('category')->toArray());
    const campaignData = @json($campaignAnalysis->pluck('revenue')->toArray());
    
    new Chart(campaignCtx, {
        type: 'doughnut',
        data: {
            labels: campaignLabels,
            datasets: [{
                data: campaignData,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'توزيع الإيرادات حسب فئات الخدمات'
                }
            }
        }
    });
    @endif
});
</script>
@endsection