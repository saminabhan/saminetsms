<?php
// app/Models/DistributorCard.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'service_id',
        'quantity_received',
        'quantity_sold',
        'quantity_available',
        'card_price',
        'total_amount',
        'paid_amount',
        'payment_status',
        'received_at',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'card_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'received_at' => 'date',
    ];

    // العلاقات
    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // الدوال المساعدة
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function updatePaymentStatus()
    {
        $paid = $this->paid_amount;
        $total = $this->total_amount;

        if ($paid == 0) {
            $status = 'unpaid';
        } elseif ($paid >= $total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $this->update(['payment_status' => $status]);
        return $status;
    }

    // دالة بيع كروت
    public function sellCards($quantity)
    {
        if ($quantity > $this->quantity_available) {
            throw new \Exception('الكمية المطلوبة أكبر من الكمية المتاحة');
        }

        $this->quantity_sold += $quantity;
        $this->quantity_available -= $quantity;
        $this->save();

        return $this;
    }

    // دالة إضافة دفعة
    public function addPayment($amount)
    {
        if ($amount > $this->remaining_amount) {
            throw new \Exception('مبلغ الدفعة أكبر من المبلغ المتبقي');
        }

        $this->paid_amount += $amount;
        $this->updatePaymentStatus();
        $this->save();

        return $this;
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeHasAvailableCards($query)
    {
        return $query->where('quantity_available', '>', 0);
    }
    
}