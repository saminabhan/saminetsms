@extends('layouts.app')

@section('title', 'أرصدة المشتركين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-scale-balanced me-2"></i> أرصدة المشتركين</h1>
  <form action="{{ route('finance.update.balances') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-outline-primary">
      <i class="fas fa-rotate me-1"></i> تحديث جميع الأرصدة
    </button>
  </form>
  </div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>المشترك</th>
            <th>الهاتف</th>
            <th class="text-end">الرصيد</th>
          </tr>
        </thead>
        <tbody>
          @forelse($balances as $bal)
          <tr>
            <td>{{ $bal->subscriber->name ?? '-' }}</td>
            <td>{{ $bal->subscriber->phone ?? '-' }}</td>
            <td class="text-end {{ $bal->balance < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($bal->balance, 2) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center">{{ $balances->links() }}</div>
  </div>
</div>
@endsection


