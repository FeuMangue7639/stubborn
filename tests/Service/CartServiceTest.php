<?php

namespace App\Tests\Service;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class CartServiceTest extends KernelTestCase
{
    private CartService $cartService;
    private SessionInterface $session;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage()); // 📌 Session Mock
        $this->cartService = new CartService($this->session);
    }

    public function testAddToCart(): void
    {
        $this->cartService->add(1, 2);
        $cart = $this->session->get('cart');

        $this->assertArrayHasKey(1, $cart);
        $this->assertEquals(2, $cart[1]); 
    }

    public function testUpdateCart(): void
    {
        $this->cartService->add(1, 1);
        $this->cartService->update(1, 5);

        $cart = $this->session->get('cart');
        $this->assertEquals(5, $cart[1]);
    }

    public function testRemoveFromCart(): void
    {
        $this->cartService->add(1, 1);
        $this->cartService->remove(1);

        $cart = $this->session->get('cart');
        $this->assertArrayNotHasKey(1, $cart);
    }

    public function testGetTotal(): void
    {
        $this->cartService->add(1, 2);
        $this->cartService->add(2, 1);

        $products = [
            1 => ['price' => 1000], // 10€
            2 => ['price' => 500],  // 5€
        ];

        $total = $this->cartService->getTotal($products);
        $this->assertEquals(2500, $total); // 20€ + 5€ = 25€
    }
}
