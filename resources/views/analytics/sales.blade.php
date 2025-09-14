@extends('layouts.app')

@section('title', 'تقرير المبيعات والعملاء')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>تقرير المبيعات والعملاء</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" class="form-control form-control-sm">
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">تحديث</button>
            </form>
            <a href="{{ route('analytics.export-sales') }}" class="btn btn-success btn-sm">تصدير التقرير</a>
        </div>
    </div>

    <!-- إحصائيات المبيعات -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">إحصائيات المبيعات</h5>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 border-primary">
                <div class="small text-muted">إجمالي عدد الفواتير</div>
                <div class="h4 text-primary">{{ number_format($salesStats['total_invoices_count']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 border-success">
                <div class="small text-muted">فواتير المشتركين</div>
                <div class="h4 text-success">{{ number_format($salesStats['subscriber_invoices']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 border-info">
                <div class="small text-muted">فواتير الموزعين</div>
                <div class="h4 text-info">{{ number_format($salesStats['distributor_invoices']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 border-warning">
                <div class="small text-muted">متوسط قيمة الفاتورة</div>
                <div class="h4 text-warning">{{ number_format($salesStats['avg_invoice_value'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- معدل التحصيل -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>معدل التحصيل</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">إجمالي الفواتير</div>
                            <div class="h6 text-primary">{{ number_format($collectionRate['total_invoiced'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2">
                            <div class="small text-muted">إجمالي المحصل</div>
                            <div class="h6 text-success">{{ number_format($collectionRate['total_collected'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>معدل التحصيل:</span>
                    <span class="fw-bold {{ $collectionRate['collection_rate'] >= 80 ? 'text-success' : ($collectionRate['collection_rate'] >= 60 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($collectionRate['collection_rate'], 1) }}%
                    </span>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar {{ $collectionRate['collection_rate'] >= 80 ? 'bg-success' : ($collectionRate['collection_rate'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                         style="width: {{ $collectionRate['collection_rate'] }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <span>المبلغ المعلق:</span>
                    <span class="fw-bold text-danger">{{ number_format($collectionRate['outstanding'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>توزيع المبيعات حسب نوع العميل</h6>
                <canvas id="salesDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- أفضل العملاء -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>أفضل العملاء (أعلى قيمة مدفوعات)</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم العميل</th>
                                <th>نوع العميل</th>
                                <th>إجمالي المدفوع</th>
                                <th>عدد المدفوعات</th>
                                <th>متوسط الدفعة</th>
                                <th>آخر دفعة</th>
                                <th>التقييم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $index => $customer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $customer['subscriber'] ? $customer['subscriber']->name : 'غير محدد' }}</strong>
                                    @if($customer['subscriber'])
                                        <br><small class="text-muted">{{ $customer['subscriber']->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">مشترك</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($customer['total_paid'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $customer['payment_count'] }}</span>
                                </td>
                                <td>
                                    {{ number_format($customer['total_paid'] / max(1, $customer['payment_count']), 2) }}
                                </td>
                                <td>
                                    @php
                                        $lastPayment = \App\Models\Payment::join('invoices', 'invoices.id', '=', 'payments.invoice_id')
                                                      ->where('invoices.subscriber_id', $customer['subscriber']->id ?? 0)
                                                      ->latest('payments.paid_at')->first();
                                    @endphp
                                    @if($lastPayment)
                                        {{ $lastPayment->paid_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">لا يوجد</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $avgPayment = $customer['total_paid'] / max(1, $customer['payment_count']);
                                        $rating = 0;
                                        if ($customer['total_paid'] > 5000) $rating += 3;
                                        elseif ($customer['total_paid'] > 2000) $rating += 2;
                                        elseif ($customer['total_paid'] > 500) $rating += 1;
                                        
                                        if ($customer['payment_count'] > 10) $rating += 2;
                                        elseif ($customer['payment_count'] > 5) $rating += 1;
                                        
                                        if ($avgPayment > 500) $rating += 1;
                                    @endphp
                                    @if($rating >= 5)
                                        <span class="badge bg-success">VIP</span>
                                    @elseif($rating >= 3)
                                        <span class="badge bg-warning">ذهبي</span>
                                    @elseif($rating >= 1)
                                        <span class="badge bg-info">فضي</span>
                                    @else
                                        <span class="badge bg-secondary">عادي</span>
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

    <!-- تحليل الخدمات الأكثر طلباً -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>الخدمات الأكثر طلباً في الفترة</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الخدمة</th>
                                <th>الفئة</th>
                                <th>عدد الفواتير</th>
                                <th>إجمالي الكمية</th>
                                <th>إجمالي الإيرادات</th>
                                <th>متوسط سعر البيع</th>
                                <th>الشعبية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceAnalysis as $index => $service)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $service['service'] ? $service['service']->name_ar ?? $service['service']->name : 'غير محدد' }}</strong>
                                    @if($service['service'])
                                        <br><small class="text-muted">رقم: {{ $service['service']->id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($service['service'] && $service['service']->category)
                                        <span class="badge bg-secondary">{{ $service['service']->category->name_ar ?? $service['service']->category->name }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $service['invoice_count'] }}</span>
                                </td>
                                <td>{{ number_format($service['total_quantity']) }}</td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($service['total_revenue'], 2) }}</span>
                                </td>
                                <td>
                                    {{ $service['total_quantity'] > 0 ? number_format($service['total_revenue'] / $service['total_quantity'], 2) : '0.00' }}
                                </td>
                                <td>
                                    @php
                                        $popularityScore = 0;
                                        if ($service['invoice_count'] > 50) $popularityScore = 5;
                                        elseif ($service['invoice_count'] > 30) $popularityScore = 4;
                                        elseif ($service['invoice_count'] > 20) $popularityScore = 3;
                                        elseif ($service['invoice_count'] > 10) $popularityScore = 2;
                                        else $popularityScore = 1;
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $popularityScore ? 'text-warning' : 'text-muted' }}">★</span>
                                        @endfor
                                        <small class="ms-2">({{ $popularityScore }}/5)</small>
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

    <!-- رسوم بيانية -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <h6>أداء أفضل 10 خدمات</h6>
                <canvas id="topServicesChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>إحصائيات سريعة</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>أفضل عميل:</span>
                        <div class="text-end">
                            @if(count($topCustomers) > 0)
                                <strong>{{ $topCustomers[0]['subscriber'] ? $topCustomers[0]['subscriber']->name : 'غير محدد' }}</strong>
                                <br><small class="text-success">{{ number_format($topCustomers[0]['total_paid'], 2) }}</small>
                            @else
                                <span class="text-muted">لا يوجد</span>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>أفضل خدمة:</span>
                        <div class="text-end">
                            @if(count($serviceAnalysis) > 0)
                                <strong>{{ $serviceAnalysis[0]['service'] ? $serviceAnalysis[0]['service']->name_ar ?? $serviceAnalysis[0]['service']->name : 'غير محدد' }}</strong>
                                <br><small class="text-success">{{ number_format($serviceAnalysis[0]['total_revenue'], 2) }}</small>
                            @else
                                <span class="text-muted">لا يوجد</span>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>نسبة فواتير المشتركين:</span>
                        <span class="fw-bold text-info">
                            {{ $salesStats['total_invoices_count'] > 0 ? number_format(($salesStats['subscriber_invoices'] / $salesStats['total_invoices_count']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>نسبة فواتير الموزعين:</span>
                        <span class="fw-bold text-warning">
                            {{ $salesStats['total_invoices_count'] > 0 ? number_format(($salesStats['distributor_invoices'] / $salesStats['total_invoices_count']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تحليل شهري للمبيعات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>اتجاه المبيعات الشهري</h6>
                <canvas id="monthlySalesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- العملاء الجدد vs القدامى -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h6>العملاء الجدد هذا الشهر</h6>
                @php
                    $newCustomers = \App\Models\Subscriber::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
                    $newCustomersRevenue = \App\Models\Payment::join('invoices', 'invoices.id', '=', 'payments.invoice_id')
                                          ->join('subscribers', 'subscribers.id', '=', 'invoices.subscriber_id')
                                          ->whereBetween('subscribers.created_at', [now()->startOfMonth(), now()])
                                          ->sum('payments.amount');
                @endphp
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success">{{ number_format($newCustomers) }}</div>
                        <small class="text-muted">عميل جديد</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info">{{ number_format($newCustomersRevenue, 2) }}</div>
                        <small class="text-muted">إيراد العملاء الجدد</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>متوسط قيمة العميل</h6>
                @php
                    $totalCustomers = \App\Models\Subscriber::count();
                    $totalRevenue = $collectionRate['total_collected'];
                    $avgCustomerValue = $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;
                    $avgPaymentPerCustomer = $totalCustomers > 0 ? $salesStats['total_invoices_count'] / $totalCustomers : 0;
                @endphp
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary">{{ number_format($avgCustomerValue, 2) }}</div>
                        <small class="text-muted">متوسط الإيراد لكل عميل</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning">{{ number_format($avgPaymentPerCustomer, 1) }}</div>
                        <small class="text-muted">متوسط الفواتير لكل عميل</small>
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
    // رسم بياني لتوزيع المبيعات حسب نوع العميل
    const distributionCtx = document.getElementById('salesDistributionChart').getContext('2d');
    const subscriberInvoices = {{ $salesStats['subscriber_invoices'] }};
    const distributorInvoices = {{ $salesStats['distributor_invoices'] }};
    
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['المشتركين', 'الموزعين'],
            datasets: [{
                data: [subscriberInvoices, distributorInvoices],
                backgroundColor: ['#28a745', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // رسم بياني لأفضل الخدمات
    const servicesCtx = document.getElementById('topServicesChart').getContext('2d');
    const serviceLabels = @json($serviceAnalysis->take(10)->map(function($item) { 
        return $item['service'] ? ($item['service']->name_ar ?? $item['service']->name) : 'غير محدد';
    }));
    const serviceRevenue = @json($serviceAnalysis->take(10)->pluck('total_revenue'));
    const serviceCount = @json($serviceAnalysis->take(10)->pluck('invoice_count'));
    
    new Chart(servicesCtx, {
        type: 'bar',
        data: {
            labels: serviceLabels,
            datasets: [{
                label: 'الإيرادات',
                data: serviceRevenue,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'عدد الفواتير',
                data: serviceCount,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left'
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // رسم بياني للمبيعات الشهرية
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    
    // جلب بيانات المبيعات الشهرية عبر API
    fetch('/analytics/api/monthly-revenue/{{ now()->year }}')
    .then(response => response.json())
    .then(data => {
        new Chart(monthlySalesCtx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'الإيرادات الشهرية',
                    data: data.revenue,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
});
</script>
@endsection