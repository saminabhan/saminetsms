@extends('layouts.app')

@section('title', 'تعديل الموزع')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5>تعديل الموزع: {{ $distributor->name }}</h5></div>
            <div class="card-body">
                <form action="{{ route('distributors.update', $distributor) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- مهم لتحديث البيانات --}}
                    
                    <div class="mb-3">
                        <label>الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $distributor->name) }}" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $distributor->phone) }}" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $distributor->email) }}" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>العنوان</label>
                        <input type="text" name="address" value="{{ old('address', $distributor->address) }}" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>النوع <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="distributor" {{ old('type', $distributor->type) == 'distributor' ? 'selected' : '' }}>موزع</option>
                            <option value="sales_point" {{ old('type', $distributor->type) == 'sales_point' ? 'selected' : '' }}>نقطة بيع</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes', $distributor->notes) }}</textarea>
                    </div>
                    
                    <button class="btn btn-success">تحديث</button>
                    <a href="{{ route('distributors.index') }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
