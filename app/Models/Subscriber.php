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

    public function invoices()
{
    return $this->hasMany(Invoice::class);
}

public function balance()
{
    return $this->hasOne(SubscriberBalance::class);
}

// Scopes
public function scopeWithDebts($query)
{
    return $query->whereHas('balance', function($q) {
        $q->where('balance', '<', 0);
    });
}

public function scopeWithCredits($query)
{
    return $query->whereHas('balance', function($q) {
        $q->where('balance', '>', 0);
    });
}

// Accessors
public function getCurrentBalanceAttribute()
{
    if (!$this->balance) {
        SubscriberBalance::updateOrCreateForSubscriber($this->id);
        $this->load('balance');
    }
    return $this->balance->balance ?? 0;
}

public function getTotalInvoicesAttribute()
{
    return $this->invoices()->sum('final_amount');
}

public function getTotalPaidAttribute()
{
    return $this->invoices()->sum('paid_amount');
}

public function getOutstandingAmountAttribute()
{
    return $this->invoices()->sum('final_amount') - $this->invoices()->sum('paid_amount');
}

// Methods
public function updateBalance()
{
    return SubscriberBalance::updateOrCreateForSubscriber($this->id);
}
}
