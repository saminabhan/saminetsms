@extends('layouts.app')

@section('title', 'تعديل خدمة')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> تعديل خدمة: {{ $service->name_ar }}</h5>
                <a href="{{ route('services.show', $service) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye me-2"></i> عرض الخدمة
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('services.update', $service) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">الفئة</label>
                        <select name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror" required>
                            <option value="">اختر الفئة</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name_ar ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم (EN)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم (AR)</label>
                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $service->name_ar) }}" required>
                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">السعر</label>
                            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">السرعة</label>
                            <input type="text" name="speed" class="form-control @error('speed') is-invalid @enderror" value="{{ old('speed', $service->speed) }}">
                            @error('speed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">حد البيانات</label>
                            <input type="text" name="data_limit" class="form-control @error('data_limit') is-invalid @enderror" value="{{ old('data_limit', $service->data_limit) }}">
                            @error('data_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">المدة (ساعات)</label>
                            <input type="number" name="duration_hours" class="form-control @error('duration_hours') is-invalid @enderror" value="{{ old('duration_hours', $service->duration_hours) }}" min="1">
                            @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">المدة (أيام)</label>
                            <input type="number" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days', $service->duration_days) }}" min="1">
                            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $service->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">نشط</label>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="allow_quantity" id="allow_quantity" value="1" {{ old('allow_quantity', $service->allow_quantity) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_quantity">السماح بإدخال كمية (بطاقات/شهرية)</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('services.show', $service) }}" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


