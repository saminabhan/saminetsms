<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected $with = ['messages']; // لتحميل الرسائل مع المشتركين
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getFormattedPhoneAttribute()
    {
        // تنظيف رقم الهاتف وإضافة كود الدولة إذا لم يكن موجوداً
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // إذا كان الرقم يبدأ بـ 0، نقوم بحذفها وإضافة كود فلسطين
        if (substr($phone, 0, 1) === '0') {
            $phone = '970' . substr($phone, 1);
        }
        
        return $phone;
    }
}
