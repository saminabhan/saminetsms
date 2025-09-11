@extends('layouts.app')

@section('title', 'إضافة مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><i class="fas fa-plus me-2"></i> إضافة مصروف</div>
      <div class="card-body">
        <form action="{{ route('expenses.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">الفئة</label>
            <select name="expense_category_id" class="form-select" required>
              <option value="">اختر الفئة</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name_ar }} ({{ $cat->type==='operational'?'تشغيلية':'رأس مالية' }})</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">المبلغ</label>
              <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">التاريخ</label>
              <input type="date" name="spent_at" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">طريقة الدفع</label>
              <select name="payment_method" class="form-select">
                <option value="">بدون</option>
                <option value="cash">نقدي</option>
                <option value="bank">بنكي</option>
                <option value="other">أخرى</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


