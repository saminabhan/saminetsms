<?php
// app/Models/SubscriberBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_id',
        'balance',
        'total_invoices',
        'total_payments',
        'last_updated'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_invoices' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'last_updated' => 'timestamp',
    ];

    // العلاقات
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    // Scopes
    public function scopeDebtors($query)
    {
        return $query->where('balance', '<', 0);
    }

    public function scopeCreditors($query)
    {
        return $query->where('balance', '>', 0);
    }

    // Accessors
    public function getBalanceTextAttribute()
    {
        if ($this->balance > 0) {
            return 'دائن بمبلغ ' . number_format($this->balance, 2) . ' ش.ج';
        } elseif ($this->balance < 0) {
            return 'مدين بمبلغ ' . number_format(abs($this->balance), 2) . ' ش.ج';
        } else {
            return 'متوازن';
        }
    }

    public function getBalanceStatusAttribute()
    {
        if ($this->balance > 0) {
            return 'creditor'; // دائن
        } elseif ($this->balance < 0) {
            return 'debtor'; // مدين
        } else {
            return 'balanced'; // متوازن
        }
    }

    // Methods
    public function updateBalance()
    {
        $this->total_invoices = $this->subscriber->invoices()->sum('final_amount');
        $this->total_payments = $this->subscriber->invoices()->sum('paid_amount');
        $this->balance = $this->total_payments - $this->total_invoices;
        $this->last_updated = now();
        $this->save();
    }

    public static function updateOrCreateForSubscriber($subscriberId)
    {
        $balance = self::firstOrCreate(['subscriber_id' => $subscriberId]);
        $balance->updateBalance();
        return $balance;
    }
    
}