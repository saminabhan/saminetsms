<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\ExpenseCategory;
use App\Models\Partner;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::orderBy('withdrawn_at', 'desc')->paginate(20);
        return view('withdrawals.index', compact('withdrawals'));
    }

    public function create()
    {
        $operational = ExpenseCategory::where('type', 'operational')->where('is_active', true)->orderBy('name_ar')->get();
        $capital = ExpenseCategory::where('type', 'capital')->where('is_active', true)->orderBy('name_ar')->get();
        $partners = Partner::where('is_active', true)->orderBy('name')->get();
        return view('withdrawals.create', compact('operational', 'capital', 'partners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_type' => 'required|in:operational,capital,partner',
            'category_id' => 'nullable|integer',
            'source' => 'required|in:cash,bank',
            'amount' => 'required|numeric|min:0.01',
            'withdrawn_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // تحقق من ارتباط category_id بنوعه
        if (in_array($data['category_type'], ['operational', 'capital'])) {
            $valid = ExpenseCategory::where('id', $data['category_id'])
                ->where('type', $data['category_type'])->exists();
            abort_unless($valid, 422, 'فئة غير صحيحة');
        } else {
            $valid = Partner::where('id', $data['category_id'])->exists();
            abort_unless($valid, 422, 'شريك غير صحيح');
        }

        $data['user_id'] = auth()->id();

        // تحقق الرصيد حسب المصدر (نقدي/بنكي)
        $totalPayments = \App\Models\Payment::where('method', $data['source'])->sum('amount');
        $totalWithdrawals = Withdrawal::where('source', $data['source'])->sum('amount');
        $available = $totalPayments - $totalWithdrawals;
        if ($available < $data['amount']) {
            return back()->withInput()->with('error', 'لا يوجد رصيد كافٍ في ' . ($data['source']==='cash'?'الصندوق النقدي':'الصندوق البنكي'));
        }

        Withdrawal::create($data);
        return redirect()->route('withdrawals.index')->with('success', 'تم إضافة السحب');
    }

    public function show(Withdrawal $withdrawal)
    {
        return view('withdrawals.show', compact('withdrawal'));
    }

    public function destroy(Withdrawal $withdrawal)
    {
        $withdrawal->delete();
        return redirect()->route('withdrawals.index')->with('success', 'تم حذف السحب');
    }
}


