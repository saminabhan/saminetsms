@extends('layouts.app')

@section('title', 'إضافة موزع')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5>إضافة موزع جديد</h5></div>
            <div class="card-body">
                <form action="{{ route('distributors.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>العنوان</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>النوع <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="distributor">موزع</option>
                            <option value="sales_point">نقطة بيع</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>ملاحظات</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                    <button class="btn btn-success">حفظ</button>
                    <a href="{{ route('distributors.index') }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
