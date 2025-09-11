<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'name',
        'name_ar',
        'description',
        'price',
        'speed',
        'duration_hours',
        'duration_days',
        'data_limit',
        'is_active',
        'allow_quantity'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
        'allow_quantity' => 'boolean',
    ];

    // العلاقات
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ش.ج';
    }

    public function getDurationTextAttribute()
    {
        $text = '';
        if ($this->duration_hours) {
            $text .= $this->duration_hours . ' ساعة';
        }
        if ($this->duration_days) {
            if ($text) $text .= ' / ';
            $text .= $this->duration_days . ' يوم';
        }
        return $text;
    }

    public function getFullDescriptionAttribute()
    {
        $desc = $this->name_ar;
        if ($this->speed) $desc .= ' - ' . $this->speed;
        if ($this->data_limit) $desc .= ' - ' . $this->data_limit;
        if ($this->getDurationTextAttribute()) $desc .= ' - ' . $this->getDurationTextAttribute();
        return $desc;
    }
}