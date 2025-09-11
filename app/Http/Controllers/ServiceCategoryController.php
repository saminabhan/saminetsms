<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::orderBy('name_ar')->paginate(20);
        return view('service_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('service_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        ServiceCategory::create($data);

        return redirect()->route('service-categories.index')
                         ->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function show(ServiceCategory $service_category)
    {
        return view('service_categories.show', ['category' => $service_category]);
    }

    public function edit(ServiceCategory $service_category)
    {
        return view('service_categories.edit', ['category' => $service_category]);
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $service_category->update($data);

        return redirect()->route('service-categories.show', $service_category)
                         ->with('success', 'تم تحديث الفئة بنجاح');
    }

    public function destroy(ServiceCategory $service_category)
    {
        if ($service_category->services()->count() > 0) {
            return redirect()->route('service-categories.index')
                             ->with('error', 'لا يمكن حذف الفئة لوجود خدمات مرتبطة بها');
        }

        $service_category->delete();

        return redirect()->route('service-categories.index')
                         ->with('success', 'تم حذف الفئة بنجاح');
    }
}


