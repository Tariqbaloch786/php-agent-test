<?php

declare(strict_types=1);

namespace App\Billing;

use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

/**
 * Refunds of orders paid through Stripe Checkout in subscription mode: an order keeps its Checkout
 * Session id, and a refund needs the PaymentIntent that paid the session's first invoice.
 */
final class Refunds
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    /** Refunds `$amount` of an order and returns the refund's id. */
    public function refundOrder(string $sessionId, int $amount): string
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['invoice.payment_intent']]);
        $paymentIntent = self::paymentIntentOf($session);
        if ($paymentIntent === null) {
            throw new \RuntimeException(sprintf('Checkout Session %s has no paid invoice to refund', $sessionId));
        }
        $refund = $this->stripe->refunds->create(['payment_intent' => $paymentIntent, 'amount' => $amount]);

        return $refund->id;
    }

    /** The id of the PaymentIntent behind a subscription Checkout Session's invoice; null while there is none. */
    public static function paymentIntentOf(Session $session): ?string
    {
        $invoice = $session->invoice;
        if (!$invoice instanceof Invoice) {
            return null;
        }
        $paymentIntent = $invoice->payment_intent;
        if ($paymentIntent instanceof PaymentIntent) {
            return $paymentIntent->id;
        }

        return is_string($paymentIntent) ? $paymentIntent : null;
    }
}
