<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePaymentService
{
    private string $stripeSecretKey;

    public function __construct(string $stripeSecretKey)
    {
        $this->stripeSecretKey = $stripeSecretKey;
    }

    public function createCheckoutSession(array $cartItems): Session
    {
        Stripe::setApiKey($this->stripeSecretKey);

        $lineItems = [];
        foreach ($cartItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => $item['name']],
                    'unit_amount'  => $item['price'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => 'https://ton-site.com/success',
            'cancel_url'           => 'https://ton-site.com/cancel',
        ]);
    }
}
