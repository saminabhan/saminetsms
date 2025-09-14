{{-- شريط التحليلات الجانبي - يمكن إدراجه في الليوت الرئيسي --}}

<div class="list-group list-group-flush">
    <div class="list-group-item bg-light border-0">
        <h6 class="mb-0 text-uppercase fw-bold">
            <i class="fas fa-chart-bar me-2"></i>
            التحليلات والتقارير
        </h6>
    </div>
    
    <a href="{{ route('analytics.index') }}" 
       class="list-group-item list-group-item-action border-0 {{ request()->routeIs('analytics.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i>
        لوحة التحليلات الرئيسية
        <small class="d-block text-muted">نظرة عامة شاملة</small>
    </a>
    
    <a href="{{ route('analytics.financial') }}" 
       class="list-group-item list-group-item-action border-0 {{ request()->routeIs('analytics.financial') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave me-2"></i>
        التقارير المالية
        <small class="d-block text-muted">المصروفات، الأرصدة، والتدفق النقدي</small>
    </a>
    
    <a href="{{ route('analytics.sales') }}" 
       class="list-group-item list-group-item-action border-0 {{ request()->routeIs('analytics.sales') ? 'active' : '' }}">
        <i class="fas fa-chart-line me-2"></i>
        تقرير المبيعات والعملاء
        <small class="d-block text-muted">أداء المبيعات وأفضل العملاء</small>
    </a>
    
    <a href="{{ route('analytics.distributors') }}" 
       class="list-group-item list-group-item-action border-0 {{ request()->routeIs('analytics.distributors') ? 'active' : '' }}">
        <i class="fas fa-users me-2"></i>
        تقرير الموزعين والمخزون
        <small class="d-block text-muted">أداء الموزعين وحالة البطاقات</small>
    </a>
    
    <a href="{{ route('analytics.services') }}" 
       class="list-group-item list-group-item-action border-0 {{ request()->routeIs('analytics.services') ? 'active' : '' }}">
        <i class="fas fa-cogs me-2"></i>
        تقرير الخدمات والحملات
        <small class="d-block text-muted">أداء الخدمات وتحليل الحملات</small>
    </a>
</div>

{{-- فلاتر سريعة --}}
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            فلاتر سريعة
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" id="quickFilterForm">
            <div class="mb-2">
                <label class="form-label small">الفترة الزمنية:</label>
                <select name="period" class="form-select form-select-sm" onchange="applyQuickFilter()">
                    <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="yesterday" {{ request('period') === 'yesterday' ? 'selected' : '' }}>أمس</option>
                    <option value="this_week" {{ request('period') === 'this_week' ? 'selected' : '' }}>هذا الأسبوع</option>
                    <option value="last_week" {{ request('period') === 'last_week' ? 'selected' : '' }}>الأسبوع الماضي</option>
                    <option value="this_month" {{ request('period') === 'this_month' || !request('period') ? 'selected' : '' }}>هذا الشهر</option>
                    <option value="last_month" {{ request('period') === 'last_month' ? 'selected' : '' }}>الشهر الماضي</option>
                    <option value="this_year" {{ request('period') === 'this_year' ? 'selected' : '' }}>هذا العام</option>
                </select>
            </div>
            
            <div class="mb-2">
                <label class="form-label small">من تاريخ:</label>
                <input type="date" name="date_from" class="form-control form-control-sm" 
                       value="{{ request('date_from') }}" onchange="applyQuickFilter()">
            </div>
            
            <div class="mb-3">
                <label class="form-label small">إلى تاريخ:</label>
                <input type="date" name="date_to" class="form-control form-control-sm" 
                       value="{{ request('date_to') }}" onchange="applyQuickFilter()">
            </div>
            
            <button type="submit" class="btn btn-primary btn-sm w-100">تطبيق الفلاتر</button>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">إعادة تعيين</a>
        </form>
    </div>
</div>

{{-- مؤشرات سريعة --}}
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>
            مؤشرات سريعة
        </h6>
    </div>
    <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
            <small class="text-muted">إيرادات اليوم</small>
            <span class="fw-bold text-success">
                @php
                    $todayRevenue = \App\Models\Payment::whereDate('paid_at', today())->sum('amount');
                @endphp
                {{ number_format($todayRevenue, 0) }}
            </span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
            <small class="text-muted">فواتير جديدة</small>
            <span class="fw-bold text-primary">
                @php
                    $todayInvoices = \App\Models\Invoice::whereDate('created_at', today())->count();
                @endphp
                {{ $todayInvoices }}
            </span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
            <small class="text-muted">مشتركين نشطين</small>
            <span class="fw-bold text-info">
                @php
                    $activeSubscribers = \App\Models\Subscriber::where('is_active', true)->count();
                @endphp
                {{ $activeSubscribers }}
            </span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <small class="text-muted">رصيد نقدي</small>
            <span class="fw-bold text-warning">
                @php
                    $cashBalance = \App\Models\CashBox::where('type', 'cash')->sum('opening_balance') 
                                 + \App\Models\Payment::where('method', 'cash')->sum('amount') 
                                 - \App\Models\Withdrawal::where('source', 'cash')->sum('amount');
                @endphp
                {{ number_format(max(0, $cashBalance), 0) }}
            </span>
        </div>
    </div>
</div>

<script>
function applyQuickFilter() {
    const period = document.querySelector('select[name="period"]').value;
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    switch(period) {
        case 'today':
            dateFrom.value = today.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            dateFrom.value = yesterday.toISOString().split('T')[0];
            dateTo.value = yesterday.toISOString().split('T')[0];
            break;
        case 'this_week':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            dateFrom.value = weekStart.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'last_week':
            const lastWeekStart = new Date(today);
            lastWeekStart.setDate(today.getDate() - today.getDay() - 7);
            const lastWeekEnd = new Date(lastWeekStart);
            lastWeekEnd.setDate(lastWeekStart.getDate() + 6);
            dateFrom.value = lastWeekStart.toISOString().split('T')[0];
            dateTo.value = lastWeekEnd.toISOString().split('T')[0];
            break;
        case 'this_month':
            dateFrom.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'last_month':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            dateFrom.value = lastMonth.toISOString().split('T')[0];
            dateTo.value = lastMonthEnd.toISOString().split('T')[0];
            break;
        case 'this_year':
            dateFrom.value = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
    }
}
</script>