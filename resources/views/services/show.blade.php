@extends('layouts.app')

@section('title', 'عرض خدمة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-eye me-2"></i> {{ $service->name_ar }}</h5>
                <div>
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary btn-sm">
                        رجوع
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>الفئة:</strong>
                        <span class="badge bg-secondary">{{ $service->category->name_ar ?? '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>الحالة:</strong>
                        @if($service->is_active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-danger">معطل</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>السعر:</strong>
                        <span class="text-success">{{ number_format($service->price, 2) }} ش.ج</span>
                    </div>
                    <div class="col-md-4">
                        <strong>السرعة:</strong>
                        {{ $service->speed ?? '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>حد البيانات:</strong>
                        {{ $service->data_limit ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>المدة (ساعات):</strong>
                        {{ $service->duration_hours ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>المدة (أيام):</strong>
                        {{ $service->duration_days ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>السماح بالكمية:</strong>
                        @if($service->allow_quantity)
                            <span class="badge bg-info">نعم</span>
                        @else
                            <span class="badge bg-secondary">لا</span>
                        @endif
                    </div>
                </div>

                @if($service->description)
                    <div class="mb-3">
                        <strong>الوصف:</strong>
                        <div class="alert alert-light mt-2">{{ $service->description }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


