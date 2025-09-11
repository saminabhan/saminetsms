@extends('layouts.app')

@section('title', 'عرض شريك')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i> {{ $partner->name }}</h5>
        <div>
          <a href="{{ route('partners.edit', $partner) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i> تعديل</a>
          <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-2"><strong>الهاتف:</strong> {{ $partner->phone ?? '-' }}</div>
        <div class="mb-2"><strong>النسبة %:</strong> {{ number_format($partner->share_percentage, 2) }}</div>
        <div class="mb-2"><strong>الحالة:</strong> {!! $partner->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">معطل</span>' !!}</div>
      </div>
    </div>
  </div>
</div>
@endsection


