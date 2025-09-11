@extends('layouts.app')

@section('title', 'فئات الخدمات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-tags me-2"></i> فئات الخدمات</h1>
    <a href="{{ route('service-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> إضافة فئة
    </a>
  </div>

<div class="card">
  <div class="card-body">
    @if($categories->count())
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>الاسم (AR)</th>
            <th>الاسم (EN)</th>
            <th>الحالة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @foreach($categories as $category)
          <tr>
            <td><strong>{{ $category->name_ar }}</strong></td>
            <td>{{ $category->name ?? '-' }}</td>
            <td>
              @if($category->is_active)
                <span class="badge bg-success">نشطة</span>
              @else
                <span class="badge bg-danger">معطلة</span>
              @endif
            </td>
            <td>
              <div class="btn-group">
                <a href="{{ route('service-categories.show', $category) }}" class="btn btn-outline-info btn-sm">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('service-categories.edit', $category) }}" class="btn btn-outline-warning btn-sm">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('service-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الفئة؟')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center">{{ $categories->links() }}</div>
    @else
      <div class="text-center py-5">
        لا توجد فئات.
      </div>
    @endif
  </div>
</div>
@endsection


