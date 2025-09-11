@extends('layouts.app')

@section('title', 'تعديل مصروف')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><i class="fas fa-edit me-2"></i> تعديل مصروف</div>
      <div class="card-body">
        <form action="{{ route('expenses.update', $expense) }}" method="POST">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label">الفئة</label>
            <select name="expense_category_id" class="form-select" required>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $expense->expense_category_id==$cat->id?'selected':'' }}>
                  {{ $cat->name_ar }} ({{ $cat->type==='operational'?'تشغيلية':'رأس مالية' }})
                </option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">المبلغ</label>
              <input type="number" step="0.01" name="amount" class="form-control" value="{{ $expense->amount }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">التاريخ</label>
              <input type="date" name="spent_at" class="form-control" value="{{ $expense->spent_at->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">طريقة الدفع</label>
              <select name="payment_method" class="form-select">
                <option value="" {{ $expense->payment_method==''?'selected':'' }}>بدون</option>
                <option value="cash" {{ $expense->payment_method=='cash'?'selected':'' }}>نقدي</option>
                <option value="bank" {{ $expense->payment_method=='bank'?'selected':'' }}>بنكي</option>
                <option value="other" {{ $expense->payment_method=='other'?'selected':'' }}>أخرى</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="3">{{ $expense->notes }}</textarea>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


