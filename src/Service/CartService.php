<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->session->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        $this->session->set('cart', $cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->session->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId] = $quantity;
        }

        $this->session->set('cart', $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->session->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $this->session->set('cart', $cart);
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function clearCart(): void
    {
        $this->session->remove('cart');
    }

    public function getTotal(array $products): int
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $total += $products[$productId]['price'] * $quantity;
            }
        }

        return $total;
    }
}
