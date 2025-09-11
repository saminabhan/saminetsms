<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('type')->orderBy('name_ar')->paginate(20);
        return view('expense_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expense_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:operational,capital',
            'name' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        ExpenseCategory::create($data);
        return redirect()->route('expense-categories.index')->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function show(ExpenseCategory $expense_category)
    {
        return view('expense_categories.show', ['category' => $expense_category]);
    }

    public function edit(ExpenseCategory $expense_category)
    {
        return view('expense_categories.edit', ['category' => $expense_category]);
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $data = $request->validate([
            'type' => 'required|in:operational,capital',
            'name' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $expense_category->update($data);
        return redirect()->route('expense-categories.show', $expense_category)->with('success', 'تم تحديث الفئة');
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        if ($expense_category->expenses()->count() > 0) {
            return redirect()->route('expense-categories.index')->with('error', 'لا يمكن الحذف لوجود مصروفات مرتبطة');
        }
        $expense_category->delete();
        return redirect()->route('expense-categories.index')->with('success', 'تم حذف الفئة');
    }
}


