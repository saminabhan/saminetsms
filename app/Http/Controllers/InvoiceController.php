<?php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Subscriber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SubscriberBalance;
use App\Models\Payment;
use App\Models\Distributor;
use App\Models\DistributorCard;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['subscriber', 'distributor', 'distributorCard.service', 'service', 'user']);

        // فلترة حسب نوع العميل
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        // فلترة حسب المشترك
        if ($request->filled('subscriber_id')) {
            $query->where('subscriber_id', $request->subscriber_id);
        }

        // فلترة حسب الموزع
        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->distributor_id);
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
        $distributors = Distributor::active()->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'subscribers', 'distributors'));
    }

    public function create()
    {
        $subscribers = Subscriber::orderBy('name')->get();
        $distributors = Distributor::active()->orderBy('name')->get();
        
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
        
        return view('invoices.create', compact('subscribers', 'distributors', 'categories', 'servicesByCategory'));
    }

public function getDistributorCards(Request $request)
{
    $distributorId = $request->distributor_id;

    if (!$distributorId) {
        return response()->json(['error' => 'الموزع غير محدد'], 400);
    }

    $cards = DistributorCard::with('service')
        ->where('distributor_id', $distributorId)
        ->where('quantity_available', '>', 0)
        ->get()
        ->map(fn($card) => [
            'id' => $card->id,
            'service_name' => $card->service?->name ?? 'غير محدد',
            'quantity_available' => $card->quantity_available,
            'card_price' => $card->card_price,
            'remaining_amount' => $card->total_amount - $card->paid_amount,
        ]);

    return response()->json(['cards' => $cards]);
}



