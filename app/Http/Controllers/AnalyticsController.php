<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\Subscriber;
use App\Models\Distributor;
use App\Models\DistributorCard;
use App\Models\ExpenseCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\CashBox;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // الفلاتر
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // تحديد نطاق التاريخ
        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom);
            $end = Carbon::parse($dateTo);
        } elseif ($month) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        }

        // 1. الملخص المالي العام
        $financialSummary = $this->getFinancialSummary($start, $end);

        // 2. تحليل المصروفات حسب الفئات
        $expensesAnalysis = $this->getExpensesAnalysis($start, $end);

        // 3. أفضل المشتركين (أعلى إيرادًا)
        $topSubscribers = $this->getTopSubscribers($start, $end, 10);

        // 4. أفضل الموزعين (أعلى مبيعات)
        $topDistributors = $this->getTopDistributors($start, $end, 10);

        // 5. تحليل الخدمات الأكثر طلبًا
        $popularServices = $this->getPopularServices($start, $end);

        // 6. تحليل الحملات (خدمات حسب الفئة)
        $campaignAnalysis = $this->getCampaignAnalysis($start, $end);

        // 7. تحليل الخصومات
        $discountAnalysis = $this->getDiscountAnalysis($start, $end);

        // 8. تحليل طرق الدفع
        $paymentMethodsAnalysis = $this->getPaymentMethodsAnalysis($start, $end);

        // 9. الإيراد الشهري للسنة الحالية
        $monthlyRevenue = $this->getMonthlyRevenue($year);

        // 10. نمو الإيرادات (مقارنة بالفترة السابقة)
        $revenueGrowth = $this->getRevenueGrowth($start, $end);

        // 11. حالة المخزون (بطاقات الموزعين)
        $inventoryStatus = $this->getInventoryStatus();

        return view('analytics.index', compact(
            'start', 'end', 'financialSummary', 'expensesAnalysis',
            'topSubscribers', 'topDistributors', 'popularServices',
            'campaignAnalysis', 'discountAnalysis', 'paymentMethodsAnalysis',
            'monthlyRevenue', 'revenueGrowth', 'inventoryStatus', 'year'
        ));
    }

    private function getFinancialSummary($start, $end)
    {
        // الإيرادات
        $totalRevenue = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('final_amount');
        $totalPayments = (float) Payment::whereBetween('paid_at', [$start, $end])->sum('amount');
        
        // المصروفات
        $totalWithdrawals = (float) Withdrawal::whereBetween('withdrawn_at', [$start, $end])->sum('amount');
        $totalExpenses = (float) Expense::whereBetween('spent_at', [$start, $end])->sum('amount');
        
        // الخصومات
        $totalDiscounts = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('discount_amount');
        
        // الأرصدة
        $cashOpening = (float) CashBox::where('type', 'cash')->sum('opening_balance');
        $bankOpening = (float) CashBox::where('type', 'bank')->sum('opening_balance');
        
        $cashIn = (float) Payment::where('method', 'cash')->sum('amount');
        $bankIn = (float) Payment::where('method', 'bank')->sum('amount');
        $cashOut = (float) Withdrawal::where('source', 'cash')->sum('amount');
        $bankOut = (float) Withdrawal::where('source', 'bank')->sum('amount');
        
        $currentCash = max(0, $cashOpening + $cashIn - $cashOut);
        $currentBank = max(0, $bankOpening + $bankIn - $bankOut);

        return [
            'total_revenue' => $totalRevenue,
            'total_payments' => $totalPayments,
            'total_withdrawals' => $totalWithdrawals,
            'total_expenses' => $totalExpenses,
            'total_discounts' => $totalDiscounts,
            'net_profit' => $totalPayments - $totalWithdrawals - $totalExpenses,
            'current_cash' => $currentCash,
            'current_bank' => $currentBank,
            'outstanding_amount' => $totalRevenue - $totalPayments,
        ];
    }

    private function getExpensesAnalysis($start, $end)
    {
        // مصروفات تشغيلية
        $operationalExpenses = Expense::whereBetween('spent_at', [$start, $end])
            ->whereHas('category', fn($q) => $q->where('type', 'operational'))
            ->with('category')
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category->name_ar ?? $expense->category->name,
                    'amount' => (float) $expense->total,
                    'type' => 'تشغيلية'
                ];
            });

        // مصروفات رأسمالية
        $capitalExpenses = Expense::whereBetween('spent_at', [$start, $end])
            ->whereHas('category', fn($q) => $q->where('type', 'capital'))
            ->with('category')
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category->name_ar ?? $expense->category->name,
                    'amount' => (float) $expense->total,
                    'type' => 'رأسمالية'
                ];
            });

        // سحوبات الشركاء
        $partnerWithdrawals = Withdrawal::whereBetween('withdrawn_at', [$start, $end])
            ->where('category_type', 'partner')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->map(function($withdrawal) {
                $partner = \App\Models\Partner::find($withdrawal->category_id);
                return [
                    'category' => $partner ? $partner->name : 'شريك غير محدد',
                    'amount' => (float) $withdrawal->total,
                    'type' => 'سحب شريك'
                ];
            });

        return [
            'operational' => $operationalExpenses,
            'capital' => $capitalExpenses,
            'partners' => $partnerWithdrawals,
            'total_operational' => $operationalExpenses->sum('amount'),
            'total_capital' => $capitalExpenses->sum('amount'),
            'total_partners' => $partnerWithdrawals->sum('amount'),
        ];
    }

    private function getTopSubscribers($start, $end, $limit = 10)
    {
        return Payment::selectRaw('invoices.subscriber_id, SUM(payments.amount) as total_paid, COUNT(payments.id) as payment_count')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('paid_at', [$start, $end])
            ->whereNotNull('invoices.subscriber_id')
            ->groupBy('invoices.subscriber_id')
            ->orderByDesc('total_paid')
            ->limit($limit)
            ->get()
            ->map(function($row) {
                $subscriber = Subscriber::find($row->subscriber_id);
                return [
                    'subscriber' => $subscriber,
                    'total_paid' => (float) $row->total_paid,
                    'payment_count' => (int) $row->payment_count,
                ];
            });
    }

    private function getTopDistributors($start, $end, $limit = 10)
    {
        return Payment::selectRaw('invoices.distributor_id, SUM(payments.amount) as total_sales, SUM(invoices.quantity) as total_cards')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('paid_at', [$start, $end])
            ->whereNotNull('invoices.distributor_id')
            ->groupBy('invoices.distributor_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->map(function($row) {
                $distributor = Distributor::find($row->distributor_id);
                return [
                    'distributor' => $distributor,
                    'total_sales' => (float) $row->total_sales,
                    'total_cards' => (int) $row->total_cards,
                ];
            });
    }

    private function getPopularServices($start, $end)
    {
        return Invoice::selectRaw('service_id, COUNT(*) as invoice_count, SUM(quantity) as total_quantity, SUM(final_amount) as total_revenue')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function($row) {
                $service = Service::find($row->service_id);
                return [
                    'service' => $service,
                    'invoice_count' => (int) $row->invoice_count,
                    'total_quantity' => (int) $row->total_quantity,
                    'total_revenue' => (float) $row->total_revenue,
                ];
            });
    }

    private function getCampaignAnalysis($start, $end)
    {
        return ServiceCategory::withCount(['services as revenue' => function($q) use($start, $end) {
                $q->selectRaw('COALESCE(SUM(invoices.final_amount), 0)')
                  ->join('invoices', 'invoices.service_id', '=', 'services.id')
                  ->whereBetween('invoices.created_at', [$start, $end]);
            }])
            ->withCount(['services as invoice_count' => function($q) use($start, $end) {
                $q->selectRaw('COUNT(invoices.id)')
                  ->join('invoices', 'invoices.service_id', '=', 'services.id')
                  ->whereBetween('invoices.created_at', [$start, $end]);
            }])
            ->having('revenue', '>', 0)
            ->orderByDesc('revenue')
            ->get()
            ->map(function($category) {
                return [
                    'category' => $category->name_ar ?? $category->name,
                    'revenue' => (float) $category->revenue,
                    'invoice_count' => (int) $category->invoice_count,
                ];
            });
    }

    private function getDiscountAnalysis($start, $end)
    {
        $totalDiscounts = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('discount_amount');
        $discountCount = Invoice::whereBetween('created_at', [$start, $end])
                               ->where('discount_amount', '>', 0)
                               ->count();
        $avgDiscount = $discountCount > 0 ? $totalDiscounts / $discountCount : 0;

        // أكبر الخصومات
        $biggestDiscounts = Invoice::with(['subscriber', 'service'])
            ->whereBetween('created_at', [$start, $end])
            ->where('discount_amount', '>', 0)
            ->orderByDesc('discount_amount')
            ->limit(10)
            ->get();

        return [
            'total_discounts' => $totalDiscounts,
            'discount_count' => $discountCount,
            'average_discount' => $avgDiscount,
            'biggest_discounts' => $biggestDiscounts,
        ];
    }

    private function getPaymentMethodsAnalysis($start, $end)
    {
        return Payment::selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('method')
            ->get()
            ->map(function($row) {
                return [
                    'method' => $row->method === 'cash' ? 'نقدي' : 'بنكي',
                    'count' => (int) $row->count,
                    'total' => (float) $row->total,
                ];
            });
    }

    private function getMonthlyRevenue($year)
    {
        $monthlyData = Invoice::selectRaw('MONTH(created_at) as month, SUM(final_amount) as revenue, COUNT(*) as invoice_count')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('month');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'month' => $m,
                'revenue' => isset($monthlyData[$m]) ? (float) $monthlyData[$m]->revenue : 0,
                'invoice_count' => isset($monthlyData[$m]) ? (int) $monthlyData[$m]->invoice_count : 0,
            ];
        }

        return $result;
    }

    private function getRevenueGrowth($start, $end)
    {
        $currentRevenue = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('final_amount');
        
        // حساب الفترة السابقة
        $duration = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($duration + 1);
        $prevEnd = $start->copy()->subDay();
        
        $previousRevenue = (float) Invoice::whereBetween('created_at', [$prevStart, $prevEnd])->sum('final_amount');
        
        $growthPercentage = $previousRevenue > 0 
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 
            : null;

        return [
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'growth_percentage' => $growthPercentage,
            'growth_amount' => $currentRevenue - $previousRevenue,
        ];
    }

   private function getInventoryStatus()
{
    $inventoryStatus = DistributorCard::selectRaw('
            distributor_id,
            SUM(quantity_received) as total_cards,
            SUM(quantity_available) as available_cards,
            SUM(quantity_sold) as sold_cards,
            SUM(quantity_received * card_price) as total_value,
            SUM(quantity_available * card_price) as available_value
        ')
        ->groupBy('distributor_id')
        ->get()
        ->map(function ($row) {
            $distributor = Distributor::find($row->distributor_id);
            return [
                'distributor'     => $distributor,
                'total_cards'     => (int) $row->total_cards,
                'available_cards' => (int) $row->available_cards,
                'sold_cards'      => (int) $row->sold_cards,
                'total_value'     => (float) $row->total_value,
                'available_value' => (float) $row->available_value,
            ];
        });

    return $inventoryStatus;
}


    // API endpoints للرسوم البيانية
    public function monthlyRevenueApi($year = null)
    {
        $year = $year ?: now()->year;
        $monthlyRevenue = $this->getMonthlyRevenue($year);
        
        return response()->json([
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
            'revenue' => array_column($monthlyRevenue, 'revenue'),
            'invoices' => array_column($monthlyRevenue, 'invoice_count')
        ]);
    }

    public function expensesChartApi(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());
        
        $expensesAnalysis = $this->getExpensesAnalysis($start, $end);
        
        return response()->json([
            'operational' => $expensesAnalysis['operational'],
            'capital' => $expensesAnalysis['capital'],
            'partners' => $expensesAnalysis['partners'],
        ]);
    }

    // التقارير المالية التفصيلية
    public function financialReports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);

        // الملخص العام
        $summary = [
            'total_invoices' => (float) Invoice::whereBetween('created_at', [$start, $end])->sum('final_amount'),
            'total_payments' => (float) Payment::whereBetween('paid_at', [$start, $end])->sum('amount'),
            'total_expenses' => (float) Expense::whereBetween('spent_at', [$start, $end])->sum('amount') + 
                               (float) Withdrawal::whereBetween('withdrawn_at', [$start, $end])->sum('amount'),
            'total_discounts' => (float) Invoice::whereBetween('created_at', [$start, $end])->sum('discount_amount'),
        ];
        $summary['outstanding'] = $summary['total_invoices'] - $summary['total_payments'];
        $summary['net_profit'] = $summary['total_payments'] - $summary['total_expenses'];

        // تفصيل المصروفات
        $expenseBreakdown = $this->getDetailedExpenseBreakdown($start, $end);

        // أكبر المدفوعات
        $biggestPayments = Payment::with(['invoice.subscriber', 'invoice.distributor'])
            ->whereBetween('paid_at', [$start, $end])
            ->orderByDesc('amount')
            ->limit(10)
            ->get();

        // أكبر الخصومات
        $biggestDiscounts = Invoice::with(['subscriber', 'distributor'])
            ->whereBetween('created_at', [$start, $end])
            ->where('discount_amount', '>', 0)
            ->orderByDesc('discount_amount')
            ->limit(10)
            ->get();

        // حركة الصناديق
        $cashFlow = $this->getCashFlowAnalysis($start, $end);

        // الفواتير المعلقة
        $outstandingInvoices = Invoice::with(['subscriber', 'distributor'])
            ->where('payment_status', '!=', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('final_amount')
            ->limit(20)
            ->get();

        return view('analytics.financial', compact(
            'summary', 'expenseBreakdown', 'biggestPayments', 
            'biggestDiscounts', 'cashFlow', 'outstandingInvoices'
        ));
    }

    private function getDetailedExpenseBreakdown($start, $end)
    {
        $expenses = collect();
        
        // المصروفات المباشرة
        $directExpenses = Expense::with('category')
            ->whereBetween('spent_at', [$start, $end])
            ->get()
            ->groupBy('category.type');
        
        $totalAmount = 0;
        
        foreach(['operational', 'capital'] as $type) {
            if (isset($directExpenses[$type])) {
                foreach($directExpenses[$type]->groupBy('expense_category_id') as $categoryId => $categoryExpenses) {
                    $category = $categoryExpenses->first()->category;
                    $amount = $categoryExpenses->sum('amount');
                    $totalAmount += $amount;
                    
                    $expenses->push([
                        'type_name' => $type === 'operational' ? 'تشغيلية' : 'رأسمالية',
                        'type_color' => $type === 'operational' ? 'danger' : 'warning',
                        'category' => $category->name_ar ?? $category->name,
                        'amount' => (float) $amount,
                        'count' => $categoryExpenses->count(),
                        'percentage' => 0 // سيتم حسابها لاحقاً
                    ]);
                }
            }
        }

        // السحوبات
        $withdrawals = Withdrawal::whereBetween('withdrawn_at', [$start, $end])
            ->where('category_type', 'partner')
            ->get()
            ->groupBy('category_id');
        
        foreach($withdrawals as $partnerId => $partnerWithdrawals) {
            $partner = \App\Models\Partner::find($partnerId);
            $amount = $partnerWithdrawals->sum('amount');
            $totalAmount += $amount;
            
            $expenses->push([
                'type_name' => 'سحب شريك',
                'type_color' => 'info',
                'category' => $partner ? $partner->name : 'شريك غير محدد',
                'amount' => (float) $amount,
                'count' => $partnerWithdrawals->count(),
                'percentage' => 0 // سيتم حسابها لاحقاً
            ]);
        }

        // حساب النسب المئوية
        $expenses = $expenses->map(function($expense) use ($totalAmount) {
            $expense['percentage'] = $totalAmount > 0 ? ($expense['amount'] / $totalAmount) * 100 : 0;
            return $expense;
        })->sortByDesc('amount');

        return $expenses;
    }

    private function getCashFlowAnalysis($start, $end)
    {
        $cashOpening = (float) CashBox::where('type', 'cash')->sum('opening_balance');
        $bankOpening = (float) CashBox::where('type', 'bank')->sum('opening_balance');
        
        $cashIn = (float) Payment::where('method', 'cash')->sum('amount');
        $bankIn = (float) Payment::where('method', 'bank')->sum('amount');
        
        $cashOut = (float) Withdrawal::where('source', 'cash')->sum('amount');
        $bankOut = (float) Withdrawal::where('source', 'bank')->sum('amount');
        
        return [
            'opening_balance' => $cashOpening,
            'bank_opening_balance' => $bankOpening,
            'cash_in' => $cashIn,
            'bank_in' => $bankIn,
            'cash_out' => $cashOut,
            'bank_out' => $bankOut,
            'current_cash' => max(0, $cashOpening + $cashIn - $cashOut),
            'current_bank' => max(0, $bankOpening + $bankIn - $bankOut),
        ];
    }

    // تقرير المبيعات والعملاء
    public function salesReports(Request $request)
    {
        $start = Carbon::parse($request->get('date_from', now()->startOfMonth()->format('Y-m-d')));
        $end = Carbon::parse($request->get('date_to', now()->format('Y-m-d')));

        // إحصائيات المبيعات
        $salesStats = [
            'total_invoices_count' => Invoice::whereBetween('created_at', [$start, $end])->count(),
            'subscriber_invoices' => Invoice::where('client_type', 'subscriber')->whereBetween('created_at', [$start, $end])->count(),
            'distributor_invoices' => Invoice::where('client_type', 'distributor')->whereBetween('created_at', [$start, $end])->count(),
            'avg_invoice_value' => Invoice::whereBetween('created_at', [$start, $end])->avg('final_amount') ?? 0,
        ];

        // أفضل العملاء
        $topCustomers = $this->getTopSubscribers($start, $end, 20);
        
        // تحليل الخدمات
        $serviceAnalysis = $this->getPopularServices($start, $end);
        
        // معدل التحصيل
        $collectionRate = $this->getCollectionRate($start, $end);

        return view('analytics.sales', compact('salesStats', 'topCustomers', 'serviceAnalysis', 'collectionRate'));
    }

    private function getCollectionRate($start, $end)
    {
        $totalInvoices = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('final_amount');
        $totalCollected = (float) Payment::join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('invoices.created_at', [$start, $end])
            ->sum('payments.amount');
        
        return [
            'total_invoiced' => $totalInvoices,
            'total_collected' => $totalCollected,
            'collection_rate' => $totalInvoices > 0 ? ($totalCollected / $totalInvoices) * 100 : 0,
            'outstanding' => $totalInvoices - $totalCollected,
        ];
    }

    // تصدير التقارير (مثال)
    public function exportFinancialReport(Request $request)
    {
        // يمكنك استخدام Laravel Excel أو أي حزمة أخرى للتصدير
        return response()->json(['message' => 'سيتم إضافة وظيفة التصدير قريباً']);
    }

       public function distributorsReport(Request $request)
{
    $start = Carbon::parse($request->get('date_from', now()->startOfMonth()->format('Y-m-d')));
    $end = Carbon::parse($request->get('date_to', now()->format('Y-m-d')));

    // إحصائيات عامة عن الموزعين
    $distributorsStats = [
        'total_distributors' => Distributor::count(),
        'active_distributors' => Distributor::whereHas('invoices', function($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->count(),
        'total_cards_distributed' => DistributorCard::sum('quantity_received'),
        'total_cards_sold' => DistributorCard::sum('quantity_sold'),
        'total_inventory_value' => DistributorCard::sum(DB::raw('quantity_available * card_price')),
    ];

    // أفضل الموزعين بناءً على المبيعات
    $distributorPerformance = Distributor::select([
            'distributors.id',
            DB::raw('MAX(distributors.name) as name'),
            DB::raw('MAX(distributors.phone) as phone'),
            DB::raw('MAX(distributors.email) as email'),
            DB::raw('MAX(distributors.address) as address'),
            DB::raw('MAX(distributors.type) as type'),
            DB::raw('MAX(distributors.is_active) as is_active'),
            DB::raw('COALESCE(SUM(invoices.final_amount), 0) as total_sales'),
            DB::raw('COUNT(invoices.id) as invoice_count'),
            DB::raw('COALESCE(AVG(invoices.final_amount), 0) as avg_invoice_value')
        ])
        ->leftJoin('invoices', 'distributors.id', '=', 'invoices.distributor_id')
        ->whereBetween('invoices.created_at', [$start, $end])
        ->groupBy('distributors.id')
        ->orderByDesc('total_sales')
        ->get();

    // أفضل الموزعين (يمكنك أخذ أعلى 20)
    $topDistributors = $distributorPerformance->take(20);

    // حالة المخزون التفصيلية لكل موزع
    $inventoryDetails = DistributorCard::selectRaw('
            distributor_id,
            service_id,
            SUM(quantity_received) as total_received,
            SUM(quantity_available) as total_available,
            SUM(quantity_sold) as total_sold,
            SUM(quantity_received * card_price) as total_investment,
            SUM(quantity_available * card_price) as available_value,
            SUM(quantity_sold * card_price) as sold_value
        ')
        ->groupBy('distributor_id', 'service_id')
        ->get()
        ->map(function ($row) {
            $distributor = Distributor::find($row->distributor_id);
            return [
                'distributor' => $distributor,
                'service_id' => $row->service_id,
                'total_received' => (int) $row->total_received,
                'total_available' => (int) $row->total_available,
                'total_sold' => (int) $row->total_sold,
                'total_investment' => (float) $row->total_investment,
                'available_value' => (float) $row->available_value,
                'sold_value' => (float) $row->sold_value,
                'sale_rate' => $row->total_received > 0 ? ($row->total_sold / $row->total_received) * 100 : 0,
            ];
        });

    // الموزعين الذين يحتاجون متابعة (مخزون منخفض أو عدم نشاط)
    $distributorsNeedingAttention = $distributorPerformance->filter(function ($d) use ($inventoryDetails) {
        $inventory = $inventoryDetails->firstWhere('distributor.id', $d->id);
        return !$d->is_active || ($inventory && $inventory['total_available'] < 50); // مثال شرط منخفض
    });

    return view('analytics.distributors', compact(
        'distributorsStats',
        'topDistributors',
        'inventoryDetails',
        'distributorPerformance',
        'distributorsNeedingAttention',
        'start',
        'end'
    ));
}


    // تقرير الخدمات والحملات
public function servicesReport(Request $request)
{
    $start = Carbon::parse($request->get('date_from', now()->startOfMonth()->format('Y-m-d')));
    $end = Carbon::parse($request->get('date_to', now()->format('Y-m-d')));

    // إحصائيات عامة عن الخدمات
    $servicesStats = [
        'total_services' => Service::count(),
        'active_services' => Service::whereHas('invoices', function($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->count(),
        'total_service_revenue' => Invoice::whereBetween('created_at', [$start, $end])->sum('final_amount'),
        'avg_service_price' => Service::avg('price') ?? 0,
    ];

    // أداء الخدمات بالتفصيل (يشمل كل الخدمات حتى التي بدون فواتير)
    $servicesPerformance = Service::select([
        'services.id',
        'services.service_category_id',
        'services.name',
        'services.name_ar',
        'services.price',
        DB::raw('COALESCE(SUM(invoices.final_amount), 0) as total_revenue'),
        DB::raw('COUNT(invoices.id) as invoice_count'),
        DB::raw('COALESCE(SUM(invoices.quantity), 0) as total_quantity'),
        DB::raw('COALESCE(AVG(invoices.final_amount), 0) as avg_invoice_value')
    ])
    ->leftJoin('invoices', function($join) use ($start, $end) {
        $join->on('services.id', '=', 'invoices.service_id')
             ->whereBetween('invoices.created_at', [$start, $end]);
    })
    ->groupBy('services.id', 'services.service_category_id', 'services.name', 'services.name_ar', 'services.price')
    ->orderByDesc('total_revenue')
    ->get();

    // تحليل فئات الخدمات
    $categoriesAnalysis = $this->getServiceCategoriesAnalysis($start, $end);

    // الخدمات الأكثر ربحية
    $mostProfitableServices = $this->getMostProfitableServices($start, $end);

    // اتجاهات الخدمات (مقارنة بالفترات السابقة)
    $servicesTrends = $this->getServicesTrends($start, $end);

    return view('analytics.services', compact(
        'servicesStats', 'servicesPerformance', 'categoriesAnalysis',
        'mostProfitableServices', 'servicesTrends', 'start', 'end'
    ));
}

    // API للرسوم البيانية - أداء الخدمات
    public function servicesPerformanceApi(Request $request)
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', now()));

        $servicesData = Invoice::selectRaw('service_id, SUM(final_amount) as revenue, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->with('service')
            ->get();

        return response()->json([
            'labels' => $servicesData->map(fn($item) => $item->service->name_ar ?? $item->service->name ?? 'غير محدد'),
            'revenue' => $servicesData->pluck('revenue'),
            'count' => $servicesData->pluck('count')
        ]);
    }

    // API للرسوم البيانية - أداء الموزعين
    public function distributorsPerformanceApi(Request $request)
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', now()));

        $distributorsData = $this->getTopDistributors($start, $end, 10);

        return response()->json([
            'labels' => $distributorsData->map(fn($item) => $item['distributor']->name ?? 'غير محدد'),
            'sales' => $distributorsData->pluck('total_sales'),
            'cards' => $distributorsData->pluck('total_cards')
        ]);
    }

    // تصدير تقرير المبيعات
    public function exportSalesReport(Request $request)
    {
        $start = Carbon::parse($request->get('date_from', now()->startOfMonth()));
        $end = Carbon::parse($request->get('date_to', now()));

        // جمع بيانات التقرير
        $salesData = [
            'period' => $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d'),
            'summary' => $this->getFinancialSummary($start, $end),
            'top_subscribers' => $this->getTopSubscribers($start, $end, 50),
            'popular_services' => $this->getPopularServices($start, $end),
            'collection_rate' => $this->getCollectionRate($start, $end),
        ];

        // يمكن هنا استخدام Laravel Excel أو PDF generator
        return response()->json([
            'message' => 'تم تحضير بيانات تقرير المبيعات',
            'data' => $salesData
        ]);
    }

    // تصدير تقرير الموزعين
    public function exportDistributorsReport(Request $request)
    {
        $start = Carbon::parse($request->get('date_from', now()->startOfMonth()));
        $end = Carbon::parse($request->get('date_to', now()));

        $distributorsData = [
            'period' => $start->format('Y-m-d') . ' إلى ' . $end->format('Y-m-d'),
            'top_distributors' => $this->getTopDistributors($start, $end, 100),
            'inventory_status' => $this->getInventoryStatus(),
            'performance_analysis' => $this->getDistributorPerformanceAnalysis($start, $end),
        ];

        return response()->json([
            'message' => 'تم تحضير بيانات تقرير الموزعين',
            'data' => $distributorsData
        ]);
    }

    // وظائف مساعدة إضافية

private function getDetailedInventoryStatus()
{
    return DistributorCard::with(['distributor', 'service'])
        ->selectRaw('
            distributor_id,
            service_id,
            SUM(quantity_received) as total_received,
            SUM(quantity_available) as total_available,
            SUM(quantity_sold) as total_sold,
            SUM(quantity_received * card_price) as total_investment,
            SUM(quantity_available * card_price) as available_value,
            SUM(quantity_sold * card_price) as sold_value
        ')
        ->groupBy(['distributor_id', 'service_id'])
        ->get()
        ->map(function ($row) {
            return [
                'distributor' => $row->distributor ?? (object) ['name' => 'غير محدد', 'phone' => ''],
                'service' => $row->service ?? (object) ['name' => 'غير محدد'],
                'total_received' => (int) $row->total_received,
                'total_available' => (int) $row->total_available,
                'total_sold' => (int) $row->total_sold,
                'total_investment' => (float) $row->total_investment,
                'available_value' => (float) $row->available_value,
                'sold_value' => (float) $row->sold_value,
                'sale_rate' => $row->total_received > 0 ? ($row->total_sold / $row->total_received) * 100 : 0,
            ];
        });
}

    private function getDistributorPerformanceAnalysis($start, $end)
    {
        return Distributor::selectRaw('
                distributors.*,
                COALESCE(SUM(invoices.final_amount), 0) as total_sales,
                COUNT(invoices.id) as invoice_count,
                COALESCE(AVG(invoices.final_amount), 0) as avg_invoice_value
            ')
            ->leftJoin('invoices', 'distributors.id', '=', 'invoices.distributor_id')
            ->whereBetween('invoices.created_at', [$start, $end])
            ->groupBy('distributors.id')
            ->orderByDesc('total_sales')
            ->get()
            ->map(function($distributor) {
                return [
                    'distributor' => $distributor,
                    'total_sales' => (float) $distributor->total_sales,
                    'invoice_count' => (int) $distributor->invoice_count,
                    'avg_invoice_value' => (float) $distributor->avg_invoice_value,
                ];
            });
    }

    private function getDistributorsNeedingAttention($start, $end)
    {
        // الموزعين بدون نشاط
        $inactiveDistributors = Distributor::whereDoesntHave('invoices', function($q) use($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->get();

        // الموزعين بمخزون منخفض
        $lowStockDistributors = DistributorCard::selectRaw('distributor_id, SUM(quantity_available) as total_available')
            ->groupBy('distributor_id')
            ->having('total_available', '<', 10)
            ->with('distributor')
            ->get();

        return [
            'inactive' => $inactiveDistributors,
            'low_stock' => $lowStockDistributors,
        ];
    }

    private function getServicesPerformanceDetailed($start, $end)
    {
        return Service::selectRaw('
                services.*,
                COALESCE(SUM(invoices.final_amount), 0) as total_revenue,
                COUNT(invoices.id) as invoice_count,
                COALESCE(SUM(invoices.quantity), 0) as total_quantity,
                COALESCE(AVG(invoices.final_amount), 0) as avg_invoice_value
            ')
            ->leftJoin('invoices', 'services.id', '=', 'invoices.service_id')
            ->whereBetween('invoices.created_at', [$start, $end])
            ->groupBy('services.id')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function($service) {
                return [
                    'service' => $service,
                    'total_revenue' => (float) $service->total_revenue,
                    'invoice_count' => (int) $service->invoice_count,
                    'total_quantity' => (int) $service->total_quantity,
                    'avg_invoice_value' => (float) $service->avg_invoice_value,
                ];
            });
    }

    private function getServiceCategoriesAnalysis($start, $end)
    {
        return ServiceCategory::withCount(['services as total_revenue' => function($q) use($start, $end) {
                $q->selectRaw('COALESCE(SUM(invoices.final_amount), 0)')
                  ->join('invoices', 'invoices.service_id', '=', 'services.id')
                  ->whereBetween('invoices.created_at', [$start, $end]);
            }])
            ->withCount(['services as total_invoices' => function($q) use($start, $end) {
                $q->selectRaw('COUNT(invoices.id)')
                  ->join('invoices', 'invoices.service_id', '=', 'services.id')
                  ->whereBetween('invoices.created_at', [$start, $end]);
            }])
            ->withCount(['services as active_services' => function($q) use($start, $end) {
                $q->selectRaw('COUNT(DISTINCT services.id)')
                  ->join('invoices', 'invoices.service_id', '=', 'services.id')
                  ->whereBetween('invoices.created_at', [$start, $end]);
            }])
            ->orderByDesc('total_revenue')
            ->get();
    }

    private function getMostProfitableServices($start, $end)
    {
        return Invoice::selectRaw('
                service_id,
                SUM(final_amount - discount_amount) as net_revenue,
                SUM(final_amount) as gross_revenue,
                SUM(discount_amount) as total_discounts,
                COUNT(*) as invoice_count,
                AVG(final_amount) as avg_value
            ')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('net_revenue')
            ->limit(15)
            ->get()
            ->map(function($row) {
                $service = Service::find($row->service_id);
                return [
                    'service' => $service,
                    'net_revenue' => (float) $row->net_revenue,
                    'gross_revenue' => (float) $row->gross_revenue,
                    'total_discounts' => (float) $row->total_discounts,
                    'invoice_count' => (int) $row->invoice_count,
                    'avg_value' => (float) $row->avg_value,
                    'profit_margin' => $row->gross_revenue > 0 ? ($row->net_revenue / $row->gross_revenue) * 100 : 0,
                ];
            });
    }

    private function getServicesTrends($start, $end)
    {
        // مقارنة بالفترة السابقة
        $duration = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($duration + 1);
        $prevEnd = $start->copy()->subDay();

        $currentServices = Invoice::selectRaw('service_id, SUM(final_amount) as revenue')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('service_id')
            ->pluck('revenue', 'service_id');

        $previousServices = Invoice::selectRaw('service_id, SUM(final_amount) as revenue')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->groupBy('service_id')
            ->pluck('revenue', 'service_id');

        $trends = [];
        foreach($currentServices as $serviceId => $currentRevenue) {
            $previousRevenue = $previousServices->get($serviceId, 0);
            $growthRate = $previousRevenue > 0 
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 
                : null;

            $service = Service::find($serviceId);
            $trends[] = [
                'service' => $service,
                'current_revenue' => (float) $currentRevenue,
                'previous_revenue' => (float) $previousRevenue,
                'growth_rate' => $growthRate,
                'growth_amount' => $currentRevenue - $previousRevenue,
            ];
        }

        return collect($trends)->sortByDesc('growth_rate');
    }
    
}