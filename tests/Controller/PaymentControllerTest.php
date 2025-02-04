<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PaymentControllerTest extends WebTestCase
{
    public function testCheckoutPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Finaliser votre commande');
    }

    public function testCreateCheckoutSession(): void
    {
        $client = static::createClient();
        $session = new Session(new MockArraySessionStorage()); // 📌 Création d'une session mock

        // ✅ Ajouter un produit dans le panier simulé
        $cartData = [
            1 => ['name' => 'Produit Test', 'price' => 2999, 'quantity' => 1]
        ];
        $session->set('cart', $cartData);
        $session->save();

        // ✅ Injecter la session dans le client Symfony
        $client->getContainer()->set('session', $session);

        // ✅ Vérifier que le panier est bien rempli avant la requête
        $this->assertNotEmpty($session->get('cart'), "Le panier ne doit pas être vide avant la requête.");

        // ✅ Envoyer une requête POST vers `/create-checkout-session`
        $client->request('POST', '/create-checkout-session', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(array_values($cartData))); // 🔥 Envoi du panier sous forme de JSON

        // ✅ Vérifier que la réponse est correcte
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData, "La réponse doit contenir un ID de session Stripe.");
    }
}