public function store(Request $request)
    {
        // التحقق من نوع العميل
        $clientType = $request->client_type;
        
        if ($clientType === 'subscriber') {
            return $this->storeSubscriberInvoice($request);
        } elseif ($clientType === 'distributor') {
            return $this->storeDistributorInvoice($request);
        }
        
        return redirect()->back()->with('error', 'نوع العميل غير صحيح');
    }

   private function storeDistributorInvoice(Request $request)
{
    // قواعد التحقق
    $validationRules = [
        'distributor_id' => 'required|exists:distributors,id',
        'distributor_card_id' => 'required|exists:distributor_cards,id',
        'quantity_distributor' => 'required|integer|min:1',
        'notes_distributor' => 'nullable|string|max:1000'
    ];

    $request->validate($validationRules);

    $distributorCard = DistributorCard::with(['distributor', 'service'])
                                     ->findOrFail($request->distributor_card_id);

    // التأكد من أن الكرت ينتمي للموزع
    if ($distributorCard->distributor_id != $request->distributor_id) {
        return redirect()->back()
                       ->withInput()
                       ->with('error', 'الكرت المحدد لا ينتمي للموزع المحدد');
    }

    $quantity = (int) $request->quantity_distributor;

    if ($quantity > $distributorCard->quantity_available) {
        return redirect()->back()
                       ->withInput()
                       ->with('error', "الكمية المطلوبة ({$quantity}) أكبر من الكمية المتاحة ({$distributorCard->quantity_available})");
    }

    if ($quantity <= 0) {
        return redirect()->back()
                       ->withInput()
                       ->with('error', 'يجب أن تكون الكمية أكبر من صفر');
    }

    $unitPrice = (float) $distributorCard->card_price;
    $totalAmount = $unitPrice * $quantity;

    try {
        DB::beginTransaction();

      $invoice = Invoice::create([
    'invoice_number' => Invoice::generateInvoiceNumber(),
    'client_type' => 'distributor',       // نوع العميل موزع
    'subscriber_id' => null,              // مهم: NULL للموزع
    'distributor_id' => $request->distributor_id,
    'distributor_card_id' => $request->distributor_card_id,
    'service_id' => $distributorCard->service_id,
    'quantity' => $quantity,
    'user_id' => auth()->id(),
    'original_price' => $totalAmount,
    'discount_amount' => 0,
    'final_amount' => $totalAmount,
    'notes' => $request->notes_distributor,
    'status' => 'pending',                // غير مدفوعة
    'payment_status' => 'unpaid',         // غير مدفوعة
    'paid_amount' => 0,                   // مبلغ مدفوع صفر
    'service_start_date' => now()->format('Y-m-d'),
    'service_end_date' => now()->addDays(30)->format('Y-m-d'),
]);


        // تحديث كمية الكروت
        $distributorCard->quantity_sold += $quantity;
        $distributorCard->quantity_available -= $quantity;
        if ($distributorCard->quantity_available < 0) {
            $distributorCard->quantity_available = 0;
        }
        $distributorCard->save();

       
        DB::commit();

        return redirect()->route('invoices.show', $invoice)
                        ->with('success', "تم إنشاء فاتورة الموزع بنجاح. تم بيع {$quantity} كرت بقيمة {$totalAmount} ش.ج");

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error creating distributor invoice: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'error' => $e->getTraceAsString()
        ]);

        return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
    }
}


    private function storeSubscriberInvoice(Request $request)
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
            $endDate->addDays(30);
        }

        try {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'client_type' => 'subscriber',
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
   $invoice->load([
    'subscriber',
    'distributor',
    'distributorCard.service',
    'service.category',
    'user',
    'payments' => fn($q) => $q->orderBy('paid_at', 'desc')->with('user')
]);

    
    return view('invoices.show', compact('invoice'));
}
    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل فاتورة مدفوعة');
        }

        // فواتير الموزعين لا يمكن تعديلها
        if ($invoice->client_type === 'distributor') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل فواتير الموزعين');
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

        // فواتير الموزعين لا يمكن تعديلها
        if ($invoice->client_type === 'distributor') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل فواتير الموزعين');
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

        // إعادة الكروت للموزع في حالة فاتورة الموزع
        if ($invoice->client_type === 'distributor' && $invoice->distributor_card_id) {
            $distributorCard = DistributorCard::find($invoice->distributor_card_id);
            if ($distributorCard) {
                $distributorCard->quantity_sold -= $invoice->quantity;
                $distributorCard->quantity_available += $invoice->quantity;
                $distributorCard->save();
            }
        }

        $subscriberId = $invoice->subscriber_id;
        $invoice->delete();

        // تحديث رصيد المشترك
        if ($subscriberId) {
            SubscriberBalance::updateOrCreateForSubscriber($subscriberId);
        }

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

    DB::beginTransaction();
    try {
        // تحديث المبلغ المدفوع والفاتورة
        $invoice->paid_amount += $paymentAmount;
        $invoice->updatePaymentStatus(); // تحدد 'unpaid', 'partial', 'paid'
        $invoice->save();

        // إنشاء الدفعة
        Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'method' => $request->payment_method,
            'amount' => $paymentAmount,
            'paid_at' => now()->format('Y-m-d')
        ]);

        // إذا الفاتورة لموزع، حدث المبلغ المدفوع على البطاقة
        if ($invoice->client_type === 'distributor' && $invoice->distributor_card_id) {
            $card = $invoice->distributorCard;
            $card->paid_amount += $paymentAmount;
            $card->remaining_amount = max(0, ($card->card_price * $invoice->quantity) - $card->paid_amount);
            $card->save();
        }

        // إذا هناك مشترك، حدث رصيد المشترك
        if ($invoice->subscriber_id) {
            SubscriberBalance::updateOrCreateForSubscriber($invoice->subscriber_id);
        }

        DB::commit();

        $invoice->load(['payments' => function($q) {
            $q->orderBy('paid_at', 'desc');
        }, 'payments.user']);

        return redirect()->route('invoices.show', $invoice)
                         ->with('success', 'تم إضافة الدفعة بنجاح');
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error adding payment: ' . $e->getMessage(), [
            'invoice_id' => $invoice->id,
            'request' => $request->all()
        ]);
        return back()->with('error', 'حدث خطأ أثناء إضافة الدفعة: ' . $e->getMessage());
    }
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

    // Ajax - جلب الخدمات حسب الفئة
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
}