@extends('layouts.app')

@section('title', 'إضافة سحب')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><i class="fas fa-arrow-down-wide-short me-2"></i> إضافة سحب</div>
      <div class="card-body">
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('withdrawals.store') }}" method="POST" id="withdrawForm">
          @csrf
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">نوع السحب</label>
              <select name="category_type" id="category_type" class="form-select" required>
                <option value="operational">مصروفات تشغيلية</option>
                <option value="capital">مصروفات رأس مالية</option>
                <option value="partner">مسحوبات شركاء</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">المصدر</label>
              <select name="source" class="form-select" required>
                <option value="cash">الصندوق النقدي</option>
                <option value="bank">الصندوق البنكي</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">الفئة/الشريك</label>
              <select name="category_id" id="category_id" class="form-select" required>
                <option value="">اختر أولاً</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">المبلغ</label>
              <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">التاريخ</label>
              <input type="date" name="withdrawn_at" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('withdrawals.index') }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const categoryType = document.getElementById('category_type');
  const categorySelect = document.getElementById('category_id');
  const operational = @json($operational->map(fn($c)=>['id'=>$c->id,'name'=>$c->name_ar]));
  const capital = @json($capital->map(fn($c)=>['id'=>$c->id,'name'=>$c->name_ar]));
  const partners = @json($partners->map(fn($p)=>['id'=>$p->id,'name'=>$p->name]));

  function refill() {
    const type = categoryType.value;
    let list = [];
    if (type === 'operational') list = operational;
    else if (type === 'capital') list = capital;
    else list = partners;
    categorySelect.innerHTML = '<option value="">اختر</option>';
    list.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.id; opt.textContent = item.name;
      categorySelect.appendChild(opt);
    });
  }
  categoryType.addEventListener('change', refill);
  refill();
});
</script>
@endpush
@endsection


