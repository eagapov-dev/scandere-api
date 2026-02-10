<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'message', 'subscribe_newsletter', 'is_read'];
    protected function casts(): array { return ['subscribe_newsletter' => 'boolean', 'is_read' => 'boolean']; }
}
