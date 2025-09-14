{{-- ملف الويدجتس للإحصائيات السريعة --}}

<div class="row g-3 mb-4">
    {{-- الإيرادات الإجمالية --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-25 text-success rounded-circle p-3">
                            <i class="fas fa-chart-line fa-xl"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title mb-1">إجمالي الإيرادات</h6>
                        <h4 class="text-success mb-0">{{ number_format($financialSummary['total_revenue'], 0) }}</h4>
                        <small class="text-muted">شيكل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- المدفوعات المستلمة --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-xl"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title mb-1">المدفوعات المستلمة</h6>
                        <h4 class="text-primary mb-0">{{ number_format($financialSummary['total_payments'], 0) }}</h4>
                        <small class="text-muted">شيكل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- إجمالي المصروفات --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-25 text-danger rounded-circle p-3">
                            <i class="fas fa-credit-card fa-xl"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title mb-1">إجمالي المصروفات</h6>
                        <h4 class="text-danger mb-0">{{ number_format($financialSummary['total_expenses'] + $financialSummary['total_withdrawals'], 0) }}</h4>
                        <small class="text-muted">شيكل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- صافي الربح --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-{{ $financialSummary['net_profit'] >= 0 ? 'success' : 'warning' }} bg-opacity-25 text-{{ $financialSummary['net_profit'] >= 0 ? 'success' : 'warning' }} rounded-circle p-3">
                            <i class="fas fa-{{ $financialSummary['net_profit'] >= 0 ? 'arrow-up' : 'arrow-down' }} fa-xl"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-title mb-1">صافي الربح</h6>
                        <h4 class="text-{{ $financialSummary['net_profit'] >= 0 ? 'success' : 'warning' }} mb-0">{{ number_format($financialSummary['net_profit'], 0) }}</h4>
                        <small class="text-muted">شيكل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- إحصائيات إضافية --}}
<div class="row g-3 mb-4">
    {{-- الأرصدة الحالية --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">الأرصدة الحالية</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h5 class="text-info mb-1">{{ number_format($financialSummary['current_cash'], 0) }}</h5>
                            <small class="text-muted">رصيد نقدي</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info mb-1">{{ number_format($financialSummary['current_bank'], 0) }}</h5>
                        <small class="text-muted">رصيد بنكي</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- المبالغ المعلقة --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">المبالغ المعلقة</h6>
                <div class="text-center">
                    <h5 class="text-warning mb-1">{{ number_format($financialSummary['outstanding_amount'], 0) }}</h5>
                    <small class="text-muted">مبالغ غير محصلة</small>
                    @if($financialSummary['total_revenue'] > 0)
                    <div class="mt-2">
                        <span class="badge bg-warning">{{ number_format(($financialSummary['outstanding_amount'] / $financialSummary['total_revenue']) * 100, 1) }}%</span>
                        <small class="text-muted d-block">من إجمالي الفواتير</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- الخصومات --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">إجمالي الخصومات</h6>
                <div class="text-center">
                    <h5 class="text-secondary mb-1">{{ number_format($financialSummary['total_discounts'], 0) }}</h5>
                    <small class="text-muted">شيكل خصم</small>
                    @if($financialSummary['total_revenue'] > 0)
                    <div class="mt-2">
                        <span class="badge bg-secondary">{{ number_format(($financialSummary['total_discounts'] / $financialSummary['total_revenue']) * 100, 1) }}%</span>
                        <small class="text-muted d-block">نسبة الخصم</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- مؤشرات الأداء السريعة --}}
@if(isset($revenueGrowth) && $revenueGrowth['growth_percentage'] !== null)
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">معدل النمو</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">الفترة السابقة</small>
                        <h6 class="mb-0">{{ number_format($revenueGrowth['previous_revenue'], 0) }}</h6>
                    </div>
                    <div class="text-center">
                        <div class="display-6 {{ $revenueGrowth['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $revenueGrowth['growth_percentage'] >= 0 ? '↗' : '↘' }}
                        </div>
                        <span class="badge bg-{{ $revenueGrowth['growth_percentage'] >= 0 ? 'success' : 'danger' }}">
                            {{ $revenueGrowth['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($revenueGrowth['growth_percentage'], 1) }}%
                        </span>
                    </div>
                    <div>
                        <small class="text-muted">الفترة الحالية</small>
                        <h6 class="mb-0">{{ number_format($revenueGrowth['current_revenue'], 0) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">توزيع طرق الدفع</h6>
                @if(isset($paymentMethodsAnalysis) && count($paymentMethodsAnalysis) > 0)
                <div class="row text-center">
                    @foreach($paymentMethodsAnalysis as $method)
                    <div class="col-6">
                        <h6 class="text-{{ $method['method'] === 'نقدي' ? 'success' : 'primary' }} mb-1">
                            {{ number_format($method['total'], 0) }}
                        </h6>
                        <small class="text-muted">{{ $method['method'] }} ({{ $method['count'] }} دفعة)</small>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center mb-0">لا توجد مدفوعات في هذه الفترة</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif