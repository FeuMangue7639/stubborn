<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'checkout_page')]
    public function checkoutPage(SessionInterface $session): Response
    {
        // Récupération du panier en session
        $cart = $session->get('cart', []);

        // Calcul du total en centimes (évite les erreurs de conversion)
        $total = 0;
        foreach ($cart as $item) {
            $total += intval($item['price'] * 100) * $item['quantity']; // Convertir en centimes et s'assurer que c'est un entier
        }

        return $this->render('cart/checkout.html.twig', [
            'stripe_public_key' => $this->getParameter('stripe.public_key'),
            'total' => $total // Envoi du total en centimes
        ]);
    }

    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(SessionInterface $session): JsonResponse
    {
        Stripe::setApiKey($this->getParameter('stripe.secret_key'));

        try {
            $cart = $session->get('cart', []);

            $lineItems = [];
            if (!$cart) {
                $cart[] = ['name' => 'Produit Test', 'price' => 5000, 'quantity' => 1];
            }
            foreach ($cart as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['name'],
                        ],
                        'unit_amount' => intval($item['price'] * 100), // Correction : s'assurer que c'est un entier
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('payment_success', [], 0),
                'cancel_url' => $this->generateUrl('payment_cancel', [], 0),
            ]);

            return new JsonResponse(['id' => $session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur Stripe : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        return $this->render('cart/payment_success.html.twig');
    }

    #[Route('/payment-cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('cart/payment_cancel.html.twig');
    }
}
