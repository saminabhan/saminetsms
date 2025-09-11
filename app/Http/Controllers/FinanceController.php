<?php
// app/Http/Controllers/FinanceController.php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Invoice;
use App\Models\SubscriberBalance;
use App\Models\Payment;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $invoiceQuery = Invoice::query();
        $paymentQuery = Payment::query();

        if ($dateFrom) {
            $invoiceQuery->whereDate('created_at', '>=', $dateFrom);
            $paymentQuery->whereDate('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invoiceQuery->whereDate('created_at', '<=', $dateTo);
            $paymentQuery->whereDate('paid_at', '<=', $dateTo);
        }

        // إحصائيات المالية
        $totalInvoices = (float) $invoiceQuery->clone()->sum('final_amount');
        $totalPaid = (float) $invoiceQuery->clone()->sum('paid_amount');
        $totalOutstanding = $totalInvoices - $totalPaid;
        $totalDebtors = SubscriberBalance::where('balance', '<', 0)->count();
        $totalCreditors = SubscriberBalance::where('balance', '>', 0)->count();

        // تدفقات نقدي/بنكي
        $cashIn = (float) $paymentQuery->clone()->where('method', 'cash')->sum('amount');
        $bankIn = (float) $paymentQuery->clone()->where('method', 'bank')->sum('amount');

        $withdrawalsQuery = Withdrawal::query();
        if ($dateFrom) { $withdrawalsQuery->whereDate('withdrawn_at', '>=', $dateFrom); }
        if ($dateTo) { $withdrawalsQuery->whereDate('withdrawn_at', '<=', $dateTo); }
        $cashOut = (float) $withdrawalsQuery->clone()->where('source', 'cash')->sum('amount');
        $bankOut = (float) $withdrawalsQuery->clone()->where('source', 'bank')->sum('amount');

        $totalCash = max(0, $cashIn - $cashOut);
        $totalBank = max(0, $bankIn - $bankOut);

        // إيراد الشهر الحالي
        $currentMonthRevenue = (float) Payment::whereBetween('paid_at', [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')])
            ->sum('amount');

        // آخر الفواتير
        $recentInvoices = Invoice::with(['subscriber', 'service'])
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        // المدينون
        $debtors = SubscriberBalance::with('subscriber')
                                   ->where('balance', '<', 0)
                                   ->orderBy('balance', 'asc')
                                   ->limit(5)
                                   ->get();

        return view('finance.index', compact(
            'totalInvoices', 'totalPaid', 'totalOutstanding',
            'totalDebtors', 'totalCreditors', 'recentInvoices', 'debtors',
            'totalCash', 'totalBank', 'currentMonthRevenue', 'dateFrom', 'dateTo'
        ));
    }

    public function debtors()
    {
        $debtors = SubscriberBalance::with('subscriber')
                                   ->where('balance', '<', 0)
                                   ->orderBy('balance', 'asc')
                                   ->paginate(20);

        return view('finance.debtors', compact('debtors'));
    }

    public function balances()
    {
        $balances = SubscriberBalance::with('subscriber')
                                    ->orderBy('balance', 'desc')
                                    ->paginate(20);

        return view('finance.balances', compact('balances'));
    }

    public function updateAllBalances()
    {
        $subscribers = Subscriber::all();
        
        foreach ($subscribers as $subscriber) {
            SubscriberBalance::updateOrCreateForSubscriber($subscriber->id);
        }

        return redirect()->route('finance.balances')
                        ->with('success', 'تم تحديث جميع الأرصدة بنجاح');
    }
}