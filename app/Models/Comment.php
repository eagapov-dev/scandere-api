<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'body', 'is_approved'];
    protected function casts(): array { return ['is_approved' => 'boolean']; }

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function scopeApproved($q) { return $q->where('is_approved', true); }
}
