<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $messages = Message::with('subscriber')->latest()->paginate(20);
        return view('messages.index', compact('messages'));
    }

    public function create()
    {
        $subscribers = Subscriber::where('is_active', true)->get();
        return view('messages.create', compact('subscribers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subscriber_ids' => 'required|array|min:1',
            'subscriber_ids.*' => 'exists:subscribers,id',
            'message_content' => 'required|string|max:1000',
        ]);

        $results = $this->smsService->sendBulkMessage(
            $request->subscriber_ids,
            $request->message_content
        );

        $successCount = collect($results)->where('result.success', true)->count();
        $totalCount = count($results);

        return redirect()->route('messages.index')
                        ->with('success', "تم إرسال {$successCount} من أصل {$totalCount} رسالة بنجاح");
    }

    public function show(Message $message)
    {
        return view('messages.show', compact('message'));
    }

    public function resend(Message $message)
    {
        $result = $this->smsService->sendMessage($message->subscriber, $message->message_content);
        
        if ($result['success']) {
            return redirect()->back()->with('success', 'تم إعادة إرسال الرسالة بنجاح');
        } else {
            return redirect()->back()->with('error', 'فشل في إعادة إرسال الرسالة: ' . ($result['error'] ?? 'خطأ غير معروف'));
        }
    }
}