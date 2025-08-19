<?php
// app/Http/Controllers/SubscriberController.php
namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
   public function index()
{
    // withCount يحسب عدد الرسائل لكل مشترك
    $subscribers = Subscriber::withCount('messages')
                    ->latest()
                    ->paginate(20);

    return view('subscribers.index', compact('subscribers'));
}


    public function create()
    {
        return view('subscribers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:subscribers,phone',
        ]);

        Subscriber::create($request->only(['name', 'phone']));

        return redirect()->route('subscribers.index')
                        ->with('success', 'تم إضافة المشترك بنجاح');
    }

    public function show(Subscriber $subscriber)
    {
        $messages = $subscriber->messages()->latest()->paginate(10);
        return view('subscribers.show', compact('subscriber', 'messages'));
    }

    public function edit(Subscriber $subscriber)
    {
        return view('subscribers.edit', compact('subscriber'));
    }

    public function update(Request $request, Subscriber $subscriber)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:subscribers,phone,' . $subscriber->id,
        ]);

        $subscriber->update($request->only(['name', 'phone', 'is_active']));

        return redirect()->route('subscribers.index')
                        ->with('success', 'تم تحديث بيانات المشترك بنجاح');
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('subscribers.index')
                        ->with('success', 'تم حذف المشترك بنجاح');
    }

    public function toggle(Subscriber $subscriber)
    {
        $subscriber->update(['is_active' => !$subscriber->is_active]);
        
        $status = $subscriber->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        return redirect()->back()->with('success', $status . ' المشترك بنجاح');
    }
}

