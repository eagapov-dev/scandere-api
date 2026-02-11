<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailService
{
    /**
     * Generate a temporary signed download URL for a product.
     *
     * @param Product $product
     * @param User $user
     * @param int $days Number of days the URL is valid (default: 7)
     * @return string
     */
    public function generateDownloadUrl(Product $product, User $user, int $days = 7): string
    {
        return URL::temporarySignedRoute(
            'product.download',
            now()->addDays($days),
            [
                'product' => $product->id,
                'user' => $user->id
            ]
        );
    }

    /**
     * Get the admin email address.
     *
     * @return string
     */
    public function getAdminEmail(): string
    {
        return config('services.admin.email', 'admin@scandereai.store');
    }

    /**
     * Get the admin notification email address.
     *
     * @return string|null
     */
    public function getAdminNotificationEmail(): ?string
    {
        return config('services.admin.notification_email');
    }

    /**
     * Format unsubscribe URL for newsletter emails.
     *
     * @param string $email
     * @return string
     */
    public function formatUnsubscribeUrl(string $email): string
    {
        return config('app.frontend_url') . '/unsubscribe/' . urlencode($email);
    }

    /**
     * Get product URL for frontend.
     *
     * @param string $slug
     * @return string
     */
    public function getProductUrl(string $slug): string
    {
        return config('app.frontend_url') . '/products/' . $slug;
    }

    /**
     * Get frontend URL.
     *
     * @param string $path
     * @return string
     */
    public function getFrontendUrl(string $path = ''): string
    {
        $baseUrl = config('app.frontend_url');

        if (empty($path)) {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Sanitize email content to prevent XSS.
     *
     * @param string $content
     * @return string
     */
    public function sanitizeContent(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email address.
     *
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
