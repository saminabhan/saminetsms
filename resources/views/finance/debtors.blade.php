@extends('layouts.app')

@section('title', 'المدينون')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-user-minus me-2"></i> المدينون</h1>
  <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary">رجوع</a>
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
          @forelse($debtors as $bal)
          <tr>
            <td>{{ $bal->subscriber->name ?? '-' }}</td>
            <td>{{ $bal->subscriber->phone ?? '-' }}</td>
            <td class="text-end text-danger">{{ number_format($bal->balance, 2) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="text-center text-muted">لا يوجد مدينون</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center">{{ $debtors->links() }}</div>
  </div>
</div>
@endsection


