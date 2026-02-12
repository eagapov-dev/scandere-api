<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'is_admin'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function orders() { return $this->hasMany(Order::class); }
    public function cart() { return $this->hasMany(CartItem::class); }
    public function cartItems() { return $this->hasMany(CartItem::class); }
    public function comments() { return $this->hasMany(Comment::class); }

    public function isAdmin(): bool { return $this->is_admin; }

    public function hasPurchased(Product $product): bool
    {
        return $this->orders()
            ->where('status', 'completed')
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->exists();
    }

    public function purchasedProductIds(): array
    {
        return $this->orders()
            ->where('status', 'completed')
            ->with('items:order_id,product_id')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }
}
