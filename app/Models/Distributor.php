<?php
// app/Models/Distributor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'type',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function distributorCards()
    {
        return $this->hasMany(DistributorCard::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // الـ Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDistributors($query)
    {
        return $query->where('type', 'distributor');
    }

    public function scopeSalesPoints($query)
    {
        return $query->where('type', 'sales_point');
    }

    // الدوال المساعدة
    public function getTotalCardsAttribute()
    {
        return $this->distributorCards->sum('quantity_received');
    }

    public function getAvailableCardsAttribute()
    {
        return $this->distributorCards->sum('quantity_available');
    }

    public function getSoldCardsAttribute()
    {
        return $this->distributorCards->sum('quantity_sold');
    }

    public function getTotalAmountAttribute()
    {
        return $this->distributorCards->sum('total_amount');
    }

    public function getPaidAmountAttribute()
    {
        return $this->distributorCards->sum('paid_amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getPaymentStatusAttribute()
    {
        $total = $this->total_amount;
        $paid = $this->paid_amount;

        if ($paid == 0) {
            return 'unpaid';
        } elseif ($paid >= $total) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

    // دالة للحصول على الكروت حسب الخدمة
    public function getCardsByService($serviceId)
    {
        return $this->distributorCards()->where('service_id', $serviceId)->first();
    }

    // دالة لإضافة كروت جديدة
    public function addCards($serviceId, $quantity, $cardPrice, $receivedAt = null, $notes = null)
    {
        $existingCard = $this->getCardsByService($serviceId);
        $totalAmount = $quantity * $cardPrice;

        if ($existingCard) {
            // إضافة للكروت الموجودة
            $existingCard->update([
                'quantity_received' => $existingCard->quantity_received + $quantity,
                'quantity_available' => $existingCard->quantity_available + $quantity,
                'total_amount' => $existingCard->total_amount + $totalAmount,
                'notes' => $notes ?: $existingCard->notes
            ]);
            $existingCard->updatePaymentStatus();
            return $existingCard;
        } else {
            // إنشاء سجل جديد
            return $this->distributorCards()->create([
                'service_id' => $serviceId,
                'quantity_received' => $quantity,
                'quantity_available' => $quantity,
                'card_price' => $cardPrice,
                'total_amount' => $totalAmount,
                'received_at' => $receivedAt ?: now()->format('Y-m-d'),
                'user_id' => auth()->id(),
                'notes' => $notes
            ]);
        }
    }
}