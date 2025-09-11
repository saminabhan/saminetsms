<?php
// app/Http/Controllers/ServiceController.php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('category')->orderBy('created_at', 'desc')->paginate(20);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        $categories = ServiceCategory::active()->orderBy('name_ar')->get();
        return view('services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'speed' => 'nullable|string|max:50',
            'duration_hours' => 'nullable|integer|min:1',
            'duration_days' => 'nullable|integer|min:1',
            'data_limit' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'allow_quantity' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['allow_quantity'] = $request->boolean('allow_quantity');

        Service::create($data);

        return redirect()->route('services.index')
                        ->with('success', 'تم إضافة الخدمة بنجاح');
    }

    public function show(Service $service)
    {
        $service->load('category');
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::active()->orderBy('name_ar')->get();
        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'speed' => 'nullable|string|max:50',
            'duration_hours' => 'nullable|integer|min:1',
            'duration_days' => 'nullable|integer|min:1',
            'data_limit' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'allow_quantity' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['allow_quantity'] = $request->boolean('allow_quantity');

        $service->update($data);

        return redirect()->route('services.show', $service)
                        ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    public function destroy(Service $service)
    {
        if ($service->invoices()->count() > 0) {
            return redirect()->route('services.index')
                           ->with('error', 'لا يمكن حذف الخدمة لأنها مرتبطة بفواتير');
        }

        $service->delete();

        return redirect()->route('services.index')
                        ->with('success', 'تم حذف الخدمة بنجاح');
    }

    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        return redirect()->route('services.index')
                        ->with('success', "تم {$status} الخدمة بنجاح");
    }
}