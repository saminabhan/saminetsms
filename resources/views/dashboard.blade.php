@extends('layouts.app')

@section('title', 'لوحة التحكم الرئيسية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-tachometer-alt me-2"></i>
        لوحة التحكم
    </h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h4>{{ $totalSubscribers }}</h4>
                <p class="mb-0">إجمالي المشتركين</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <i class="fas fa-user-check fa-3x mb-3"></i>
                <h4>{{ $activeSubscribers }}</h4>
                <p class="mb-0">المشتركين النشطين</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <i class="fas fa-envelope fa-3x mb-3"></i>
                <h4>{{ $totalMessages }}</h4>
                <p class="mb-0">إجمالي الرسائل</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h4>{{ $sentMessages }}</h4>
                <p class="mb-0">الرسائل المرسلة</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <i class="fas fa-comment-dots fa-3x mb-3"></i>
                <h4>{{ $smsBalance }}</h4>
                <p class="mb-0">الرسائل المتبقية</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 animate__animated animate__fadeInUp animate__delay-0.5s">
  <div class="col-md-3 mb-3">
    <div class="card stats-card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold">رصيد الصندوق النقدي</div>
          <div class="fs-4">{{ number_format($cashBalance ?? 0, 2) }} ش.ج</div>
        </div>
        <i class="fas fa-cash-register fa-2x"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card stats-card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold">رصيد الصندوق البنكي</div>
          <div class="fs-4">{{ number_format($bankBalance ?? 0, 2) }} ش.ج</div>
        </div>
        <i class="fas fa-building-columns fa-2x"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card stats-card success">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold">إيراد هذا الشهر</div>
          <div class="fs-4">{{ number_format($revenueThisMonth ?? 0, 2) }} ش.ج</div>
        </div>
        <i class="fas fa-coins fa-2x"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card stats-card {{ ($moMChange ?? 0) >= 0 ? 'success' : 'danger' }}">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold">مقارنة بالشهر السابق</div>
          <div class="fs-4">{{ isset($moMChange) ? round($moMChange, 1) . '%' : 'N/A' }}</div>
        </div>
        <i class="fas fa-chart-line fa-2x"></i>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4 animate__animated animate__fadeInUp animate__delay-0.5s">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0"><i class="fas fa-trophy me-2"></i> أعلى 5 مشتركين دفعاً هذا الشهر</h5>
    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">عرض الفواتير</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>المشترك</th>
            <th>الهاتف</th>
            <th>إجمالي المدفوع</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($topPayers ?? []) as $row)
          <tr>
            <td>{{ $row['subscriber']->name ?? '-' }}</td>
            <td>{{ $row['subscriber']->phone ?? '-' }}</td>
            <td class="text-success">{{ number_format($row['total'] ?? 0, 2) }} ش.ج</td>
          </tr>
          @empty
          <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات لهذا الشهر</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  </div>


<!-- Quick Actions -->
<div class="row animate__animated animate__fadeInUp animate__delay-0.5s">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('subscribers.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>
                        إضافة مشترك جديد
                    </a>
                    <a href="{{ route('messages.create') }}" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>
                        إرسال رسالة جديدة
                    </a>
                    <a href="{{ route('messages.index') }}" class="btn btn-info">
                        <i class="fas fa-history me-2"></i>
                        عرض سجل الرسائل
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    إحصائيات الرسائل
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-success">{{ $sentMessages }}</h4>
                        <small class="text-muted">مرسلة</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-danger">{{ $failedMessages }}</h4>
                        <small class="text-muted">فشلت</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-primary">{{ $totalMessages }}</h4>
                        <small class="text-muted">المجموع</small>
                    </div>
                </div>
                
                @if($totalMessages > 0)
                    <div class="mt-3">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ ($sentMessages / $totalMessages) * 100 }}%"></div>
                            <div class="progress-bar bg-danger" role="progressbar" 
                                 style="width: {{ ($failedMessages / $totalMessages) * 100 }}%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            نسبة نجاح الإرسال: {{ round(($sentMessages / $totalMessages) * 100, 1) }}%
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection