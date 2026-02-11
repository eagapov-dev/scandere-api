<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id', 'title', 'slug', 'short_description', 'description',
        'price', 'is_free', 'is_active', 'show_on_homepage', 'file_path', 'file_name',
        'file_size', 'file_type', 'preview_image', 'download_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function bundles() { return $this->belongsToMany(Bundle::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeOnHomepage($q) { return $q->where('show_on_homepage', true); }
    // Alias for backward compatibility
    public function scopeFeatured($q) { return $q->where('show_on_homepage', true); }

    public function getFormattedPriceAttribute(): string
    {
        return $this->is_free ? 'Free' : '$' . number_format($this->price, 2);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
