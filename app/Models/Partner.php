<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'share_percentage',
        'is_active',
    ];

    protected $casts = [
        'share_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}


