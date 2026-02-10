<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'price', 'original_price', 'is_active'];
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'original_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function products() { return $this->belongsToMany(Product::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }

    public function getSavingsAttribute(): string
    {
        return '$' . number_format($this->original_price - $this->price, 2);
    }
}
