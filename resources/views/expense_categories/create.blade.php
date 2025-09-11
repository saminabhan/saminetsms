@extends('layouts.app')

@section('title', 'إضافة فئة مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="fas fa-plus me-2"></i> إضافة فئة</div>
      <div class="card-body">
        <form action="{{ route('expense-categories.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">النوع</label>
            <select name="type" class="form-select">
              <option value="operational">تشغيلية</option>
              <option value="capital">رأس مالية</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم (AR)</label>
            <input type="text" name="name_ar" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم (EN)</label>
            <input type="text" name="name" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-check form-switch mb-3">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
            <label class="form-check-label" for="is_active">نشطة</label>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


