@extends('layouts.app')

@section('title', 'تقرير الموزعين والمخزون')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>تقرير الموزعين والمخزون</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date_from" value="{{ $start->format('Y-m-d') }}" class="form-control form-control-sm">
                <input type="date" name="date_to" value="{{ $end->format('Y-m-d') }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">تحديث</button>
            </form>
            <a href="{{ route('analytics.export-distributors') }}?date_from={{ $start->format('Y-m-d') }}&date_to={{ $end->format('Y-m-d') }}" 
               class="btn btn-success btn-sm">تصدير التقرير</a>
        </div>
    </div>

    <!-- الإحصائيات العامة -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي الموزعين</div>
                <div class="h4 text-primary">{{ number_format($distributorsStats['total_distributors']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">الموزعين النشطين</div>
                <div class="h4 text-success">{{ number_format($distributorsStats['active_distributors']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">إجمالي البطاقات الموزعة</div>
                <div class="h4 text-info">{{ number_format($distributorsStats['total_cards_distributed']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">البطاقات المباعة</div>
                <div class="h4 text-warning">{{ number_format($distributorsStats['total_cards_sold']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">قيمة المخزون المتاح</div>
                <div class="h4 text-danger">{{ number_format($distributorsStats['total_inventory_value'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center p-3">
                <div class="small text-muted">معدل البيع العام</div>
                @php
                    $overallSaleRate = $distributorsStats['total_cards_distributed'] > 0 
                        ? ($distributorsStats['total_cards_sold'] / $distributorsStats['total_cards_distributed']) * 100 
                        : 0;
                @endphp
                <div class="h4 {{ $overallSaleRate > 70 ? 'text-success' : ($overallSaleRate > 30 ? 'text-warning' : 'text-danger') }}">
                    {{ number_format($overallSaleRate, 1) }}%
                </div>
            </div>
        </div>
    </div>

    <!-- رسم بياني لأداء الموزعين -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <h6>أداء أفضل 10 موزعين</h6>
                <canvas id="distributorsPerformanceChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>توزيع معدلات البيع</h6>
                <canvas id="saleRatesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- أفضل الموزعين -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>أفضل الموزعين في الفترة ({{ $start->format('Y-m-d') }} - {{ $end->format('Y-m-d') }})</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الموزع</th>
                                <th>إجمالي المبيعات</th>
                                <th>عدد البطاقات</th>
                                <th>عدد المدفوعات</th>
                                <th>متوسط قيمة الدفعة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topDistributors as $index => $distributor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $distributor['distributor'] ? $distributor['distributor']->name : 'غير محدد' }}</strong>
                                    @if($distributor['distributor'])
                                        <br><small class="text-muted">{{ $distributor['distributor']->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($distributor['total_sales'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ number_format($distributor['total_cards']) }}</span>
                                </td>
                                <td>{{ $distributor['payment_count'] }}</td>
                                <td>
                                    {{ $distributor['payment_count'] > 0 ? number_format($distributor['total_sales'] / $distributor['payment_count'], 2) : '0.00' }}
                                </td>
                                <td>
                                    @php
                                        $avgSale = $distributor['total_sales'] / max(1, $distributor['payment_count']);
                                    @endphp
                                    <span class="badge {{ $avgSale > 1000 ? 'bg-success' : ($avgSale > 500 ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ $avgSale > 1000 ? 'ممتاز' : ($avgSale > 500 ? 'جيد' : 'ضعيف') }}
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

    <!-- حالة المخزون التفصيلية -->
  <!-- حالة المخزون التفصيلية -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card p-3">
            <h6>حالة المخزون التفصيلية</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>الموزع</th>
                            <th>الخدمة</th>
                            <th>الكمية المستلمة</th>
                            <th>الكمية المتاحة</th>
                            <th>الكمية المباعة</th>
                            <th>قيمة الاستثمار</th>
                            <th>قيمة المتاح</th>
                            <th>معدل البيع</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventoryDetails as $inventory)
                        <tr>
                            <td>{{ $inventory['distributor'] ? $inventory['distributor']->name : 'غير محدد' }}</td>
                            <td>{{ $inventory['service_id'] }}</td>
                            <td>{{ number_format($inventory['total_received']) }}</td>
                            <td>
                                <span class="badge {{ $inventory['total_available'] > 5 ? 'bg-success' : ($inventory['total_available'] > 0 ? 'bg-warning' : 'bg-danger') }}">
                                    {{ number_format($inventory['total_available']) }}
                                </span>
                            </td>
                            <td>{{ number_format($inventory['total_sold']) }}</td>
                            <td>{{ number_format($inventory['total_investment'], 2) }}</td>
                            <td>{{ number_format($inventory['available_value'], 2) }}</td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 80px;">
                                    <div class="progress-bar {{ $inventory['sale_rate'] > 70 ? 'bg-success' : ($inventory['sale_rate'] > 30 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ $inventory['sale_rate'] }}%">
                                        {{ number_format($inventory['sale_rate'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($inventory['total_available'] == 0)
                                    <span class="badge bg-danger">نفدت الكمية</span>
                                @elseif($inventory['total_available'] < 5)
                                    <span class="badge bg-warning">مخزون منخفض</span>
                                @elseif($inventory['sale_rate'] < 30)
                                    <span class="badge bg-info">بيع بطيء</span>
                                @else
                                    <span class="badge bg-success">طبيعي</span>
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


  <!-- الموزعين الذين يحتاجون متابعة -->
@if(count($distributorsNeedingAttention['inactive'] ?? []) > 0 || count($distributorsNeedingAttention['low_stock'] ?? []) > 0)
<div class="row mb-4">
    @if(count($distributorsNeedingAttention['inactive'] ?? []) > 0)
    <div class="col-md-6">
        <div class="card p-3 border-warning">
            <h6 class="text-warning">⚠️ موزعين بدون نشاط في الفترة المحددة</h6>
            <div style="max-height: 300px; overflow-y: auto;">
                @foreach($distributorsNeedingAttention['inactive'] ?? [] as $distributor)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <strong>{{ $distributor->name }}</strong>
                        <br><small class="text-muted">{{ $distributor->phone }}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-warning">لا يوجد مبيعات</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(count($distributorsNeedingAttention['low_stock'] ?? []) > 0)
    <div class="col-md-6">
        <div class="card p-3 border-danger">
            <h6 class="text-danger">🚨 موزعين بمخزون منخفض</h6>
            <div style="max-height: 300px; overflow-y: auto;">
                @foreach($distributorsNeedingAttention['low_stock'] ?? [] as $lowStock)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <strong>{{ $lowStock->distributor ? $lowStock->distributor->name : 'غير محدد' }}</strong>
                        <br><small class="text-muted">متبقي: {{ $lowStock->total_available }} بطاقة</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger">مخزون منخفض</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif


    <!-- تحليل أداء الموزعين -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <h6>تحليل شامل لأداء الموزعين</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>الموزع</th>
                                <th>إجمالي المبيعات</th>
                                <th>عدد الفواتير</th>
                                <th>متوسط قيمة الفاتورة</th>
                                <th>تقييم الأداء</th>
                                <th>آخر نشاط</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distributorPerformance as $performance)
                            <tr>
                                <td>
                                    <strong>{{ $inventory['distributor']->name ?? 'غير محدد' }}</strong>
                                    <br><small class="text-muted">{{ $inventory['distributor']->phone ?? 'غير محدد' }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($performance['total_sales'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $performance['invoice_count'] }}</span>
                                </td>
                                <td>{{ number_format($performance['avg_invoice_value'], 2) }}</td>
                                <td>
                                    @php
                                        $score = 0;
                                        if ($performance['total_sales'] > 5000) $score += 3;
                                        elseif ($performance['total_sales'] > 2000) $score += 2;
                                        elseif ($performance['total_sales'] > 500) $score += 1;
                                        
                                        if ($performance['invoice_count'] > 20) $score += 2;
                                        elseif ($performance['invoice_count'] > 10) $score += 1;
                                        
                                        if ($performance['avg_invoice_value'] > 200) $score += 1;
                                    @endphp
                                    @if($score >= 5)
                                        <span class="badge bg-success">ممتاز</span>
                                    @elseif($score >= 3)
                                        <span class="badge bg-warning">جيد</span>
                                    @elseif($score >= 1)
                                        <span class="badge bg-info">متوسط</span>
                                    @else
                                        <span class="badge bg-danger">ضعيف</span>
                                    @endif
                                </td>
                                <td>
                                   @php
    $lastInvoice = null;
    if ($performance['distributor']) {
        $lastInvoice = \App\Models\Invoice::where('distributor_id', $performance['distributor']->id)
                         ->latest('created_at')
                         ->first();
    }
@endphp

@if($lastInvoice)
    {{ $lastInvoice->created_at->diffForHumans() }}
@else
    <span class="text-muted">لا يوجد</span>
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
    // رسم بياني لأداء الموزعين
    const performanceCtx = document.getElementById('distributorsPerformanceChart').getContext('2d');
    
    fetch('{{ route("analytics.distributorsPerformanceApi") }}?start={{ $start->format("Y-m-d") }}&end={{ $end->format("Y-m-d") }}')
    .then(response => response.json())
    .then(data => {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'إجمالي المبيعات',
                    data: data.sales,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'عدد البطاقات',
                    data: data.cards,
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
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'أداء أفضل الموزعين'
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
    });

    // رسم بياني لتوزيع معدلات البيع
    const saleRatesCtx = document.getElementById('saleRatesChart').getContext('2d');
    
    const saleRatesData = @json($inventoryDetails->groupBy(function($item) {
        $rate = $item['sale_rate'];
        if ($rate >= 70) return 'ممتاز (70%+)';
        if ($rate >= 40) return 'جيد (40-69%)';
        if ($rate >= 20) return 'متوسط (20-39%)';
        return 'ضعيف (<20%)';
    })->map->count());
    
    new Chart(saleRatesCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(saleRatesData),
            datasets: [{
                data: Object.values(saleRatesData),
                backgroundColor: [
                    '#28a745',
                    '#ffc107', 
                    '#fd7e14',
                    '#dc3545'
                ]
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
});
</script>
@endsection