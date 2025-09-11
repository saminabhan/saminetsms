@extends('layouts.app')

@section('title', 'السحوبات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-arrow-down-wide-short me-2"></i> السحوبات</h1>
  <a href="{{ route('withdrawals.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة سحب</a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>التاريخ</th>
            <th>النوع</th>
            <th>المصدر</th>
            <th>المبلغ</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($withdrawals as $w)
          <tr>
            <td>{{ $w->withdrawn_at->format('Y-m-d') }}</td>
            <td><span class="badge bg-secondary">{{ $w->category_type==='operational'?'تشغيلية':($w->category_type==='capital'?'رأس مالية':'شركاء') }}</span></td>
            <td>{!! $w->source==='cash' ? '<span class="badge bg-info">نقدي</span>' : '<span class="badge bg-primary">بنكي</span>' !!}</td>
            <td class="text-danger">{{ number_format($w->amount, 2) }}</td>
            <td>
              <a href="{{ route('withdrawals.show', $w) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center text-muted">لا توجد سحوبات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center">{{ $withdrawals->links() }}</div>
  </div>
</div>
@endsection


