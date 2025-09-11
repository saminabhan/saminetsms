@extends('layouts.app')

@section('title', 'عرض مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> مصروف {{ $expense->category->name_ar ?? '' }}</h5>
        <div>
          <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i> تعديل</a>
          <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4"><strong>التاريخ:</strong> {{ $expense->spent_at->format('Y-m-d') }}</div>
          <div class="col-md-4"><strong>المبلغ:</strong> {{ number_format($expense->amount, 2) }} ش.ج</div>
          <div class="col-md-4"><strong>النوع:</strong> {{ $expense->category->type==='operational'?'تشغيلية':'رأس مالية' }}</div>
        </div>
        @if($expense->payment_method)
        <div class="mb-3"><strong>طريقة الدفع:</strong> {{ $expense->payment_method }}</div>
        @endif
        @if($expense->notes)
        <div class="mb-3"><strong>ملاحظات:</strong>
          <div class="alert alert-light mt-2">{{ $expense->notes }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection


