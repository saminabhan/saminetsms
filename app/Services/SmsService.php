<?php
// app/Services/SmsService.php
namespace App\Services;

use App\Models\Subscriber;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $apiUrl = 'http://hotsms.ps/sendbulksms.php';
    private $apiToken = '66ef464c07d8f';
    private $sender = 'SAMI NET';

    public function sendMessage(Subscriber $subscriber, string $messageContent)
    {
        try {
            // إنشاء سجل الرسالة في قاعدة البيانات
            $message = Message::create([
                'subscriber_id' => $subscriber->id,
                'message_content' => $messageContent,
                'status' => 'pending'
            ]);

            // تحضير البيانات للإرسال
            $params = [
                'api_token' => $this->apiToken,
                'sender' => $this->sender,
                'mobile' => $subscriber->formatted_phone,
                'type' => '0',
                'text' => $messageContent
            ];

            // إرسال الطلب للـ API
            $response = Http::timeout(30)->get($this->apiUrl, $params);

            // تسجيل استجابة الـ API
            $apiResponse = $response->body();
            
            // تحديد حالة الرسالة بناءً على الاستجابة
            $status = $response->successful() ? 'sent' : 'failed';
            
            // تحديث سجل الرسالة
            $message->update([
                'status' => $status,
                'api_response' => $apiResponse
            ]);

            // تسجيل في اللوق
            Log::info('SMS sent', [
                'subscriber' => $subscriber->name,
                'phone' => $subscriber->formatted_phone,
                'message' => $messageContent,
                'status' => $status,
                'response' => $apiResponse
            ]);

            return [
                'success' => $response->successful(),
                'message' => $message,
                'api_response' => $apiResponse
            ];

        } catch (\Exception $e) {
            // في حالة حدوث خطأ
            Log::error('SMS sending failed', [
                'subscriber' => $subscriber->name,
                'phone' => $subscriber->phone,
                'message' => $messageContent,
                'error' => $e->getMessage()
            ]);

            if (isset($message)) {
                $message->update([
                    'status' => 'failed',
                    'api_response' => 'Error: ' . $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'message' => $message ?? null,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendBulkMessage(array $subscriberIds, string $messageContent)
    {
        $results = [];
        $subscribers = Subscriber::whereIn('id', $subscriberIds)
                                ->where('is_active', true)
                                ->get();

        foreach ($subscribers as $subscriber) {
            $result = $this->sendMessage($subscriber, $messageContent);
            $results[] = [
                'subscriber' => $subscriber,
                'result' => $result
            ];

            // توقف قصير بين الرسائل لتجنب التحميل الزائد على الـ API
            usleep(500000); // 0.5 ثانية
        }

        return $results;
    }
}