<?php

declare(strict_types=1);

namespace Tests;

use App\Billing\Refunds;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

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
                'payments' => [
                    'data' => [
                        [
                            'payment' => [
                                'payment_intent' => PaymentIntent::constructFrom([
                                    'id' => 'pi_test_1',
                                    'object' => 'payment_intent',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame('pi_test_1', Refunds::paymentIntentOf($session));
    }

    public function testNoPaymentIntentBeforeTheInvoiceExists(): void
    {
        $session = Session::constructFrom(['id' => 'cs_test_2', 'object' => 'checkout.session', 'mode' => 'subscription', 'invoice' => null]);

        self::assertNull(Refunds::paymentIntentOf($session));
    }

    public function testNoPaymentIntentWhileTheInvoiceHasNoPayments(): void
    {
        // An open invoice: nothing has paid it yet. Before API version 2025-03-31.basil it carries
        // payment_intent null; from that version on, an empty list of payments.
        $session = Session::constructFrom([
            'id' => 'cs_test_3',
            'object' => 'checkout.session',
            'mode' => 'subscription',
            'invoice' => [
                'id' => 'in_test_3',
                'object' => 'invoice',
                'payment_intent' => null,
                'payments' => ['object' => 'list', 'data' => []],
            ],
        ]);

        self::assertNull(Refunds::paymentIntentOf($session));
    }
}
