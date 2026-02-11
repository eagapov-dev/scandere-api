<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'body', 'answer', 'status', 'show_on_homepage'];
    protected function casts(): array {
        return [
            'status' => 'string',
            'show_on_homepage' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function scopePublished($q) { return $q->where('status', 'published'); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }
    public function scopeOnHomepage($q) { return $q->where('show_on_homepage', true); }
}
