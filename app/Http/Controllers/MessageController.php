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
        // استخراج الأرقام والمبالغ من النص
        preg_match_all('/\d+/', $prompt, $numbers);
        $amount = !empty($numbers[0]) ? $numbers[0][0] : null;
        
        // تحليل الكلمات المفتاحية المتطور
        $keywords = $this->extractKeywords($prompt);
        
        // تحديد نوع الرسالة بناءً على التحليل المتطور
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
                
            case 'welcome':
                return $this->generateWelcomeMessage($keywords, $type);
                
            case 'support':
                return $this->generateSupportMessage($keywords, $type);
                
            case 'renewal':
                return $this->generateRenewalMessage($amount, $keywords, $type);
                
            case 'upgrade':
                return $this->generateUpgradeMessage($amount, $keywords, $type);
                
            case 'complaint':
                return $this->generateComplaintMessage($keywords, $type);
                
            case 'custom':
            default:
                return $this->generateCustomMessage($prompt, $keywords, $type);
        }
    }

        private function generateWelcomeMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        
        $templates = [
            "🤗 أهلاً وسهلاً بكم في عائلة {$companyName} الكبيرة! نعدكم بخدمة متميزة وتجربة رائعة",
            "🌟 مرحباً بكم في {$companyName}! انضممتم للعائلة وسنكون دائماً في خدمتكم بكل حب",
            "💙 {$companyName} يرحب بكم! أهلاً بالعضو الجديد في مجتمعنا المتميز من العملاء الكرام",
            "🎉 أهلاً بكم في {$companyName}! بداية رائعة لشراكة طويلة مليئة بالخدمة المتميزة",
            "🏠 مرحباً بكم في بيتكم الثاني - {$companyName}! نعدكم بأن تشعروا بالراحة والاهتمام دائماً"
        ];
        
        return $templates[array_rand($templates)];
    }

    // تحليل الطلب وتحديد النوع - نظام متطور
    private function analyzePrompt($prompt)
    {
        $originalPrompt = $prompt;
        $prompt = strtolower($prompt);
        
        // إزالة علامات الترقيم والرموز للتحليل الأفضل
        $cleanPrompt = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $prompt);
        $words = array_filter(explode(' ', $cleanPrompt));
        
        $patterns = [
            'payment' => [
                'keywords' => ['دفع', 'شيكل', 'مبلغ', 'فاتورة', 'مديون', 'عليه', 'دين', 'سداد', 'استحقاق', 'متأخر', 'قسط', 'حساب', 'رصيد', 'مستحق', 'تسديد', 'pay', 'payment', 'bill', 'debt', 'money', 'owe', 'balance'],
                'phrases' => ['عليه فلوس', 'ما دفع', 'مش مسدد', 'متأخر بالدفع', 'حسابه مديون', 'فاتورته جاهزة'],
                'priority' => 15,
                'context_words' => ['تذكير', 'reminder', 'عاجل', 'urgent', 'آخر إنذار', 'قطع الخدمة']
            ],
            'internet' => [
                'keywords' => ['انترنت', 'internet', 'نت', 'net', 'باقة', 'سرعة', 'wifi', 'شبكة', 'اتصال', 'اشتراك', 'broadband', 'connection', 'speed', 'mb', 'gb', 'unlimited', 'fiber', 'adsl', 'package', 'plan'],
                'phrases' => ['سامي نت', 'sami net', 'خدمة النت', 'باقة النت', 'اشتراك الانترنت', 'سرعة الانترنت'],
                'priority' => 12,
                'context_words' => ['سريع', 'بطيء', 'منقطع', 'مشترك جديد', 'تجديد', 'upgrade']
            ],
            'offer' => [
                'keywords' => ['عرض', 'خصم', 'تخفيض', '%', 'بالمية', 'مجاني', 'هدية', 'عروض', 'تخفيضات', 'offer', 'discount', 'sale', 'promotion', 'deal', 'free', 'gift'],
                'phrases' => ['عرض خاص', 'خصم كبير', 'لفترة محدودة', 'مجانا', 'بنص السعر', 'وفر فلوس'],
                'priority' => 10,
                'context_words' => ['محدود', 'limited', 'سارع', 'hurry', 'اليوم فقط', 'today only']
            ],
            'maintenance' => [
                'keywords' => ['صيانة', 'انقطاع', 'أعمال', 'توقف', 'إصلاح', 'maintenance', 'downtime', 'repair', 'fix', 'upgrade', 'تطوير', 'تحسين'],
                'phrases' => ['أعمال صيانة', 'انقطاع مؤقت', 'تطوير الشبكة', 'إصلاح العطل', 'توقف الخدمة'],
                'priority' => 11,
                'context_words' => ['مؤقت', 'temporary', 'مجدول', 'scheduled', 'طارئ', 'emergency']
            ],
            'problem' => [
                'keywords' => ['مشكلة', 'عطل', 'خلل', 'problem', 'issue', 'مشاكل', 'اعتذار', 'خراب', 'مش شغال', 'معطل', 'error', 'trouble', 'fault', 'broken'],
                'phrases' => ['في مشكلة', 'النت مش شغال', 'عطل في الشبكة', 'خلل تقني', 'مشكلة فنية'],
                'priority' => 13,
                'context_words' => ['حل', 'solution', 'إصلاح', 'معالجة', 'نعتذر', 'sorry']
            ],
            'thank' => [
                'keywords' => ['شكر', 'امتنان', 'تقدير', 'thank', 'شكرا', 'نشكر', 'grateful', 'appreciate', 'ممتن'],
                'phrases' => ['نشكركم', 'شكرا لكم', 'نقدر ثقتكم', 'امتنان كبير', 'فخورين بكم'],
                'priority' => 8,
                'context_words' => ['ثقة', 'trust', 'وفاء', 'loyalty', 'عملاء مميزين', 'valued customers']
            ],
            'holiday' => [
                'keywords' => ['عيد', 'مناسبة', 'تهنئة', 'holiday', 'عام سعيد', 'كريم', 'مبارك', 'رمضان', 'فطر', 'أضحى', 'christmas', 'new year'],
                'phrases' => ['عيد مبارك', 'عام سعيد', 'رمضان كريم', 'كل عام وأنتم بخير'],
                'priority' => 7,
                'context_words' => ['تهنئة', 'congratulation', 'celebration', 'احتفال']
            ],
            'welcome' => [
                'keywords' => ['مرحبا', 'أهلا', 'welcome', 'hello', 'hi', 'انضمام', 'جديد', 'new', 'join'],
                'phrases' => ['مرحبا بك', 'أهلا وسهلا', 'عميل جديد', 'انضممت لنا'],
                'priority' => 6,
                'context_words' => ['عائلة', 'family', 'فريق', 'team', 'مجتمع', 'community']
            ],
            'support' => [
                'keywords' => ['مساعدة', 'دعم', 'خدمة عملاء', 'support', 'help', 'assistance', 'customer service', 'تواصل', 'اتصال'],
                'phrases' => ['خدمة العملاء', 'الدعم الفني', 'نحن هنا لمساعدتك', 'تواصل معنا'],
                'priority' => 9,
                'context_words' => ['24/7', 'دائما', 'always', 'متاح', 'available']
            ],
            'renewal' => [
                'keywords' => ['تجديد', 'renewal', 'renew', 'extend', 'تمديد', 'انتهاء', 'expire', 'انتهت', 'مدة'],
                'phrases' => ['تجديد الاشتراك', 'انتهت المدة', 'تمديد الخدمة', 'تجديد الباقة'],
                'priority' => 11,
                'context_words' => ['مدة', 'period', 'شهر', 'month', 'سنة', 'year']
            ],
            'upgrade' => [
                'keywords' => ['ترقية', 'upgrade', 'تطوير', 'تحسين', 'improve', 'better', 'أفضل', 'زيادة سرعة'],
                'phrases' => ['ترقية الباقة', 'تحسين السرعة', 'باقة أفضل', 'خدمة محسنة'],
                'priority' => 9,
                'context_words' => ['سرعة', 'speed', 'performance', 'أداء', 'جودة', 'quality']
            ],
            'complaint' => [
                'keywords' => ['شكوى', 'complaint', 'غير راضي', 'مش عاجبني', 'سيء', 'bad', 'مشكلة في الخدمة', 'زعلان'],
                'phrases' => ['مش راضي', 'خدمة سيئة', 'مشكلة كبيرة', 'غير مقبول', 'أريد حل'],
                'priority' => 14,
                'context_words' => ['حل', 'solution', 'تعويض', 'compensation', 'اعتذار', 'apology']
            ]
        ];
        
        $detectedTypes = [];
        $contextInfo = [];
        
        foreach ($patterns as $type => $config) {
            $score = 0;
            $matchedKeywords = [];
            $matchedPhrases = [];
            
            // فحص الكلمات المفتاحية
            foreach ($config['keywords'] as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    $score += $config['priority'];
                    $matchedKeywords[] = $keyword;
                }
            }
            
            // فحص العبارات المركبة
            if (isset($config['phrases'])) {
                foreach ($config['phrases'] as $phrase) {
                    if (strpos($prompt, strtolower($phrase)) !== false) {
                        $score += $config['priority'] * 1.5; // وزن أكبر للعبارات المركبة
                        $matchedPhrases[] = $phrase;
                    }
                }
            }
            
            // فحص كلمات السياق
            if (isset($config['context_words'])) {
                foreach ($config['context_words'] as $contextWord) {
                    if (strpos($prompt, $contextWord) !== false) {
                        $score += 3;
                        $contextInfo[$type][] = $contextWord;
                    }
                }
            }
            
            if ($score > 0) {
                $detectedTypes[$type] = [
                    'score' => $score,
                    'keywords' => $matchedKeywords,
                    'phrases' => $matchedPhrases,
                    'context' => $contextInfo[$type] ?? []
                ];
            }
        }
        
        // ترتيب حسب النقاط
        uasort($detectedTypes, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        if (!empty($detectedTypes)) {
            $topType = array_key_first($detectedTypes);
            $topScore = $detectedTypes[$topType]['score'];
            
            return [
                'category' => $topType,
                'confidence' => $topScore,
                'all_types' => $detectedTypes,
                'matched_keywords' => $detectedTypes[$topType]['keywords'],
                'matched_phrases' => $detectedTypes[$topType]['phrases'],
                'context' => $detectedTypes[$topType]['context'],
                'original_prompt' => $originalPrompt
            ];
        }
        
        return [
            'category' => 'custom', 
            'confidence' => 0, 
            'all_types' => [],
            'original_prompt' => $originalPrompt
        ];
    }
    
    // استخراج الكلمات المفتاحية - نظام متطور
    private function extractKeywords($prompt)
    {
        $prompt = strtolower($prompt);
        
        $keywords = [
            // نبرة الرسالة
            'urgent' => $this->checkPattern($prompt, ['عاجل', 'urgent', 'فوري', 'سريع', 'حالا', 'الآن', 'immediately', 'asap']),
            'polite' => $this->checkPattern($prompt, ['مؤدب', 'polite', 'لطيف', 'nice', 'محترم', 'respectful', 'بأدب', 'بلطف']),
            'formal' => $this->checkPattern($prompt, ['رسمي', 'formal', 'official', 'professional', 'مهني']),
            'friendly' => $this->checkPattern($prompt, ['ودود', 'friendly', 'warm', 'حميمي', 'قريب', 'أليف']),
            'apologetic' => $this->checkPattern($prompt, ['اعتذار', 'sorry', 'apologetic', 'نعتذر', 'نأسف', 'متأسفين']),
            
            // نوع الرسالة
            'reminder' => $this->checkPattern($prompt, ['تذكير', 'reminder', 'تنبيه', 'alert', 'إشعار', 'notification']),
            'announcement' => $this->checkPattern($prompt, ['إعلان', 'announcement', 'notify', 'أعلن', 'نعلن']),
            'invitation' => $this->checkPattern($prompt, ['دعوة', 'invitation', 'invite', 'ندعوك', 'مدعو']),
            'congratulation' => $this->checkPattern($prompt, ['تهنئة', 'congratulation', 'مبروك', 'congratulations', 'نهنئ']),
            
            // التوقيت
            'today' => $this->checkPattern($prompt, ['اليوم', 'today', 'هذا اليوم']),
            'tomorrow' => $this->checkPattern($prompt, ['غدا', 'tomorrow', 'بكرة']),
            'this_week' => $this->checkPattern($prompt, ['هذا الأسبوع', 'this week', 'الأسبوع']),
            'urgent_time' => $this->checkPattern($prompt, ['آخر فرصة', 'last chance', 'انتهاء العرض', 'offer ends']),
            
            // العواطف
            'happy' => $this->checkPattern($prompt, ['سعيد', 'happy', 'مبسوط', 'فرحان', 'joy', 'cheerful']),
            'concerned' => $this->checkPattern($prompt, ['قلق', 'concerned', 'worried', 'منزعج', 'مهموم']),
            'excited' => $this->checkPattern($prompt, ['متحمس', 'excited', 'enthusiastic', 'مشوق']),
            
            // المحتوى
            'personal' => $this->checkPattern($prompt, ['شخصي', 'personal', 'خاص', 'فردي']),
            'business' => $this->checkPattern($prompt, ['تجاري', 'business', 'عمل', 'تسويق', 'marketing']),
            'technical' => $this->checkPattern($prompt, ['تقني', 'technical', 'فني', 'تكنولوجي']),
            
            // خصائص سامي نت
            'company_name' => $this->checkPattern($prompt, ['سامي نت', 'sami net', 'samiNet', 'الشركة', 'company']),
            'service_quality' => $this->checkPattern($prompt, ['جودة', 'quality', 'ممتاز', 'excellent', 'أفضل', 'best']),
            'customer_care' => $this->checkPattern($prompt, ['اهتمام بالعملاء', 'customer care', 'خدمة العملاء', 'customer service']),
        ];
        
        return $keywords;
    }
    
    // دالة مساعدة لفحص الأنماط
    private function checkPattern($text, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (strpos($text, strtolower($pattern)) !== false) {
                return true;
            }
        }
        return false;
    }
    
    // رسائل الدفع
   private function generatePaymentMessage($amount, $keywords, $type)
    {
        $companyName = "سامي نت";
        $matched_keywords = $type['matched_keywords'] ?? [];
        $context = $type['context'] ?? [];
        
        // تحديد مستوى الإلحاح
        $urgencyLevel = 'normal';
        if ($keywords['urgent'] || in_array('عاجل', $context) || in_array('آخر إنذار', $matched_keywords)) {
            $urgencyLevel = 'urgent';
        } elseif ($keywords['polite'] || $keywords['friendly']) {
            $urgencyLevel = 'polite';
        }
        
        $templates = [
            'polite' => [
                "عزيزي عميل {$companyName}، نذكركم بلطف أن لديكم مبلغ {$amount} شيكل مستحق الدفع. نشكركم على تعاونكم الدائم 🙏",
                "مرحباً من {$companyName}، لديكم فاتورة بمبلغ {$amount} شيكل. يمكنكم الدفع في الوقت المناسب لكم 💙",
                "عميلنا الكريم في {$companyName}، مبلغ {$amount} شيكل في انتظار التسديد. نقدر ثقتكم الغالية ❤️",
                "تحية طيبة من {$companyName}، رصيدكم يظهر مبلغ {$amount} شيكل مستحق. شكراً لكونكم معنا 🌟"
            ],
            'urgent' => [
                "تنبيه من {$companyName}: يرجى تسديد مبلغ {$amount} شيكل اليوم تجنباً لتعليق الخدمة ⚠️",
                "عاجل - {$companyName}: مبلغ {$amount} شيكل متأخر في الدفع. تواصلوا معنا فوراً 📞",
                "إشعار هام من {$companyName}: آخر فرصة لتسديد {$amount} شيكل قبل إيقاف الخدمة 🔴",
                "تذكير أخير من {$companyName}: مبلغ {$amount} شيكل يجب دفعه خلال 24 ساعة ⏰"
            ],
            'normal' => [
                "من {$companyName}: لديكم مبلغ {$amount} شيكل مستحق الدفع. يرجى التسديد قريباً 💳",
                "{$companyName} - فاتورتكم بمبلغ {$amount} شيكل جاهزة للدفع. ادفعوا أونلاين أو اتصلوا بنا 📱",
                "حساب {$companyName}: مبلغ {$amount} شيكل بانتظار التسديد. شكراً لتعاونكم 🤝",
                "إشعار من {$companyName}: استحقاق بمبلغ {$amount} شيكل. طرق دفع متعددة متاحة 💰"
            ],
            'final_notice' => [
                "إنذار نهائي من {$companyName}: مبلغ {$amount} شيكل يجب تسديده خلال 48 ساعة وإلا ستُوقف الخدمة 🚫",
                "آخر تحذير من {$companyName}: {$amount} شيكل متأخر كثيراً. تجنبوا قطع الخدمة بالدفع الآن ❌",
                "{$companyName} - إشعار قطع الخدمة: مبلغ {$amount} شيكل لم يُسدد. اتصلوا الآن لتجنب الانقطاع ⛔"
            ]
        ];
        
        // إذا كان في الطلب كلمات تدل على الإنذار النهائي
        if (strpos($type['original_prompt'], 'آخر') !== false || strpos($type['original_prompt'], 'نهائي') !== false) {
            $urgencyLevel = 'final_notice';
        }
        
        $options = $templates[$urgencyLevel];
        $message = $options[array_rand($options)];
        
        // استبدال المبلغ
        return str_replace('{' . $amount . '}', $amount ?: 'المستحق', $message);
    }
    
    // رسائل الإنترنت
        private function generateInternetMessage($amount, $keywords, $type)
    {
        $companyName = "سامي نت";
        
        if ($amount) {
            $templates = [
                "🚀 {$companyName} - باقة إنترنت مميزة بـ {$amount} شيكل شهرياً! سرعة عالية وثبات في الاتصال",
                "📶 عرض {$companyName}: اشترك بباقة {$amount} شيكل واستمتع بإنترنت بلا حدود وسرعة فائقة",
                "💻 {$companyName} - باقة الـ {$amount} شيكل: إنترنت مستقر + دعم فني 24/7 + تركيب مجاني",
                "🌐 جديد من {$companyName}: باقة {$amount} شيكل - اتصال موثوق وسرعات تصل لأعلى المعدلات",
                "⚡ {$companyName} يقدم: إنترنت بـ {$amount} شيكل فقط! جودة عالية وأسعار تنافسية",
                "🏠 إنترنت منزلي من {$companyName} بـ {$amount} شيكل - تغطية ممتازة في كل أنحاء المنزل"
            ];
        } else {
            $templates = [
                "🚀 {$companyName} - أسرع إنترنت في المنطقة! باقات متنوعة تناسب احتياجاتكم",
                "📞 {$companyName} في خدمتكم: إنترنت موثوق مع دعم فني متميز 24/7",
                "💙 اختاروا {$companyName}: جودة عالية، أسعار منافسة، وخدمة عملاء ممتازة",
                "🏆 {$companyName} الأول في الخدمة: إنترنت فايبر عالي السرعة لجميع الاستخدامات",
                "🌟 ثقوا بـ {$companyName}: سنوات من التميز في خدمات الإنترنت والاتصالات",
                "📶 {$companyName} يضمن لكم: اتصال مستقر، سرعة حقيقية، وأسعار عادلة"
            ];
        }
        
        return $templates[array_rand($templates)];
    }

    
    // رسائل الصيانة
    private function generateMaintenanceMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        
        $templates = [
            'advance_notice' => [
                "📋 إشعار من {$companyName}: صيانة مجدولة غداً من 2-4 صباحاً لتحسين جودة الخدمة. نعتذر للإزعاج",
                "🔧 {$companyName} - تطوير الشبكة: انقطاع مؤقت غداً صباحاً لضمان أفضل أداء لعملائنا الكرام",
                "⚙️ من فريق {$companyName}: أعمال صيانة دورية غداً لرفع كفاءة الشبكة وتحسين التغطية",
                "🛠️ {$companyName} يعمل من أجلكم: صيانة ليلية غداً لترقية الأجهزة وزيادة السرعات"
            ],
            'current' => [
                "⏰ {$companyName} - نعمل حالياً على حل مشكلة فنية طارئة. الخدمة ستعود خلال ساعة بإذن الله",
                "🔄 انقطاع مؤقت من {$companyName} بسبب صيانة عاجلة. فريقنا الفني يعمل بأقصى سرعة للحل",
                "🙏 {$companyName} يعتذر: عطل مؤقت قيد الإصلاح. شكراً لصبركم وتفهمكم الكريم",
                "⚡ {$companyName} - جاري إصلاح خلل في الشبكة. نقدر انتظاركم ونعمل على استعادة الخدمة سريعاً"
            ],
            'completed' => [
                "✅ {$companyName} - تمت الصيانة بنجاح! الخدمة عادت بأداء محسن. شكراً لصبركم الكريم",
                "💚 انتهت أعمال التطوير في {$companyName}! استمتعوا الآن بسرعة أعلى وثبات أكبر",
                "🎉 {$companyName} - الشبكة تعمل بكامل طاقتها بعد الصيانة. نشكركم على تفهمكم",
                "🌟 {$companyName} يعلن: انتهاء أعمال الترقية بنجاح. خدمة محسنة في انتظاركم الآن!"
            ]
        ];
        
        $style = 'advance_notice';
        if ($keywords['urgent'] || $keywords['concerned']) {
            $style = 'current';
        } elseif (strpos($type['original_prompt'], 'انتهت') !== false || strpos($type['original_prompt'], 'تمت') !== false) {
            $style = 'completed';
        }
        
        $options = $templates[$style];
        return $options[array_rand($options)];
    }

    
    // رسائل الشكر
   private function generateThankMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        
        $templates = [
            "❤️ فريق {$companyName} يشكركم من القلب! ثقتكم الغالية هي سر نجاحنا واستمرارنا",
            "🌟 {$companyName} فخور بكم! شكراً لاختياركم خدماتنا وكونكم عملاء مميزين معنا",
            "🙏 امتنان {$companyName} لا حدود له! نقدر وفاءكم ونسعى دائماً لإرضائكم وخدمتكم",
            "💙 عائلة {$companyName} تحتفل بكم! شكراً لثقتكم وحبكم الذي يدفعنا للتميز دائماً",
            "🤝 {$companyName} يقدر عملاءه الكرام! بفضل دعمكم المستمر نحقق إنجازات جديدة كل يوم",
            "🏆 {$companyName} معكم منذ سنوات! شكراً لكونكم شركاء النجاح والتميز في رحلتنا",
            "🌹 من كل فريق {$companyName}: شكراً لاختياركم الثقة بنا. نعدكم بخدمة أفضل دائماً"
        ];
        
        return $templates[array_rand($templates)];
    }
    

    
    // رسائل العروض
     private function generateOfferMessage($amount, $keywords, $type)
    {
        $companyName = "سامي نت";
        
        if ($amount) {
            // إذا كان المبلغ يحتوي على %
            if (strpos($type['original_prompt'], '%') !== false || strpos($type['original_prompt'], 'بالمية') !== false) {
                $templates = [
                    "🔥 عرض محدود من {$companyName}! خصم {$amount}% على جميع باقات الإنترنت. سارعوا قبل انتهاء الكمية!",
                    "⚡ {$companyName} يقدم: وفروا {$amount}% من فاتورتكم الشهرية! عرض لأول 100 مشترك فقط",
                    "🎁 مفاجأة من {$companyName}: خصم {$amount}% + شهر مجاني لكل عميل جديد. العرض ينتهي قريباً!",
                    "💰 {$companyName} - عرض الشتاء: تخفيض {$amount}% على الاشتراك السنوي. وفروا أكثر!"
                ];
            } else {
                $templates = [
                    "💳 {$companyName} - عرض خاص: باقة إنترنت كاملة بـ {$amount} شيكل فقط للشهر الأول!",
                    "🎉 {$companyName} يفاجئكم: ادفعوا {$amount} شيكل واحصلوا على 3 أشهر إنترنت مجاني!",
                    "⭐ عرض {$companyName} المحدود: وفروا {$amount} شيكل على باقة العائلة الكاملة",
                    "🚀 {$companyName} - فقط {$amount} شيكل للحصول على أسرع إنترنت في المنطقة!"
                ];
            }
        } else {
            $templates = [
                "🆓 {$companyName} - عروض حصرية! شهر مجاني لكل مشترك جديد + تركيب بدون رسوم",
                "🎁 {$companyName} يهديكم: باقة مضاعفة بنفس السعر! العدد محدود، سارعوا بالحجز",
                "🔥 عرض الأسبوع من {$companyName}: سرعة مضاعفة + راوتر مجاني + دعم VIP مجاناً!",
                "💝 مفاجأة {$companyName}: اشتركوا الآن واحصلوا على ترقية مجانية لأسرع باقة!",
                "⚡ {$companyName} - لا تفوتوا الفرصة: عروض خاصة لأول 50 عميل يتصل اليوم!"
            ];
        }
        
        return $templates[array_rand($templates)];
    }
    

    private function generateProblemMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        $isApology = $keywords['apologetic'] || strpos($type['original_prompt'], 'اعتذار') !== false;
        
        $templates = [
            'apology' => [
                "🙏 فريق {$companyName} يعتذر بشدة عن المشكلة التي واجهتموها. نعمل بأقصى جهد لحلها فوراً",
                "💔 {$companyName} آسف للإزعاج! نتفهم انزعاجكم ونعدكم بحل المشكلة في أسرع وقت ممكن",
                "😔 اعتذار صادق من {$companyName}: المشكلة الفنية ستُحل خلال ساعات قليلة. نقدر صبركم",
                "🤝 {$companyName} يعتذر ويتحمل المسؤولية كاملة. سنعوضكم عن أي إزعاج تعرضتم له"
            ],
            'solution' => [
                "✅ {$companyName} - تم حل المشكلة نهائياً! يمكنكم الآن استخدام الخدمة بشكل طبيعي",
                "💚 {$companyName} يبشركم: المشكلة الفنية محلولة والشبكة تعمل بكامل طاقتها الآن",
                "🌈 أخبار سارة من {$companyName}: عادت الأمور لطبيعتها وأضفنا تحسينات إضافية!",
                "🎉 {$companyName} - مشكلة محلولة + ترقية مجانية! شكراً لصبركم وتفهمكم الكريم"
            ],
            'investigation' => [
                "🔍 {$companyName} يحقق في المشكلة المبلغ عنها. سنعود إليكم بالتفاصيل والحل قريباً",
                "📋 تم تسجيل شكواكم في {$companyName}. فريقنا الفني يعمل على الحل بأولوية عالية",
                "⚙️ {$companyName} - نحن على علم بالمشكلة ونعمل عليها. سنرسل تحديثات منتظمة"
            ]
        ];
        
        $style = 'apology';
        if (strpos($type['original_prompt'], 'حل') !== false || strpos($type['original_prompt'], 'تم') !== false) {
            $style = 'solution';
        } elseif (strpos($type['original_prompt'], 'تحقيق') !== false || strpos($type['original_prompt'], 'نتابع') !== false) {
            $style = 'investigation';
        }
        
        $options = $templates[$style];
        return $options[array_rand($options)];
    }
    

    
    // رسائل المناسبات
      private function generateHolidayMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        $currentMonth = date('n');
        
        $holidays = [
            'ramadan' => [
                "🌙 {$companyName} يهنئكم برمضان المبارك! شهر كريم مليء بالخير والبركات عليكم وعلى الأحباب",
                "🕌 رمضان مبارك من عائلة {$companyName}! نتمنى لكم صياماً مقبولاً وأياماً مباركة",
                "✨ {$companyName} - رمضان كريم! شهر الخير والمغفرة حل علينا، كل عام وأنتم بألف خير"
            ],
            'eid' => [
                "🎈 {$companyName} يهنئكم بالعيد السعيد! كل عام وأنتم بخير وصحة وسعادة مع الأحباب",
                "🎁 عيد مبارك من كل فريق {$companyName}! أيام سعيدة وذكريات جميلة في انتظاركم",
                "🌟 {$companyName} - عيدكم سعيد! نتمنى لكم الفرح والسرور في هذه الأيام المباركة"
            ],
            'new_year' => [
                "🎊 {$companyName} يهنئكم بالعام الجديد! سنة سعيدة مليئة بالنجاحات والإنجازات",
                "🌟 عام جديد سعيد من {$companyName}! نتطلع لخدمتكم بتميز أكبر في العام القادم",
                "🎉 {$companyName} - كل عام وأنتم بخير! عام جديد بفرص جديدة وإنجازات أكبر"
            ],
            'general' => [
                "🎊 {$companyName} يشارككم الفرحة في هذه المناسبة السعيدة! أجمل التهاني والتبريكات",
                "💝 مناسبة سعيدة من {$companyName}! نتمنى لكم أوقاتاً جميلة وذكريات لا تُنسى",
                "🌹 {$companyName} يهنئكم بهذه المناسبة الكريمة! كل عام وأنتم بألف خير وسعادة"
            ]
        ];
        
        // تحديد نوع المناسبة
        $prompt = strtolower($type['original_prompt']);
        $holidayType = 'general';
        
        if (strpos($prompt, 'رمضان') !== false) $holidayType = 'ramadan';
        elseif (strpos($prompt, 'عيد') !== false) $holidayType = 'eid';
        elseif (strpos($prompt, 'عام') !== false || strpos($prompt, 'سنة') !== false) $holidayType = 'new_year';
        
        $templates = $holidays[$holidayType];
        return $templates[array_rand($templates)];
    }

    private function generateSupportMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        
        $templates = [
            "🛟 فريق {$companyName} هنا لمساعدتكم 24/7! أي استفسار أو مساعدة، نحن في الخدمة دائماً",
            "📞 {$companyName} - خدمة العملاء: اتصلوا بنا في أي وقت، فريقنا جاهز لحل جميع استفساراتكم",
            "💬 {$companyName} يقدم دعماً فنياً متميزاً! تواصلوا معنا عبر الهاتف أو الواتساب",
            "🎧 دعم {$companyName} المتاح دائماً: خبراء تقنيون جاهزون لمساعدتكم في أي وقت تحتاجونه",
            "🔧 فريق الدعم الفني في {$companyName} مستعد! حلول سريعة وفعالة لجميع استفساراتكم التقنية"
        ];
        
        return $templates[array_rand($templates)];
    }

      private function analyzeAdvancedContext($prompt)
    {
        $contexts = [];
        
        // كلمات تجارية
        $businessWords = ['شركة', 'خدمة', 'عمل', 'business', 'service', 'company', 'تجاري', 'مهني'];
        foreach ($businessWords as $word) {
            if (strpos($prompt, $word) !== false) $contexts['business'] = ($contexts['business'] ?? 0) + 1;
        }
        
        // كلمات شخصية/ودية
        $personalWords = ['أهل', 'عائلة', 'شخصي', 'حبيبي', 'صديق', 'personal', 'family', 'friend'];
        foreach ($personalWords as $word) {
            if (strpos($prompt, $word) !== false) $contexts['personal'] = ($contexts['personal'] ?? 0) + 1;
        }
        
        // كلمات معلوماتية
        $infoWords = ['معلومات', 'تفاصيل', 'info', 'details', 'شرح', 'كيف', 'ماذا', 'متى', 'أين'];
        foreach ($infoWords as $word) {
            if (strpos($prompt, $word) !== false) $contexts['informative'] = ($contexts['informative'] ?? 0) + 1;
        }
        
        // كلمات تحفيزية
// كلمات تحفيزية
$motivationalWords = ['تقدم', 'نجاح', 'أفضل']; // انتهى تعريف الكلمات
    }    
    // رسائل مخصصة
 private function generateCustomMessage($prompt, $keywords, $type)
    {
        $companyName = "سامي نت";
        $prompt = strtolower($prompt);
        
        // تحليل أكثر تفصيلاً للسياق
        $context = $this->analyzeAdvancedContext($prompt);
        
        // قوالب حسب السياق المحدد
        $contextTemplates = [
            // رسائل تجارية عامة
            'business_general' => [
                "💼 {$companyName} في خدمتكم دائماً! نحرص على تقديم أفضل الحلول التقنية لعملائنا الكرام",
                "🏢 {$companyName} يتميز بالجودة والاحترافية في جميع خدماته. ثقتكم تدفعنا للأفضل",
                "⭐ اختاروا {$companyName} للتميز: خدمة عملاء ممتازة، حلول تقنية متطورة، وأسعار منافسة"
            ],
            
            // رسائل ودية شخصية
            'friendly_personal' => [
                "😊 أهلاً وسهلاً! {$companyName} سعيد بوجودكم معنا ونتطلع لخدمتكم بكل محبة واهتمام",
                "🤗 مرحباً بكم في عائلة {$companyName} الكبيرة! نحن هنا لنكون أقرب لكم من أي شركة أخرى",
                "❤️ {$companyName} يعاملكم كأفراد العائلة! راحتكم ورضاكم هو هدفنا الأول والأخير"
            ],
            
            // رسائل معلوماتية
            'informative' => [
                "📋 {$companyName} يقدم لكم المعلومات التي تحتاجونها. للاستفسارات، تواصلوا معنا في أي وقت",
                "📞 للحصول على معلومات مفصلة عن خدمات {$companyName}، اتصلوا بنا أو زوروا موقعنا",
                "💡 {$companyName} - مصدركم الموثوق للمعلومات التقنية والاستشارات المتخصصة"
            ],
            
            // رسائل تحفيزية
            'motivational' => [
                "🚀 {$companyName} يؤمن بقدراتكم! معاً نحو مستقبل رقمي أفضل وتجربة إنترنت استثنائية",
                "⚡ لا تتوقفوا عن التقدم! {$companyName} يدعم طموحاتكم بأحدث تقنيات الاتصال",
                "🌟 {$companyName} يلهمكم للوصول أعلى! سرعة فائقة وإمكانيات لا محدودة في انتظاركم"
            ],
            
            // رسائل تقديرية
            'appreciative' => [
                "🙏 {$companyName} يقدر اختياركم له من بين الشركات الكثيرة. هذه الثقة شرف كبير لنا",
                "💎 عملاء {$companyName} هم الأغلى! نعتبركم شركاء نجاح وليس مجرد زبائن",
                "🏆 {$companyName} فخور بعملائه المميزين! كل واحد منكم يستحق أفضل ما لدينا"
            ]
        ];
        
        // اختيار النمط المناسب
        $selectedStyle = $this->selectContextStyle($context, $keywords);
        $templates = $contextTemplates[$selectedStyle];
        
        return $templates[array_rand($templates)];
    }
