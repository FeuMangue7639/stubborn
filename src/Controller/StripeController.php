<?php

namespace App\Controller;

use App\Service\StripePaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(StripePaymentService $stripePaymentService, Request $request): JsonResponse
    {
        $cart = $request->toArray(); // Récupère les données du panier envoyées par AJAX

        if (empty($cart)) {
            return $this->json(['error' => 'Panier vide'], 400);
        }

        $session = $stripePaymentService->createCheckoutSession($cart);

        return $this->json(['id' => $session->id]);
    }
}
