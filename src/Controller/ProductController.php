<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(EntityManagerInterface $entityManager): Response
    {
        // Récupère les produits mis en avant
        $products = $entityManager->getRepository(Product::class)->findBy(['isHighlighted' => true]);

        return $this->render('product/home.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/products', name: 'product_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        // Récupère tous les produits
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'product_detail')]
    public function detail(Product $product): Response
    {
        return $this->render('product/detail.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/cart', name: 'cart')]
    public function cart(SessionInterface $session): Response
    {
        // Récupère le panier depuis la session
        $cart = $session->get('cart', []);

        // Calcul du total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function addToCart(Product $product, SessionInterface $session): Response
    {
        // Récupère le panier depuis la session
        $cart = $session->get('cart', []);

        // Si le produit existe déjà dans le panier, augmente la quantité
        $productId = $product->getId();
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            // Sinon, ajoute le produit au panier avec une quantité de 1
            $cart[$productId] = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => 1,
            ];
        }

        // Sauvegarde le panier dans la session
        $session->set('cart', $cart);

        // Redirige l'utilisateur vers la page du panier
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
public function updateCart(Product $product, Request $request, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);
    $productId = $product->getId();

    // Récupère la nouvelle quantité depuis le formulaire
    $newQuantity = (int) $request->request->get('quantity', 1);

    if (isset($cart[$productId])) {
        if ($newQuantity > 0) {
            $cart[$productId]['quantity'] = $newQuantity; // Met à jour la quantité
        } else {
            unset($cart[$productId]); // Supprime l'article si la quantité est 0
        }
    }

    $session->set('cart', $cart);

    return $this->redirectToRoute('cart');
}

#[Route('/cart/remove/{id}', name: 'cart_remove')]
public function removeFromCart(Product $product, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);
    $productId = $product->getId();

    if (isset($cart[$productId])) {
        unset($cart[$productId]); // Supprime l'article du panier
    }

    $session->set('cart', $cart);

    return $this->redirectToRoute('cart');
}

}
