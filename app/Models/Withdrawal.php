<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_type',
        'category_id',
        'source',
        'user_id',
        'amount',
        'withdrawn_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withdrawn_at' => 'date',
    ];
}


