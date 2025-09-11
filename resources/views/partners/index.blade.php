@extends('layouts.app')

@section('title', 'الشركاء')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0"><i class="fas fa-people-group me-2"></i> الشركاء</h1>
  <a href="{{ route('partners.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة شريك</a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>الاسم</th>
            <th>الهاتف</th>
            <th>النسبة %</th>
            <th>الحالة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($partners as $p)
          <tr>
            <td><strong>{{ $p->name }}</strong></td>
            <td>{{ $p->phone ?? '-' }}</td>
            <td>{{ number_format($p->share_percentage, 2) }}</td>
            <td>{!! $p->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">معطل</span>' !!}</td>
            <td>
              <div class="btn-group">
                <a href="{{ route('partners.show', $p) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                <a href="{{ route('partners.edit', $p) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                <form action="{{ route('partners.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف الشريك؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center text-muted">لا يوجد شركاء</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center">{{ $partners->links() }}</div>
  </div>
</div>
@endsection


