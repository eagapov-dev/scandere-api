<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;
    protected $fillable = ['email', 'first_name', 'last_name', 'source', 'ip_address', 'subscribed_at', 'unsubscribed_at'];
    protected function casts(): array { return ['subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime']; }
    public function scopeActive($q) { return $q->whereNull('unsubscribed_at'); }
}
