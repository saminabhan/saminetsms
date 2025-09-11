<?php
// routes/web.php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ServiceController;
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

        // Finance quick stats
        $cashIn = (float) \App\Models\Payment::where('method','cash')->sum('amount');
        $bankIn = (float) \App\Models\Payment::where('method','bank')->sum('amount');
        $cashOut = (float) \App\Models\Withdrawal::where('source','cash')->sum('amount');
        $bankOut = (float) \App\Models\Withdrawal::where('source','bank')->sum('amount');
        $cashBalance = max(0, $cashIn - $cashOut);
        $bankBalance = max(0, $bankIn - $bankOut);

        $startThisMonth = now()->startOfMonth()->format('Y-m-d');
        $endThisMonth = now()->endOfMonth()->format('Y-m-d');
        $startPrevMonth = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endPrevMonth = now()->subMonth()->endOfMonth()->format('Y-m-d');

        $revenueThisMonth = (float) \App\Models\Payment::whereBetween('paid_at', [$startThisMonth, $endThisMonth])->sum('amount');
        $revenuePrevMonth = (float) \App\Models\Payment::whereBetween('paid_at', [$startPrevMonth, $endPrevMonth])->sum('amount');
        $moMChange = $revenuePrevMonth > 0 ? (($revenueThisMonth - $revenuePrevMonth) / $revenuePrevMonth) * 100 : null;

        $topPayers = \App\Models\Payment::selectRaw('invoices.subscriber_id, SUM(payments.amount) as total')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('paid_at', [$startThisMonth, $endThisMonth])
            ->groupBy('invoices.subscriber_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function($row){
                $subscriber = \App\Models\Subscriber::find($row->subscriber_id);
                return [
                    'subscriber' => $subscriber,
                    'total' => $row->total,
                ];
            });

        return view('dashboard', compact(
            'totalSubscribers',
            'activeSubscribers', 
            'totalMessages',
            'sentMessages',
            'failedMessages',
            'smsBalance',
            'cashBalance',
            'bankBalance',
            'revenueThisMonth',
            'revenuePrevMonth',
            'moMChange',
            'topPayers'
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

        // روتس المالية
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/debtors', [FinanceController::class, 'debtors'])->name('debtors');
        Route::get('/balances', [FinanceController::class, 'balances'])->name('balances');
        Route::post('/update-balances', [FinanceController::class, 'updateAllBalances'])->name('update.balances');
    });

    // روتس الفواتير
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.payment');
    Route::get('api/service-price', [InvoiceController::class, 'getServicePrice'])->name('api.service.price');

    // روتس الخدمات
    Route::resource('services', ServiceController::class);
    Route::post('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle');

    // فئات المصاريف، الشركاء، والسحوبات (بدون صفحة المصروفات)
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class);
    Route::resource('partners', \App\Http\Controllers\PartnerController::class);
    Route::resource('withdrawals', \App\Http\Controllers\WithdrawalController::class)->only(['index','create','store','show','destroy']);

    // روتس فئات الخدمات
    Route::resource('service-categories', \App\Http\Controllers\ServiceCategoryController::class);
});
// في routes/web.php أضف هذا السطر
Route::get('/get-services-by-category', [InvoiceController::class, 'getServicesByCategory'])
    ->name('invoices.getServicesByCategory');
});