private function selectContextStyle($context, $keywords)
{
    // مثال مبسط: اختر النمط الأكثر تكراراً في السياق
    if (!empty($context)) {
        arsort($context); // ترتيب حسب القيمة تنازلياً
        return key($context); // إرجاع النمط الأعلى
    }

    // fallback إذا لم يوجد سياق
    return 'business_general';
}

        private function generateUpgradeMessage($amount, $keywords, $type)
    {
        $companyName = "سامي نت";
        
        if ($amount) {
            $templates = [
                "⬆️ {$companyName} - ترقية مميزة: احصلوا على سرعة مضاعفة بإضافة {$amount} شيكل فقط شهرياً!",
                "🚀 {$companyName} يقدم ترقية الباقة: +{$amount} شيكل = سرعة أعلى + مزايا إضافية رائعة",
                "💎 ترقية VIP من {$companyName}: باقة متطورة بـ {$amount} شيكل إضافية تشمل مزايا حصرية"
            ];
        } else {
            $templates = [
                "⚡ {$companyName} - حان وقت الترقية! باقات أسرع ومزايا أكثر في انتظاركم",
                "📶 {$companyName} يدعوكم للترقية: سرعات أعلى، ثبات أكبر، وخدمات متطورة",
                "🌟 {$companyName} - ترقوا تجربتكم! باقات محدثة تواكب احتياجاتكم المتزايدة"
            ];
        }
        
        return $templates[array_rand($templates)];
    }
    
    // رسائل الشكاوى - سامي نت يحل مشاكلكم
    private function generateComplaintMessage($keywords, $type)
    {
        $companyName = "سامي نت";
        
        $templates = [
            "📝 {$companyName} يتلقى شكواكم بجدية تامة. فريق متخصص سيراجع الموضوع ويتواصل معكم قريباً",
            "🎯 شكواكم وصلت لإدارة {$companyName}. نعدكم بمراجعة شاملة وحل جذري للمشكلة",
            "⚖️ {$companyName} يقدر ملاحظاتكم. سنحقق في الموضوع ونضمن عدم تكراره مستقبلاً",
            "🔍 فريق الجودة في {$companyName} سيدرس شكواكم بعناية. هدفنا رضاكم التام عن خدماتنا",
            "💼 {$companyName} يأخذ شكواكم على محمل الجد. مدير العلاقات سيتصل بكم خلال 24 ساعة"
        ];
        
        return $templates[array_rand($templates)];
    }
        private function generateRenewalMessage($amount, $keywords, $type)
    {
        $companyName = "سامي نت";
        
        if ($amount) {
            $templates = [
                "📅 {$companyName} يذكركم: موعد تجديد اشتراككم بمبلغ {$amount} شيكل اقترب. جددوا الآن لضمان الاستمرارية",
                "🔄 تجديد اشتراك {$companyName}: مبلغ {$amount} شيكل لتجديد خدمة الإنترنت لشهر إضافي",
                "⏰ {$companyName} - تذكير تجديد: باقتكم تنتهي قريباً، جددوا بـ {$amount} شيكل وواصلوا التمتع بالخدمة"
            ];
        } else {
            $templates = [
                "📋 {$companyName} يذكركم بموعد تجديد الاشتراك. تواصلوا معنا لمعرفة العروض والباقات المتاحة",
                "🔄 وقت التجديد من {$companyName}! اختاروا من باقاتنا المتنوعة وجددوا بأفضل الأسعار",
                "⭐ {$companyName} - تجديد الاشتراك: عروض خاصة للتجديد المبكر، تواصلوا معنا الآن!"
            ];
        }
        
        return $templates[array_rand($templates)];
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