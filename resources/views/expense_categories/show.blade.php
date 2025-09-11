@extends('layouts.app')

@section('title', 'عرض فئة مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> {{ $category->name_ar }}</h5>
        <div>
          <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i> تعديل</a>
          <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4"><strong>النوع:</strong> {{ $category->type==='operational' ? 'تشغيلية':'رأس مالية' }}</div>
          <div class="col-md-4"><strong>الاسم (EN):</strong> {{ $category->name ?? '-' }}</div>
          <div class="col-md-4">
            <strong>الحالة:</strong>
            {!! $category->is_active ? '<span class="badge bg-success">نشطة</span>' : '<span class="badge bg-danger">معطلة</span>' !!}
          </div>
        </div>
        @if($category->description)
        <div class="mb-3"><strong>الوصف:</strong>
          <div class="alert alert-light mt-2">{{ $category->description }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection


