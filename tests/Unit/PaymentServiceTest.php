<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
    }

    public function test_can_calculate_cart_total(): void
    {
        $items = [
            ['price' => 10.00, 'quantity' => 2],
            ['price' => 15.50, 'quantity' => 1],
        ];

        $total = $this->paymentService->calculateTotal($items);

        $this->assertEquals(35.50, $total);
    }

    public function test_can_format_amount_for_stripe(): void
    {
        $amount = 29.99;

        $formatted = $this->paymentService->formatAmountForStripe($amount);

        $this->assertEquals(2999, $formatted);
    }

    public function test_validates_product_availability(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $isAvailable = $this->paymentService->isProductAvailable($product);

        $this->assertTrue($isAvailable);
    }

    public function test_inactive_product_is_not_available(): void
    {
        $product = Product::factory()->inactive()->create();

        $isAvailable = $this->paymentService->isProductAvailable($product);

        $this->assertFalse($isAvailable);
    }
}
