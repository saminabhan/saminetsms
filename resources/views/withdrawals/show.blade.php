@extends('layouts.app')

@section('title', 'عرض سحب')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-arrow-down-wide-short me-2"></i> تفاصيل السحب</h5>
        <div>
          <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
          <form action="{{ route('withdrawals.destroy', $withdrawal) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف السحب؟')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">حذف</button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-2"><strong>التاريخ:</strong> {{ $withdrawal->withdrawn_at->format('Y-m-d') }}</div>
        <div class="mb-2"><strong>النوع:</strong> {{ $withdrawal->category_type==='operational'?'تشغيلية':($withdrawal->category_type==='capital'?'رأس مالية':'شركاء') }}</div>
        <div class="mb-2"><strong>المصدر:</strong> {{ $withdrawal->source==='cash'?'الصندوق النقدي':'الصندوق البنكي' }}</div>
        <div class="mb-2"><strong>المبلغ:</strong> {{ number_format($withdrawal->amount, 2) }} ش.ج</div>
        @if($withdrawal->notes)
          <div class="mb-2"><strong>ملاحظات:</strong>
            <div class="alert alert-light mt-2">{{ $withdrawal->notes }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection


