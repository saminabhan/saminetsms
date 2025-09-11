@extends('layouts.app')

@section('title', 'المالية')

@section('content')
<form method="GET" class="mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">من تاريخ</label>
      <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">إلى تاريخ</label>
      <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> بحث</button>
      <a href="{{ route('finance.index') }}" class="btn btn-secondary">إزالة التصفية</a>
    </div>
  </div>
</form>

<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">إجمالي الفواتير</div>
            <div class="fs-4">{{ number_format($totalInvoices, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-file-invoice fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stats-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">إجمالي المدفوع</div>
            <div class="fs-4">{{ number_format($totalPaid, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-hand-holding-dollar fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stats-card warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">المتبقي</div>
            <div class="fs-4">{{ number_format($totalOutstanding, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-scale-unbalanced fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stats-card danger">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">عدد المدينين</div>
            <div class="fs-4">{{ $totalDebtors }}</div>
          </div>
          <i class="fas fa-user-minus fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">نقدي</div>
            <div class="fs-4">{{ number_format($totalCash ?? 0, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-cash-register fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">بنكي</div>
            <div class="fs-4">{{ number_format($totalBank ?? 0, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-building-columns fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stats-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold">إيراد الشهر الحالي</div>
            <div class="fs-4">{{ number_format($currentMonthRevenue ?? 0, 2) }} ش.ج</div>
          </div>
          <i class="fas fa-coins fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-clock me-2"></i> آخر 10 فواتير</h6>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.index') }}">عرض كل الفواتير</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>رقم الفاتورة</th>
                <th>المشترك</th>
                <th>الخدمة</th>
                <th>المبلغ النهائي</th>
                <th>المدفوع</th>
                <th>الحالة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentInvoices as $inv)
              <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->subscriber->name ?? '-' }}</td>
                <td>{{ $inv->service->name_ar ?? '-' }}</td>
                <td>{{ number_format($inv->final_amount, 2) }}</td>
                <td>{{ number_format($inv->paid_amount, 2) }}</td>
                <td>
                  @switch($inv->payment_status)
                    @case('paid')<span class="badge bg-success">مدفوعة</span>@break
                    @case('partial')<span class="badge bg-warning">جزئية</span>@break
                    @default <span class="badge bg-danger">غير مدفوعة</span>
                  @endswitch
                </td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-center text-muted">لا توجد فواتير</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-triangle-exclamation me-2"></i> أعلى المدينين</h6>
        <a class="btn btn-sm btn-outline-danger" href="{{ route('finance.debtors') }}">عرض الكل</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>المشترك</th>
                <th>الرصيد</th>
              </tr>
            </thead>
            <tbody>
              @forelse($debtors as $bal)
              <tr>
                <td>{{ $bal->subscriber->name ?? '-' }}</td>
                <td class="text-danger">{{ number_format($bal->balance, 2) }}</td>
              </tr>
              @empty
              <tr><td colspan="2" class="text-center text-muted">لا يوجد مدينون</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


