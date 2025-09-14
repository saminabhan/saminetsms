@extends('layouts.app')

@section('title', 'تقرير الخدمات والحملات')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>تقرير الخدمات والحملات</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date_from" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm">
                <input type="date" name="date_to" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">تحديث</button>
            </form>
        </div>
    </div>

    <!-- الإحصائيات العامة للخدمات -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي الخدمات</div>
                <div class="h4 text-primary">{{ number_format($servicesStats['total_services'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <div class="small text-muted">الخدمات النشطة</div>
                <div class="h4 text-success">{{ number_format($servicesStats['active_services'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي الإيرادات</div>
                <div class="h4 text-info">{{ number_format($servicesStats['total_service_revenue'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <div class="small text-muted">متوسط سعر الخدمة</div>
                <div class="h4 text-warning">{{ number_format($servicesStats['avg_service_price'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- أداء أفضل 10 خدمات -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <h6>أداء أفضل 10 خدمات</h6>
                <canvas id="servicesPerformanceChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>توزيع الإيرادات حسب فئات الخدمات</h6>
                <canvas id="servicesCategoriesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- تحليل فئات الخدمات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>تحليل فئات الخدمات</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>فئة الخدمة</th>
                                <th>إجمالي الإيرادات</th>
                                <th>إجمالي الفواتير</th>
                                <th>الخدمات النشطة</th>
                                <th>متوسط الإيراد لكل فاتورة</th>
                                <th>الحصة من الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalCategoriesRevenue = $categoriesAnalysis->sum('total_revenue') ?? 0;
                            @endphp
                            @foreach($categoriesAnalysis as $category)
                            <tr>
                                <td>
                                    <strong>{{ $category->name_ar ?? $category->name ?? 'غير محدد' }}</strong>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($category->total_revenue ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ number_format($category->total_invoices ?? 0) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $category->active_services ?? 0 }}</span>
                                </td>
                                <td>
                                    {{ ($category->total_invoices ?? 0) > 0 ? number_format(($category->total_revenue ?? 0) / $category->total_invoices, 2) : '0.00' }}
                                </td>
                                <td>
                                    @php
                                        $percentage = $totalCategoriesRevenue > 0 ? (($category->total_revenue ?? 0) / $totalCategoriesRevenue) * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
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

    <!-- الخدمات الأكثر ربحية -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>الخدمات الأكثر ربحية</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الخدمة</th>
                                <th>صافي الإيرادات</th>
                                <th>إجمالي الإيرادات</th>
                                <th>إجمالي الخصومات</th>
                                <th>عدد الفواتير</th>
                                <th>متوسط القيمة</th>
                                <th>هامش الربح</th>
                                <th>التقييم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mostProfitableServices as $index => $service)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($service['service'])
                                        <strong>{{ $service['service']->name_ar ?? $service['service']->name }}</strong>
                                        <br><small class="text-muted">رقم: {{ $service['service']->id }}</small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($service['net_revenue'] ?? 0, 2) }}</span>
                                </td>
                                <td>{{ number_format($service['gross_revenue'] ?? 0, 2) }}</td>
                                <td>
                                    <span class="text-warning">{{ number_format($service['total_discounts'] ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $service['invoice_count'] ?? 0 }}</span>
                                </td>
                                <td>{{ number_format($service['avg_value'] ?? 0, 2) }}</td>
                                <td>
                                    @php
                                        $profit = $service['profit_margin'] ?? 0;
                                        $badgeClass = $profit > 90 ? 'bg-success' : ($profit > 80 ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ number_format($profit, 1) }}%</span>
                                </td>
                                <td>
                                    @php
                                        $rating = 0;
                                        $net = $service['net_revenue'] ?? 0;
                                        $margin = $service['profit_margin'] ?? 0;
                                        $count = $service['invoice_count'] ?? 0;

                                        if ($net > 10000) $rating += 3;
                                        elseif ($net > 5000) $rating += 2;
                                        elseif ($net > 1000) $rating += 1;

                                        if ($margin > 90) $rating += 2;
                                        elseif ($margin > 80) $rating += 1;

                                        if ($count > 50) $rating += 1;
                                    @endphp
                                    @if($rating >= 5)
                                        <span class="badge bg-success">ممتاز</span>
                                    @elseif($rating >= 3)
                                        <span class="badge bg-warning">جيد</span>
                                    @else
                                        <span class="badge bg-info">متوسط</span>
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

    <!-- أداء جميع الخدمات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>أداء جميع الخدمات في الفترة المحددة</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>الخدمة</th>
                                <th>الفئة</th>
                                <th>السعر الأساسي</th>
                                <th>إجمالي الإيرادات</th>
                                <th>عدد الفواتير</th>
                                <th>إجمالي الكمية</th>
                                <th>متوسط الفاتورة</th>
                                <th>الحالة</th>
                                <th>الاتجاه</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicesPerformance as $performance)
                            <tr>
                                <td>
                                    @if(isset($performance['service']) && $performance['service'])
                                        <strong>{{ $performance['service']->name_ar ?? $performance['service']->name }}</strong>
                                        <br><small class="text-muted">ID: {{ $performance['service']->id }}</small>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($performance['service']->category) && $performance['service']->category)
                                        <span class="badge bg-secondary">{{ $performance['service']->category->name_ar ?? $performance['service']->category->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">غير محدد</span>
                                    @endif
                                </td>
                                <td>{{ $performance['service']->price ?? '0.00' }}</td>
                                <td>{{ number_format($performance['total_revenue'] ?? 0, 2) }}</td>
                                <td>{{ $performance['invoice_count'] ?? 0 }}</td>
                                <td>{{ number_format($performance['total_quantity'] ?? 0) }}</td>
                                <td>{{ number_format($performance['avg_invoice_value'] ?? 0, 2) }}</td>
                                <td>
                                    @php
                                        $revenue = $performance['total_revenue'] ?? 0;
                                        $invoiceCount = $performance['invoice_count'] ?? 0;
                                    @endphp
                                    @if($revenue > 0)
                                        @if($invoiceCount > 20)
                                            <span class="badge bg-success">نشط</span>
                                        @elseif($invoiceCount > 5)
                                            <span class="badge bg-warning">متوسط</span>
                                        @else
                                            <span class="badge bg-info">بطيء</span>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $trend = $servicesTrends->firstWhere('service.id', $performance['service']->id ?? 0);
                                    @endphp
                                    @if($trend && isset($trend['growth_rate']))
                                        @if($trend['growth_rate'] > 20)
                                            <span class="text-success">📈 +{{ number_format($trend['growth_rate'], 1) }}%</span>
                                        @elseif($trend['growth_rate'] > 0)
                                            <span class="text-warning">📊 +{{ number_format($trend['growth_rate'], 1) }}%</span>
                                        @elseif($trend['growth_rate'] > -20)
                                            <span class="text-warning">📉 {{ number_format($trend['growth_rate'], 1) }}%</span>
                                        @else
                                            <span class="text-danger">📉 {{ number_format($trend['growth_rate'], 1) }}%</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
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

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم بياني لأداء الخدمات
    const servicesCtx = document.getElementById('servicesPerformanceChart').getContext('2d');
    
    fetch('{{ route("analytics.servicesPerformanceApi") }}?start={{ $start->format("Y-m-d") }}&end={{ $end->format("Y-m-d") }}')
    .then(response => response.json())
    .then(data => {
        new Chart(servicesCtx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'الإيرادات',
                    data: data.revenue,
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'عدد الفواتير',
                    data: data.count,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'أداء أفضل 10 خدمات' }
                },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'الإيرادات' } },
                    y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'عدد الفواتير' }, grid: { drawOnChartArea: false } }
                }
            }
        });
    });

    // رسم بياني دائري لفئات الخدمات
    const categoriesCtx = document.getElementById('servicesCategoriesChart').getContext('2d');
    const categoriesLabels = @json($categoriesAnalysis->map(fn($c) => $c->name_ar ?? $c->name ?? 'غير محدد'));
    const categoriesData = @json($categoriesAnalysis->pluck('total_revenue'));

    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: categoriesLabels,
            datasets: [{ data: categoriesData, backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#FF6384','#C9CBCF','#4BC0C0','#36A2EB'] }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' }, title: { display: true, text: 'توزيع الإيرادات حسب الفئات' } }
        }
    });
});
</script>
@endsection
