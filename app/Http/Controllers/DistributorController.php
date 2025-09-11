<?php
// app/Http/Controllers/DistributorController.php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\DistributorCard;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
   public function index(Request $request)
{
    $query = Distributor::with(['distributorCards.service', 'invoices.payments']);

    // فلترة حسب النوع
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // فلترة حسب الحالة
    if ($request->filled('is_active')) {
        $query->where('is_active', $request->is_active);
    }

    // البحث بالاسم أو الهاتف
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    $distributors = $query->orderBy('name')->paginate(20);

    // حساب المبالغ والكروت
    foreach ($distributors as $distributor) {
        // إجمالي الفواتير
        $totalAmount = $distributor->invoices->sum('final_amount');

        // مجموع المدفوعات لكل الفواتير
        $totalPaid = $distributor->invoices->sum(function($invoice) {
            return $invoice->payments->sum('amount');
        });

        $distributor->total_amount = $totalAmount;
        $distributor->remaining_amount = max(0, $totalAmount - $totalPaid);

        // الكروت
        $distributor->total_cards = $distributor->distributorCards->sum('quantity');
        $distributor->available_cards = $distributor->distributorCards->sum('quantity_available');

        // حالة الدفع
        if ($distributor->remaining_amount == 0) {
            $distributor->payment_status = 'paid';
        } elseif ($distributor->remaining_amount > 0 && $totalPaid > 0) {
            $distributor->payment_status = 'partial';
        } else {
            $distributor->payment_status = 'unpaid';
        }
    }

    return view('distributors.index', compact('distributors'));
}


    public function create()
    {
        return view('distributors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'type' => 'required|in:distributor,sales_point',
            'is_active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $distributor = Distributor::create($request->all());

        return redirect()->route('distributors.show', $distributor)
                        ->with('success', 'تم إضافة الموزع بنجاح');
    }

    public function show(Distributor $distributor)
    {
        $distributor->load(['distributorCards.service.category']);
        
        // تجميع البيانات للإحصائيات
        $stats = [
            'total_cards' => $distributor->total_cards,
            'available_cards' => $distributor->available_cards,
            'sold_cards' => $distributor->sold_cards,
            'total_amount' => $distributor->total_amount,
            'paid_amount' => $distributor->paid_amount,
            'remaining_amount' => $distributor->remaining_amount,
            'payment_status' => $distributor->payment_status
        ];

        return view('distributors.show', compact('distributor', 'stats'));
    }

    public function edit(Distributor $distributor)
    {
        return view('distributors.edit', compact('distributor'));
    }

    public function update(Request $request, Distributor $distributor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'type' => 'required|in:distributor,sales_point',
            'is_active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $distributor->update($request->all());

        return redirect()->route('distributors.show', $distributor)
                        ->with('success', 'تم تحديث بيانات الموزع بنجاح');
    }

    public function destroy(Distributor $distributor)
    {
        if ($distributor->distributorCards()->exists()) {
            return redirect()->route('distributors.index')
                           ->with('error', 'لا يمكن حذف موزع لديه كروت مسجلة');
        }

        $distributor->delete();

        return redirect()->route('distributors.index')
                        ->with('success', 'تم حذف الموزع بنجاح');
    }

    // صفحة إضافة كروت للموزع
   public function addCards(Distributor $distributor)
{
    $categories = ServiceCategory::where('is_active', 1)
                    ->with(['services' => function($q) { $q->where('is_active', 1); }])
                    ->get();

    // إنشاء مصفوفة JSON للخدمات حسب الفئة
    $servicesByCategory = [];
    foreach($categories as $category) {
        $servicesByCategory[$category->id] = $category->services->map(function($service){
            return [
                'id' => $service->id,
                'name' => $service->name_ar ?? $service->name,
                'price' => (float)$service->price,
                'description' => $service->description ?? '-',
                'allow_quantity' => true, // أو حسب منطقيتك
            ];
        });
    }

    return view('distributors.add-cards', compact('distributor', 'categories', 'servicesByCategory'));
}


    // حفظ الكروت الجديدة
    public function storeCards(Request $request, Distributor $distributor)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
            'card_price' => 'required|numeric|min:0',
            'received_at' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        try {
            $distributor->addCards(
                $request->service_id,
                $request->quantity,
                $request->card_price,
                $request->received_at,
                $request->notes
            );

            return redirect()->route('distributors.show', $distributor)
                           ->with('success', 'تم إضافة الكروت بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'حدث خطأ أثناء إضافة الكروت: ' . $e->getMessage());
        }
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

    // Ajax - جلب كروت الموزع
    public function getDistributorCards(Request $request, Distributor $distributor)
    {
        $cards = $distributor->distributorCards()
                            ->with('service')
                            ->hasAvailableCards()
                            ->get()
                            ->map(function($card) {
                                return [
                                    'id' => $card->id,
                                    'service_name' => $card->service->name_ar ?? $card->service->name,
                                    'quantity_available' => $card->quantity_available,
                                    'card_price' => (float) $card->card_price,
                                    'remaining_amount' => (float) $card->remaining_amount,
                                    'payment_status' => $card->payment_status
                                ];
                            });

        return response()->json(['cards' => $cards]);
    }
}