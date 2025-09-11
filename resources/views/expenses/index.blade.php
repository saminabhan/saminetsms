@extends('layouts.app')

@section('title', 'المصروفات')

@section('content')
<div class="d-flex justify-content بين align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-money-bill-wave me-2"></i> المصروفات</h1>
  <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة مصروف</a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>التاريخ</th>
            <th>الفئة</th>
            <th>النوع</th>
            <th>المبلغ</th>
            <th>بواسطة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($expenses as $exp)
          <tr>
            <td>{{ $exp->spent_at->format('Y-m-d') }}</td>
            <td>{{ $exp->category->name_ar ?? '-' }}</td>
            <td><span class="badge bg-secondary">{{ $exp->category->type==='operational'?'تشغيلية':'رأس مالية' }}</span></td>
            <td class="text-danger">{{ number_format($exp->amount, 2) }}</td>
            <td>{{ $exp->user->name ?? '-' }}</td>
            <td>
              <div class="btn-group">
                <a href="{{ route('expenses.show', $exp) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                <a href="{{ route('expenses.edit', $exp) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف المصروف؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center text-muted">لا توجد مصروفات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center">{{ $expenses->links() }}</div>
  </div>
</div>
@endsection


