@extends('layouts.app')

@section('title', 'تعديل فئة مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="fas fa-edit me-2"></i> تعديل فئة: {{ $category->name_ar }}</div>
      <div class="card-body">
        <form action="{{ route('expense-categories.update', $category) }}" method="POST">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label">النوع</label>
            <select name="type" class="form-select">
              <option value="operational" {{ $category->type==='operational'?'selected':'' }}>تشغيلية</option>
              <option value="capital" {{ $category->type==='capital'?'selected':'' }}>رأس مالية</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم (AR)</label>
            <input type="text" name="name_ar" class="form-control" value="{{ $category->name_ar }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم (EN)</label>
            <input type="text" name="name" class="form-control" value="{{ $category->name }}">
          </div>
          <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
          </div>
          <div class="form-check form-switch mb-3">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $category->is_active?'checked':'' }}>
            <label class="form-check-label" for="is_active">نشطة</label>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('expense-categories.show', $category) }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


