<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_id',
        'message_content',
        'status',
        'api_response'
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'sent' => '<span class="badge bg-success">تم الإرسال</span>',
            'failed' => '<span class="badge bg-danger">فشل الإرسال</span>',
            'pending' => '<span class="badge bg-warning">في الانتظار</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">غير معروف</span>';
    }
}
