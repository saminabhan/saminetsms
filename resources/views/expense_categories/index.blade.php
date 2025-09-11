@extends('layouts.app')

@section('title', 'فئات المصروفات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-list me-2"></i> فئات المصروفات</h1>
  <a href="{{ route('expense-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة فئة</a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>النوع</th>
            <th>الاسم</th>
            <th>الحالة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $cat)
          <tr>
            <td><span class="badge bg-secondary">{{ $cat->type === 'operational' ? 'تشغيلية' : 'رأس مالية' }}</span></td>
            <td><strong>{{ $cat->name_ar }}</strong></td>
            <td>{!! $cat->is_active ? '<span class="badge bg-success">نشطة</span>' : '<span class="badge bg-danger">معطلة</span>' !!}</td>
            <td>
              <div class="btn-group">
                <a href="{{ route('expense-categories.show', $cat) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                <a href="{{ route('expense-categories.edit', $cat) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                <form action="{{ route('expense-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف الفئة؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center text-muted">لا توجد فئات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center">{{ $categories->links() }}</div>
  </div>
</div>
@endsection


