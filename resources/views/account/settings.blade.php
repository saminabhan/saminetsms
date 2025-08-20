@extends('layouts.app')

@section('title', 'إعدادات الحساب')

@section('content')
<div class="container py-5">
    <h2 class="mb-5 text-center fw-bold"><i class="fas fa-user me-1"></i> إعدادات الحساب</h2>

    <!-- القسم الأول: الاسم والإيميل -->
    <div class="card mb-4 shadow-sm border rounded-4 animate__animated animate__fadeInUp animate__delay-0.5s">
        <div class="card-header bg-light fw-bold text-primary">
            معلومات الحساب
        </div>
        <div class="card-body">
            <form action="{{ route('account.settings.updateProfile') }}" method="POST" dir="rtl">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label fw-semibold">الاسم</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg text-start" value="{{ old('name', $user->name) }}" required>
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg text-start" value="{{ old('email', $user->email) }}" required>
                    @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm hover-scale">تحديث المعلومات</button>
            </form>
        </div>
    </div>

    <!-- القسم الثاني: كلمة المرور -->
    <div class="card shadow-sm border rounded-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card-header bg-light fw-bold text-warning">
            تغيير كلمة المرور
        </div>
        <div class="card-body">
            <form action="{{ route('account.settings.updatePassword') }}" method="POST" dir="rtl">
                @csrf

                <div class="mb-4">
                    <label for="current_password" class="form-label fw-semibold">كلمة المرور الحالية</label>
                    <input type="password" name="current_password" id="current_password" class="form-control form-control-lg text-start" required>
                    @error('current_password')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg text-start">
                    @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg text-start">
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100 shadow-sm hover-scale">تحديث كلمة المرور</button>
            </form>
        </div>
    </div>
</div>

<style>
    /* تأثير تكبير بسيط عند المرور على الأزرار */
    .hover-scale:hover {
        transform: scale(1.03);
        transition: transform 0.2s;
    }
</style>
@endsection
