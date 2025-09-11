@extends('layouts.app')

@section('title', 'عرض فئة خدمة')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-tag me-2"></i> {{ $category->name_ar }}</h5>
        <div>
          <a href="{{ route('service-categories.edit', $category) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i> تعديل</a>
          <a href="{{ route('service-categories.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>الاسم (AR):</strong> {{ $category->name_ar }}
          </div>
          <div class="col-md-6">
            <strong>الاسم (EN):</strong> {{ $category->name ?? '-' }}
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>الحالة:</strong>
            @if($category->is_active)
              <span class="badge bg-success">نشطة</span>
            @else
              <span class="badge bg-danger">معطلة</span>
            @endif
          </div>
        </div>
        @if($category->description)
        <div class="mb-3">
          <strong>الوصف:</strong>
          <div class="alert alert-light mt-2">{{ $category->description }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection


