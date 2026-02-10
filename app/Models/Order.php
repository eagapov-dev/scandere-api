<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'payment_gateway', 'payment_id', 'status', 'paid_at', 'payment_meta'];
    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'paid_at' => 'datetime', 'payment_meta' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }

    public function scopeCompleted($q) { return $q->where('status', 'completed'); }

    public function markAsCompleted(string $paymentId): void
    {
        $this->update([
            'status' => 'completed',
            'payment_id' => $paymentId,
            'paid_at' => now(),
        ]);
    }
}
