<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'og_image'];
    protected function casts(): array { return ['is_active' => 'boolean']; }

    protected static function booted(): void
    {
        static::creating(fn(Category $c) => $c->slug = $c->slug ?: Str::slug($c->name));
    }

    public function products() { return $this->hasMany(Product::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
