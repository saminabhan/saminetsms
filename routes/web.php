<?php
// routes/web.php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SessionsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// صفحة login
Route::get('login', function() {
    return view('auth.login');
})->name('login');

// معالجة login
Route::post('login', function(\Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');

    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
});

Route::get('password/reset', function() {
    return "صفحة إعادة تعيين كلمة المرور";
})->name('password.request');

// معالجة logout
Route::post('logout', function(\Illuminate\Http\Request $request){
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// جميع الراوتات التالية محمية بالـ auth middleware
Route::middleware('auth')->group(function() {
    // مسارات المشتركين
    Route::resource('subscribers', SubscriberController::class);
    Route::patch('subscribers/{subscriber}/toggle', [SubscriberController::class, 'toggle'])
         ->name('subscribers.toggle');

    // مسارات الرسائل
    Route::resource('messages', MessageController::class)->except(['edit', 'update', 'destroy']);
    Route::post('messages/{message}/resend', [MessageController::class, 'resend'])
         ->name('messages.resend');
    
    // مسار مساعد الذكاء الاصطناعي (مرة واحدة فقط)
    Route::post('/messages/ai-suggest', [MessageController::class, 'aiSuggest'])
        ->name('messages.aiSuggest');

    Route::get('/sessions', [SessionsController::class, 'index'])->name('sessions.index');

    // مسار لوحة التحكم الرئيسية
    Route::get('/dashboard', function () {
        $totalSubscribers = \App\Models\Subscriber::count();
        $activeSubscribers = \App\Models\Subscriber::where('is_active', true)->count();
        $totalMessages = \App\Models\Message::count();
        $sentMessages = \App\Models\Message::where('status', 'sent')->count();
        $failedMessages = \App\Models\Message::where('status', 'failed')->count();

        // جلب رصيد الرسائل من API
        $smsBalance = 0;
        try {
            $response = file_get_contents('http://hotsms.ps/getbalance.php?api_token=66ef464c07d8f');
            $smsBalance = intval($response);
        } catch (\Exception $e) {
            $smsBalance = 0;
        }

        return view('dashboard', compact(
            'totalSubscribers',
            'activeSubscribers', 
            'totalMessages',
            'sentMessages',
            'failedMessages',
            'smsBalance'
        ));
    })->name('dashboard');
Route::middleware(['auth'])->group(function () {
    // عرض صفحة الإعدادات
    Route::get('/account/settings', [AccountSettingsController::class, 'index'])
        ->name('account.settings');

    // تحديث الاسم والإيميل
    Route::post('/account/settings/profile', [AccountSettingsController::class, 'updateProfile'])
        ->name('account.settings.updateProfile');

    // تحديث كلمة المرور
    Route::post('/account/settings/password', [AccountSettingsController::class, 'updatePassword'])
        ->name('account.settings.updatePassword');
});

});