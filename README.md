# billing (API Update Agent demo, PHP)

Refunds of subscription orders paid through Stripe Checkout, on `stripe/stripe-php` 16 (locked at
16.6.0, which sends Stripe API 2025-02-24.acacia by default), checked by PHPStan and PHPUnit in CI.

It exists so API Update Agent's PHP support can be shown end to end on the change real projects made
for Stripe's 2025-03-31.basil release, where `Invoice.payment_intent` was removed in favour of the
invoice's payments (the same migration as `Sylius/StripePlugin` commit 38d0afe, "Upgrade
stripe/stripe-php to ^17.0"; this repository's code is its own). The agent moves stripe-php with
Composer (`composer.json` and `composer.lock`) and the repository's own CI judges the fix.
