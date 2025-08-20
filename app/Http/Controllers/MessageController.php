<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    public function aiSuggest(Request $request)
    {
        $prompt = $request->input('prompt', '');
        
        // تحليل الطلب وتحديد نوع الرسالة
        $messageType = $this->analyzePrompt($prompt);
        
        // جرب الطرق المختلفة بالترتيب
        $methods = [
            'smart_template_system',
            'groq_api',
            'huggingface_api',
            'ollama_local',
            'fallback_templates'
        ];
        
        foreach ($methods as $method) {
            try {
                $result = $this->$method($prompt, $messageType);
                if ($result) {
                    return response()->json(['suggestion' => $result]);
                }
            } catch (\Exception $e) {
                Log::warning("AI method $method failed: " . $e->getMessage());
                continue;
            }
        }
        
        return response()->json([
            'error' => 'عذراً، لا يمكن الوصول لخدمة الذكاء الاصطناعي حالياً'
        ], 500);
    }
    
    // نظام القوالب الذكي المحدث
    private function smart_template_system($prompt, $type)
    {
        // استخراج الأرقام من النص (مبالغ، أيام، نسب...)
        preg_match_all('/\d+/', $prompt, $numbers);
        $amount = !empty($numbers[0]) ? $numbers[0][0] : null;
        
        // تحليل الكلمات المفتاحية
        $keywords = $this->extractKeywords($prompt);
        
        switch ($type['category']) {
            case 'payment':
                return $this->generatePaymentMessage($amount, $keywords, $type);
                
            case 'internet':
                return $this->generateInternetMessage($amount, $keywords, $type);
                
            case 'maintenance':
                return $this->generateMaintenanceMessage($keywords, $type);
                
            case 'thank':
                return $this->generateThankMessage($keywords, $type);
                
            case 'offer':
                return $this->generateOfferMessage($amount, $keywords, $type);
                
            case 'problem':
                return $this->generateProblemMessage($keywords, $type);
                
            case 'holiday':
                return $this->generateHolidayMessage($keywords, $type);
                
            case 'custom':
            default:
                return $this->generateCustomMessage($prompt, $keywords, $type);
        }
    }
    
    // تحليل الطلب وتحديد النوع
    private function analyzePrompt($prompt)
    {
        $prompt = strtolower($prompt);
        
        $patterns = [
            'payment' => [
                'keywords' => ['دفع', 'شيكل', 'مبلغ', 'فاتورة', 'مديون', 'عليه', 'دين', 'سداد', 'استحقاق'],
                'priority' => 10
            ],
            'internet' => [
                'keywords' => ['انترنت', 'internet', 'باقة', 'سرعة', 'wifi', 'شبكة', 'اتصال', 'اشتراك'],
                'priority' => 9
            ],
            'offer' => [
                'keywords' => ['عرض', 'خصم', 'تخفيض', '%', 'مجاني', 'هدية', 'عروض', 'تخفيضات'],
                'priority' => 8
            ],
            'maintenance' => [
                'keywords' => ['صيانة', 'انقطاع', 'أعمال', 'توقف', 'إصلاح', 'maintenance', 'downtime'],
                'priority' => 7
            ],
            'problem' => [
                'keywords' => ['مشكلة', 'عطل', 'خلل', 'problem', 'issue', 'مشاكل', 'اعتذار'],
                'priority' => 7
            ],
            'thank' => [
                'keywords' => ['شكر', 'امتنان', 'تقدير', 'thank', 'شكرا', 'نشكر'],
                'priority' => 6
            ],
            'holiday' => [
                'keywords' => ['عيد', 'مناسبة', 'تهنئة', 'holiday', 'عام سعيد', 'كريم'],
                'priority' => 5
            ]
        ];
        
        $detectedTypes = [];
        
        foreach ($patterns as $type => $config) {
            $score = 0;
            foreach ($config['keywords'] as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    $score += $config['priority'];
                }
            }
            if ($score > 0) {
                $detectedTypes[$type] = $score;
            }
        }
        
        if (!empty($detectedTypes)) {
            arsort($detectedTypes);
            $topType = array_key_first($detectedTypes);
            return [
                'category' => $topType,
                'confidence' => $detectedTypes[$topType],
                'all_types' => $detectedTypes
            ];
        }
        
        return ['category' => 'custom', 'confidence' => 0, 'all_types' => []];
    }
    
    // استخراج الكلمات المفتاحية
    private function extractKeywords($prompt)
    {
        $keywords = [
            'urgent' => strpos($prompt, 'عاجل') !== false || strpos($prompt, 'urgent') !== false,
            'polite' => strpos($prompt, 'مؤدب') !== false || strpos($prompt, 'لطيف') !== false,
            'formal' => strpos($prompt, 'رسمي') !== false || strpos($prompt, 'formal') !== false,
            'friendly' => strpos($prompt, 'ودود') !== false || strpos($prompt, 'friendly') !== false,
            'reminder' => strpos($prompt, 'تذكير') !== false || strpos($prompt, 'reminder') !== false,
        ];
        
        return $keywords;
    }
    
    // رسائل الدفع
    private function generatePaymentMessage($amount, $keywords, $type)
    {
        $templates = [
            'polite' => [
                "عزيزي العميل، نذكركم بلطف أن لديكم مبلغ {$amount} شيكل مستحق الدفع. نشكركم على تعاونكم 🙏",
                "مرحباً، نود تذكيركم بوجود مبلغ {$amount} شيكل في حسابكم. يمكنكم الدفع في أي وقت مناسب لكم 💙",
                "عميلنا الكريم، يرجى تسديد المبلغ المستحق {$amount} شيكل عند الإمكان. شكراً لتفهمكم ❤️"
            ],
            'urgent' => [
                "تنبيه: يرجى تسديد المبلغ المستحق {$amount} شيكل اليوم تجنباً لتعليق الخدمة ⚠️",
                "عاجل: لديكم مبلغ {$amount} شيكل مستحق الدفع. يرجى التواصل معنا فوراً 📞",
                "إشعار هام: مبلغ {$amount} شيكل متأخر في الدفع. تجنبوا قطع الخدمة بالتسديد اليوم 🔴"
            ],
            'default' => [
                "لديكم مبلغ {$amount} شيكل مستحق الدفع. يرجى التسديد في أقرب وقت. شكراً لكم 💳",
                "فاتورتكم بمبلغ {$amount} شيكل جاهزة للدفع. اتصلوا بنا أو ادفعوا أونلاين 📱",
                "مبلغ {$amount} شيكل مطلوب تسديده. نقدر تعاونكم معنا دائماً 🤝"
            ]
        ];
        
        $style = $keywords['polite'] ? 'polite' : ($keywords['urgent'] ? 'urgent' : 'default');
        $options = $templates[$style];
        
        return str_replace('{' . $amount . '}', $amount ?: 'المستحق', $options[array_rand($options)]);
    }
    
    // رسائل الإنترنت
    private function generateInternetMessage($amount, $keywords, $type)
    {
        if ($amount) {
            $templates = [
                "باقة إنترنت مميزة بـ {$amount} شيكل شهرياً! سرعة عالية وثبات في الاتصال 🚀",
                "اشترك الآن في باقتنا الجديدة بـ {$amount} شيكل واستمتع بإنترنت بلا حدود 📶",
                "عرض خاص: باقة {$amount} شيكل - إنترنت فائق السرعة مع دعم فني 24/7 💻",
                "باقة الـ {$amount} شيكل متاحة الآن! اتصال مستقر وسرعات ممتازة 🌐"
            ];
        } else {
            $templates = [
                "إنترنت سريع وموثوق! اختر الباقة التي تناسبك واستمتع بأفضل خدمة 🚀",
                "خدمة إنترنت احترافية مع دعم فني متميز. اتصل بنا الآن! 📞",
                "باقات إنترنت متنوعة وأسعار منافسة. احجز باقتك اليوم! 💙",
                "إنترنت منزلي بأعلى جودة وأفضل الأسعار. تواصل معنا 🏠"
            ];
        }
        
        return $templates[array_rand($templates)];
    }
    
    // رسائل الصيانة
    private function generateMaintenanceMessage($keywords, $type)
    {
        $templates = [
            'advance_notice' => [
                "إشعار صيانة: سيتم إجراء أعمال صيانة غداً من الساعة 2-4 صباحاً. نعتذر للإزعاج 🔧",
                "صيانة مجدولة: انقطاع مؤقت للخدمة غداً لمدة ساعتين لتحسين الشبكة ⚙️",
                "تطوير الشبكة: صيانة دورية غداً صباحاً لضمان أفضل أداء لكم 🛠️"
            ],
            'current' => [
                "نعمل حالياً على حل مشكلة فنية. الخدمة ستعود خلال ساعة واحدة ⏰",
                "انقطاع مؤقت بسبب أعمال الصيانة. نعتذر ونعمل على الحل سريعاً 🔄",
                "جاري إصلاح عطل في الشبكة. شكراً لصبركم وتفهمكم 🙏"
            ],
            'completed' => [
                "تمت أعمال الصيانة بنجاح! الخدمة عادت بشكل طبيعي. شكراً لصبركم ✅",
                "انتهت الصيانة والشبكة تعمل بأفضل أداء. نشكركم على تفهمكم 💚",
                "الخدمة متاحة الآن بعد انتهاء أعمال التطوير. استمتعوا بالسرعة الجديدة! 🎉"
            ]
        ];
        
        $style = $keywords['urgent'] ? 'current' : 'advance_notice';
        $options = $templates[$style];
        
        return $options[array_rand($options)];
    }
    
    // رسائل الشكر
    private function generateThankMessage($keywords, $type)
    {
        $templates = [
            "شكراً لثقتكم الغالية! نحن فخورون بخدمتكم ونسعى دائماً لإرضائكم ❤️",
            "امتناننا لا حدود له لاختياركم خدماتنا. أنتم الأهم في مسيرتنا 🌟",
            "نقدر وفاءكم وثقتكم. شكراً لكونكم عملاء مميزين معنا 🙏",
            "عملاؤنا الكرام، شكراً لكم من القلب على دعمكم المستمر 💙",
            "بفضل ثقتكم نستمر ونتطور. شكراً لاختياركم خدماتنا المتميزة 🤝"
        ];
        
        return $templates[array_rand($templates)];
    }
    
    // رسائل العروض
    private function generateOfferMessage($amount, $keywords, $type)
    {
        if ($amount) {
            $templates = [
                "عرض محدود! خصم {$amount}% على جميع الباقات هذا الأسبوع. سارع! 🔥",
                "فقط {$amount} شيكل للشهر الأول! اشترك الآن في أي باقة 🎁",
                "وفر {$amount} شيكل مع عرضنا الخاص. العدد محدود! ⚡",
                "عرض اليوم: خصم {$amount}% لأول 50 مشترك جديد 🏃‍♂️"
            ];
        } else {
            $templates = [
                "عروض حصرية لفترة محدودة! اشترك الآن واحصل على شهر مجاني 🆓",
                "لا تفوت الفرصة! عروض خاصة لعملائنا الكرام. اتصل الآن 📞",
                "عرض الأسبوع: باقة مضاعفة بنفس السعر! العدد محدود 2️⃣",
                "مفاجأة سارة! هدايا وخصومات حصرية. تواصل معنا فوراً 🎉"
            ];
        }
        
        return $templates[array_rand($templates)];
    }
    
    // رسائل المشاكل
    private function generateProblemMessage($keywords, $type)
    {
        $templates = [
            'apology' => [
                "نعتذر بشدة عن المشكلة التي واجهتموها. نعمل على حلها فوراً 🙏",
                "أسفون للإزعاج! فريقنا الفني يعمل على معالجة المشكلة بأسرع وقت ⚡",
                "اعتذارنا لما حدث. سنعوضكم عن أي إزعاج واجهتموه 💔"
            ],
            'solution' => [
                "تم حل المشكلة بنجاح! يمكنكم الآن استخدام الخدمة بشكل طبيعي ✅",
                "المشكلة محلولة والخدمة تعمل بكامل طاقتها. شكراً لصبركم 💚",
                "عادت الأمور لطبيعتها! نعتذر مرة أخرى ونشكركم على تفهمكم 🌈"
            ]
        ];
        
        $style = $keywords['urgent'] ? 'apology' : 'solution';
        $options = $templates[$style];
        
        return $options[array_rand($options)];
    }
    
    // رسائل المناسبات
    private function generateHolidayMessage($keywords, $type)
    {
        $currentMonth = date('n');
        
        $holidays = [
            'general' => [
                "كل عام وأنتم بخير! نتمنى لكم أياماً سعيدة مع أحبائكم 🎊",
                "أسعد الأوقات وأجمل اللحظات في هذه المناسبة الكريمة 💝",
                "بمناسبة هذا العيد السعيد، نتمنى لكم الفرح والسعادة 🎉"
            ],
            'ramadan' => [
                "رمضان مبارك! نتمنى لكم شهراً مليئاً بالخير والبركات 🌙",
                "أهلاً وسهلاً بشهر الخير! كل عام وأنتم بألف خير 🕌",
                "في هذا الشهر الكريم، نتمنى لكم الصحة والسعادة ✨"
            ],
            'eid' => [
                "عيد سعيد مبارك! نتمنى لكم الفرحة مع الأهل والأحباب 🎈",
                "كل عام وأنتم بخير بمناسبة العيد السعيد! أيام مباركة 🎁",
                "عيدكم مبارك وتقبل الله منا ومنكم صالح الأعمال 🌟"
            ]
        ];
        
        // تحديد نوع المناسبة بناءً على الشهر أو الكلمات
        $holidayType = 'general';
        if ($currentMonth == 3 || $currentMonth == 4) $holidayType = 'ramadan';
        if (strpos(strtolower($prompt ?? ''), 'عيد') !== false) $holidayType = 'eid';
        
        $templates = $holidays[$holidayType];
        return $templates[array_rand($templates)];
    }
    
    // رسائل مخصصة
    private function generateCustomMessage($prompt, $keywords, $type)
    {
        // محاولة فهم السياق وإنشاء رد مناسب
        $context = $this->analyzeContext($prompt);
        
        $templates = [
            'business' => [
                "مرحباً بكم! نحن هنا لخدمتكم وتلبية احتياجاتكم في أي وقت 💼",
                "شركتنا ملتزمة بتقديم أفضل الخدمات لعملائنا الكرام 🏢",
                "نتطلع دائماً لخدمتكم وإرضائكم بأعلى مستويات الجودة ⭐"
            ],
            'friendly' => [
                "أهلاً وسهلاً! نحن سعداء بوجودكم معنا وخدمتكم دائماً 😊",
                "مرحباً بكم في عائلتنا الكبيرة! نتمنى لكم تجربة رائعة 🤗",
                "أهلاً بكم! دائماً في خدمتكم بكل محبة واحترام ❤️"
            ],
            'informative' => [
                "للاستفسارات والمساعدة، يرجى التواصل معنا في أي وقت 📞",
                "فريق الدعم الفني متاح لمساعدتكم 24/7 🛟",
                "لمزيد من المعلومات، لا تترددوا في الاتصال بنا 📋"
            ]
        ];
        
        $style = $keywords['friendly'] ? 'friendly' : ($keywords['formal'] ? 'business' : 'informative');
        $options = $templates[$style];
        
        return $options[array_rand($options)];
    }
    
    // تحليل السياق
    private function analyzeContext($prompt)
    {
        $businessWords = ['شركة', 'خدمة', 'عمل', 'business'];
        $personalWords = ['أهل', 'عائلة', 'شخصي', 'personal'];
        
        $businessScore = 0;
        $personalScore = 0;
        
        foreach ($businessWords as $word) {
            if (stripos($prompt, $word) !== false) $businessScore++;
        }
        
        foreach ($personalWords as $word) {
            if (stripos($prompt, $word) !== false) $personalScore++;
        }
        
        return $businessScore > $personalScore ? 'business' : 'personal';
    }
    
    // الطريقة الثانية: Groq API (سريع ومجاني جزئياً)
    private function groq_api($prompt, $type)
    {
        $systemPrompt = $this->buildSystemPrompt($type);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return isset($data['choices'][0]['message']['content']) ? 
                   trim($data['choices'][0]['message']['content']) : null;
        }
        
        return null;
    }
    
    // الطريقة الثالثة: Hugging Face API
    private function huggingface_api($prompt, $type)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY', 'hf_your_token_here'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium', [
            'inputs' => "اكتب رسالة مناسبة بالعربية: " . $prompt,
            'parameters' => [
                'max_length' => 100,
                'temperature' => 0.7,
            ]
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return isset($data[0]['generated_text']) ? 
                   trim(str_replace("اكتب رسالة مناسبة بالعربية: " . $prompt, '', $data[0]['generated_text'])) : null;
        }
        
        return null;
    }
    
    // الطريقة الرابعة: Ollama المحلي
    private function ollama_local($prompt, $type)
    {
        $systemPrompt = $this->buildSystemPrompt($type);
        
        $response = Http::timeout(30)->post('http://localhost:11434/api/generate', [
            'model' => 'llama3.2:1b',
            'prompt' => $systemPrompt . "\n\nطلب المستخدم: " . $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
                'num_predict' => 100,
            ]
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return isset($data['response']) ? trim($data['response']) : null;
        }
        
        return null;
    }
    
    // بناء System Prompt مخصص حسب النوع
    private function buildSystemPrompt($type)
    {
        $basePrompt = "أنت مساعد ذكي لكتابة رسائل SMS تسويقية باللغة العربية. ";
        
        switch ($type['category']) {
            case 'payment':
                return $basePrompt . "متخصص في كتابة رسائل تذكير بالدفع مؤدبة ومحترمة. اكتب رسالة قصيرة (أقل من 160 حرف) تتضمن التفاصيل المطلوبة.";
                
            case 'internet':
                return $basePrompt . "متخصص في كتابة رسائل تسويقية لخدمات الإنترنت. اكتب رسالة جذابة ومقنعة عن خدمات الإنترنت.";
                
            case 'offer':
                return $basePrompt . "متخصص في كتابة رسائل إعلانية للعروض والخصومات. اكتب رسالة مثيرة تحفز على الشراء.";
                
            case 'maintenance':
                return $basePrompt . "متخصص في كتابة إشعارات الصيانة المهذبة. اكتب رسالة واضحة ومعتذرة عن انقطاع الخدمة.";
                
            default:
                return $basePrompt . "اكتب رسالة مناسبة وقصيرة (أقل من 160 حرف) بناءً على الطلب. استخدم لغة مؤدبة ومحترمة.";
        }
    }
    
    // القوالب البديلة المحسنة
    private function fallback_templates($prompt, $type)
    {
        return $this->smart_template_system($prompt, $type);
    }
}