<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationLink extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'url', 'location', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHeader($query)
    {
        return $query->where('location', 'header');
    }

    public function scopeFooter($query)
    {
        return $query->where('location', 'footer');
    }
}
