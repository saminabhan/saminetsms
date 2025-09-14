<?php
// routes/web.php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SessionsController;
use App\Models\Invoice;
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
    // Subscribers & Messages stats
    $totalSubscribers = \App\Models\Subscriber::count();
    $activeSubscribers = \App\Models\Subscriber::where('is_active', true)->count();
    $totalMessages = \App\Models\Message::count();
    $sentMessages = \App\Models\Message::where('status', 'sent')->count();
    $failedMessages = \App\Models\Message::where('status', 'failed')->count();

   $smsBalance = 0;
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://hotsms.ps/getbalance.php?api_token=66ef464c07d8f");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // مهلة 10 ثواني
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // في حال SSL مش مضبوط
    $response = curl_exec($ch);
    if ($response === false) {
        throw new \Exception(curl_error($ch));
    }
    $smsBalance = intval($response);
    curl_close($ch);
} catch (\Exception $e) {
    $smsBalance = 0;
}

    // Finance quick stats
    // الرصيد الافتتاحي لكل صندوق
    $cashBoxOpening = (float) \App\Models\CashBox::where('type', 'cash')->sum('opening_balance');
    $bankBoxOpening = (float) \App\Models\CashBox::where('type', 'bank')->sum('opening_balance');

    // تدفقات نقدي/بنكي
    $cashIn = (float) \App\Models\Payment::where('method','cash')->sum('amount');
    $bankIn = (float) \App\Models\Payment::where('method','bank')->sum('amount');
    $cashOut = (float) \App\Models\Withdrawal::where('source','cash')->sum('amount');
    $bankOut = (float) \App\Models\Withdrawal::where('source','bank')->sum('amount');

    // الرصيد الحالي مع الرصيد الافتتاحي
    $cashBalance = max(0, $cashBoxOpening + $cashIn - $cashOut);
    $bankBalance = max(0, $bankBoxOpening + $bankIn - $bankOut);

    // Month revenue
   $startThisMonth = now()->startOfMonth()->format('Y-m-d');
    $endThisMonth   = now()->endOfMonth()->format('Y-m-d');

    // الشهر السابق
    $startPrevMonth = now()->subMonth()->startOfMonth()->format('Y-m-d');
    $endPrevMonth   = now()->subMonth()->endOfMonth()->format('Y-m-d');

    // الإيراد من الفواتير الشهر الحالي
    $revenueThisMonth = (float) Invoice::whereBetween('created_at', [$startThisMonth, $endThisMonth])
        ->sum('final_amount');

    // الإيراد من الفواتير الشهر السابق
    $revenuePrevMonth = (float) Invoice::whereBetween('created_at', [$startPrevMonth, $endPrevMonth])
        ->sum('final_amount');

    // نسبة التغير (Month-over-Month)
    $moMChange = $revenuePrevMonth > 0
        ? (($revenueThisMonth - $revenuePrevMonth) / $revenuePrevMonth) * 100
        : null;


    // Top 5 payers this month
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
        'cashBoxOpening',
        'bankBoxOpening',
        'revenueThisMonth',
        'revenuePrevMonth',
        'moMChange',
        'topPayers'
    ));
})->name('dashboard');

Route::get('invoices/get-distributor-cards', [InvoiceController::class, 'getDistributorCards'])->name('invoices.getDistributorCards');

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
    Route::resource('distributors', DistributorController::class);
Route::get('distributors/{distributor}/add-cards', [DistributorController::class, 'addCards'])->name('distributors.add-cards');
Route::post('distributors/{distributor}/store-cards', [DistributorController::class, 'storeCards'])->name('distributors.store-cards');

// Ajax routes for distributors
Route::get('distributors/services-by-category', [DistributorController::class, 'getServicesByCategory']);
Route::get('distributors/{distributor}/cards', [DistributorController::class, 'getDistributorCards']);

// Ajax routes for invoices
Route::get('invoices/services-by-category', [InvoiceController::class, 'getServicesByCategory']);
Route::get('invoices/service-price', [InvoiceController::class, 'getServicePrice']);

// إضافة هذه المسارات إلى routes/web.php ضمن middleware auth

// مسارات التحليلات المحسنة
Route::prefix('analytics')->name('analytics.')->group(function () {
    // الصفحة الرئيسية للتحليلات
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    
    // التقارير المالية التفصيلية
    Route::get('/financial', [AnalyticsController::class, 'financialReports'])->name('financial');
    
    // تقرير المبيعات والعملاء
    Route::get('/sales', [AnalyticsController::class, 'salesReports'])->name('sales');
    
    // تقرير الموزعين والمخزون
    Route::get('/distributors', [AnalyticsController::class, 'distributorsReport'])->name('distributors');
    
    // تقرير الخدمات والحملات
    Route::get('/services', [AnalyticsController::class, 'servicesReport'])->name('services');
    
    // API endpoints للرسوم البيانية
    Route::get('/api/monthly-revenue/{year?}', [AnalyticsController::class, 'monthlyRevenueApi'])->name('monthlyRevenueApi');
    Route::get('/api/expenses-chart', [AnalyticsController::class, 'expensesChartApi'])->name('expensesChartApi');
    Route::get('/api/services-performance', [AnalyticsController::class, 'servicesPerformanceApi'])->name('servicesPerformanceApi');
    Route::get('/api/distributors-performance', [AnalyticsController::class, 'distributorsPerformanceApi'])->name('distributorsPerformanceApi');
    
    // تصدير التقارير
    Route::get('/export/financial', [AnalyticsController::class, 'exportFinancialReport'])->name('export-financial');
    Route::get('/export/sales', [AnalyticsController::class, 'exportSalesReport'])->name('export-sales');
    Route::get('/export/distributors', [AnalyticsController::class, 'exportDistributorsReport'])->name('export-distributors');
});
});