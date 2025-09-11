<?php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Subscriber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SubscriberBalance;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['subscriber', 'service', 'user']);

        // فلترة
        if ($request->filled('subscriber_id')) {
            $query->where('subscriber_id', $request->subscriber_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);
        $subscribers = Subscriber::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'subscribers'));
    }

public function create()
{
    $subscribers = Subscriber::orderBy('name')->get();
    
    // جلب الفئات النشطة فقط
    $categories = ServiceCategory::where('is_active', 1)
                                ->orderBy('name')
                                ->get();
    
    // تجهيز بيانات الخدمات مجمعة حسب الفئة
    $servicesByCategory = [];
    
    // جلب جميع الخدمات النشطة مع فئاتها
    $services = Service::where('is_active', 1)
                      ->with('category')
                      ->get();
    
    // تجميع الخدمات حسب الفئة
    foreach ($services as $service) {
        if ($service->service_category_id) {
            $categoryId = $service->service_category_id;
            
            if (!isset($servicesByCategory[$categoryId])) {
                $servicesByCategory[$categoryId] = [];
            }
            
            $servicesByCategory[$categoryId][] = [
                'id' => $service->id,
                'name' => $service->name_ar ?? $service->name,
                'price' => (float) $service->price,
                'description' => $service->description,
                'allow_quantity' => (bool) ($service->allow_quantity ?? false)
            ];
        }
    }
    
    return view('invoices.create', compact('subscribers', 'categories', 'servicesByCategory'));
}

// إذا كنت تفضل استخدام AJAX، يمكنك الاحتفاظ بهذه الدالة أيضاً
public function getServicesByCategory(Request $request)
{
    $categoryId = $request->get('category_id');
    
    if (!$categoryId) {
        return response()->json(['services' => []]);
    }
    
    $services = Service::where('service_category_id', $categoryId)
                      ->where('is_active', 1)
                      ->orderBy('name')
                      ->get()
                      ->map(function($service) {
                          return [
                              'id' => $service->id,
                              'name' => $service->name_ar ?? $service->name,
                              'price' => (float) $service->price,
                              'description' => $service->description
                          ];
                      });
    
    return response()->json(['services' => $services]);
}

public function store(Request $request)
{
    $request->validate([
        'subscriber_id' => 'required|exists:subscribers,id',
        'service_id' => 'required|exists:services,id',
        'quantity' => 'nullable|integer|min:1',
        'service_start_date' => 'required|date',
        'discount_amount' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:1000'
    ]);

    $service = Service::findOrFail($request->service_id);
    $subscriber = Subscriber::findOrFail($request->subscriber_id);
    
    $quantity = (int) ($request->quantity ?? 1);
    if (!$service->allow_quantity) {
        $quantity = 1;
    }
    $originalPrice = (float) $service->price * max(1, $quantity);
    $discountAmount = (float) ($request->discount_amount ?? 0);
    $finalAmount = max(0, $originalPrice - $discountAmount);

    // حساب تاريخ انتهاء الخدمة
    $startDate = Carbon::parse($request->service_start_date);
    $endDate = $startDate->copy();

    if ($service->duration_hours) {
        $endDate->addHours($service->duration_hours);
    } elseif ($service->duration_days) {
        $endDate->addDays($service->duration_days);
    } else {
        $endDate->addDays(30); // افتراضي شهر واحد
    }

    try {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'subscriber_id' => $request->subscriber_id,
            'service_id' => $request->service_id,
            'quantity' => $quantity,
            'user_id' => auth()->id(),
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'service_start_date' => $request->service_start_date,
            'service_end_date' => $endDate->format('Y-m-d'),
            'notes' => $request->notes,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'paid_amount' => 0
        ]);

        // تحديث رصيد المشترك
        if (class_exists('App\Models\SubscriberBalance')) {
            SubscriberBalance::updateOrCreateForSubscriber($request->subscriber_id);
        }

        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'تم إنشاء الفاتورة بنجاح');
                        
    } catch (\Exception $e) {
        return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
    }
}

    public function show(Invoice $invoice)
    {
        $invoice->load(['subscriber', 'service.category', 'user', 'payments' => function($q){
            $q->orderBy('paid_at', 'desc');
        }]);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل فاتورة مدفوعة');
        }

        $subscribers = Subscriber::orderBy('name')->get();
        $categories = ServiceCategory::active()->with('activeServices')->get();

        return view('invoices.edit', compact('invoice', 'subscribers', 'categories'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل فاتورة مدفوعة');
        }

        $request->validate([
            'subscriber_id' => 'required|exists:subscribers,id',
            'service_id' => 'required|exists:services,id',
            'quantity' => 'nullable|integer|min:1',
            'service_start_date' => 'required|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $service = Service::findOrFail($request->service_id);
        $quantity = (int) ($request->quantity ?? 1);
        if (!$service->allow_quantity) {
            $quantity = 1;
        }
        $originalPrice = $service->price * max(1, $quantity);
        $discountAmount = $request->discount_amount ?? 0;
        $finalAmount = max(0, $originalPrice - $discountAmount);

        // حساب تاريخ انتهاء الخدمة
        $startDate = Carbon::parse($request->service_start_date);
        $endDate = $startDate->copy();

        if ($service->duration_hours) {
            $endDate->addHours($service->duration_hours);
        } elseif ($service->duration_days) {
            $endDate->addDays($service->duration_days);
        } else {
            $endDate->addDays(30);
        }

        $oldSubscriberId = $invoice->subscriber_id;

        $invoice->update([
            'subscriber_id' => $request->subscriber_id,
            'service_id' => $request->service_id,
            'quantity' => $quantity,
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'service_start_date' => $request->service_start_date,
            'service_end_date' => $endDate,
            'notes' => $request->notes
        ]);

        // تحديث رصيد المشترك
        SubscriberBalance::updateOrCreateForSubscriber($request->subscriber_id);
        if ($request->subscriber_id != $oldSubscriberId) {
            SubscriberBalance::updateOrCreateForSubscriber($oldSubscriberId);
        }

        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->paid_amount > 0) {
            return redirect()->route('invoices.index')
                           ->with('error', 'لا يمكن حذف فاتورة تم دفع جزء منها');
        }

        $subscriberId = $invoice->subscriber_id;
        $invoice->delete();

        // تحديث رصيد المشترك
        SubscriberBalance::updateOrCreateForSubscriber($subscriberId);

        return redirect()->route('invoices.index')
                        ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank'
        ]);

        $paymentAmount = $request->payment_amount;
        $remainingAmount = $invoice->final_amount - $invoice->paid_amount;

        if ($paymentAmount > $remainingAmount) {
            return back()->with('error', 'مبلغ الدفعة أكبر من المبلغ المتبقي');
        }

        $invoice->paid_amount += $paymentAmount;
        $invoice->updatePaymentStatus();
        $invoice->save();

        Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'method' => $request->payment_method,
            'amount' => $paymentAmount,
            'paid_at' => now()->format('Y-m-d')
        ]);

        // تحديث رصيد المشترك
        SubscriberBalance::updateOrCreateForSubscriber($invoice->subscriber_id);

        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'تم إضافة الدفعة بنجاح');
    }

    public function getServicePrice(Request $request)
    {
        $service = Service::find($request->service_id);
        
        if (!$service) {
            return response()->json(['error' => 'الخدمة غير موجودة'], 404);
        }

        return response()->json([
            'price' => $service->price,
            'name' => $service->full_description,
            'duration_hours' => $service->duration_hours,
            'duration_days' => $service->duration_days
        ]);
    }
}