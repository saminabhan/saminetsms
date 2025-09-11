@extends('layouts.app')

@section('title', 'تعديل شريك')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="fas fa-edit me-2"></i> تعديل شريك</div>
      <div class="card-body">
        <form action="{{ route('partners.update', $partner) }}" method="POST">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label">الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ $partner->name }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-control" value="{{ $partner->phone }}">
          </div>
          <div class="mb-3">
            <label class="form-label">النسبة %</label>
            <input type="number" step="0.01" min="0" max="100" name="share_percentage" class="form-control" value="{{ $partner->share_percentage }}" required>
          </div>
          <div class="form-check form-switch mb-3">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $partner->is_active?'checked':'' }}>
            <label class="form-check-label" for="is_active">نشط</label>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('partners.show', $partner) }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


