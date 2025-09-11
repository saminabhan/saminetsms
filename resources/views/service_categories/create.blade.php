@extends('layouts.app')

@section('title', 'إضافة فئة خدمة')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="fas fa-plus me-2"></i> إضافة فئة</div>
      <div class="card-body">
        <form action="{{ route('service-categories.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">الاسم (AR) <span class="text-danger">*</span></label>
            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم (EN)</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-check form-switch mb-3">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
            <label class="form-check-label" for="is_active">نشطة</label>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('service-categories.index') }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


