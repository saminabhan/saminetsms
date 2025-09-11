<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user'])->orderBy('spent_at', 'desc');
        if ($request->filled('type')) {
            $query->whereHas('category', fn($q) => $q->where('type', $request->type));
        }
        $expenses = $query->paginate(20);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('type')->orderBy('name_ar')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
            'payment_method' => 'nullable|in:cash,bank,other',
            'notes' => 'nullable|string|max:500',
        ]);
        $data['user_id'] = auth()->id();
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'تم إضافة المصروف');
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'user']);
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('type')->orderBy('name_ar')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);
        $expense->update($data);
        return redirect()->route('expenses.show', $expense)->with('success', 'تم تحديث المصروف');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف');
    }
}


