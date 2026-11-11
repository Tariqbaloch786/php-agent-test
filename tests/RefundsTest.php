<?php

declare(strict_types=1);

namespace Tests;

use App\Billing\Refunds;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;

final class RefundsTest extends TestCase
{
    public function testReadsThePaymentIntentOfTheExpandedInvoice(): void
    {
        $session = Session::constructFrom([
            'id' => 'cs_test_1',
            'object' => 'checkout.session',
            'mode' => 'subscription',
            'invoice' => [
                'id' => 'in_test_1',
                'object' => 'invoice',
                'payment_intent' => ['id' => 'pi_test_1', 'object' => 'payment_intent'],
            ],
        ]);

        self::assertSame('pi_test_1', Refunds::paymentIntentOf($session));
    }

    public function testNoPaymentIntentBeforeTheInvoiceExists(): void
    {
        $session = Session::constructFrom(['id' => 'cs_test_2', 'object' => 'checkout.session', 'mode' => 'subscription', 'invoice' => null]);

        self::assertNull(Refunds::paymentIntentOf($session));
    }
}
