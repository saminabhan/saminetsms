<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'subscriber_id',
        'service_id',
        'user_id',
        'original_price',
        'discount_amount',
        'final_amount',
        'paid_amount',
        'service_start_date',
        'service_end_date',
        'status',
        'payment_status',
        'notes',
         'client_type',       // جديد
    'distributor_id',    // جديد
    'distributor_card_id' // جديد
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'service_start_date' => 'date',
        'service_end_date' => 'date',
    ];

    public function distributorCard()
{
    return $this->belongsTo(DistributorCard::class, 'distributor_card_id');
}

    // العلاقات
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('service_end_date', '<', now())
                    ->whereIn('payment_status', ['unpaid', 'partial']);
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->final_amount - $this->paid_amount;
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'معلقة',
            'paid' => 'مدفوعة',
            'partially_paid' => 'مدفوعة جزئياً',
            'cancelled' => 'ملغاة',
            default => 'غير محدد'
        };
    }

    public function getPaymentStatusTextAttribute()
    {
        return match($this->payment_status) {
            'unpaid' => 'غير مدفوعة',
            'paid' => 'مدفوعة',
            'partial' => 'مدفوعة جزئياً',
            default => 'غير محدد'
        };
    }

    // Methods
      public static function generateInvoiceNumber()
    {
    $lastInvoice = self::orderBy('id', 'desc')->first();
    $number = $lastInvoice ? $lastInvoice->id + 1 : 1;
    return 'INV-' . date('Y') . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    

    public function calculateEndDate()
    {
        $startDate = Carbon::parse($this->service_start_date);
        
        if ($this->service->duration_hours) {
            return $startDate->addHours($this->service->duration_hours);
        }
        
        if ($this->service->duration_days) {
            return $startDate->addDays($this->service->duration_days);
        }
        
        return $startDate->addDays(30); // افتراضي شهر
    }

public function payments()
{
     return $this->hasMany(Payment::class);
}



    public function updatePaymentStatus()
    {
        if ($this->paid_amount >= $this->final_amount) {
            $this->payment_status = 'paid';
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
            $this->status = 'partially_paid';
        } else {
            $this->payment_status = 'unpaid';
            $this->status = 'pending';
        }
        
        $this->save();
    }

     public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'distributor_id');
    }
}