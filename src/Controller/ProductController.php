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
    public function list(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Gestion du tri par fourchette de prix
        $priceRange = $request->query->get('priceRange');
        $queryBuilder = $entityManager->getRepository(Product::class)->createQueryBuilder('p');

        if ($priceRange) {
            // Tranches mises à jour : 10-29.99, 30-35.99, 36-50
            [$min, $max] = explode('-', $priceRange);
            $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                         ->setParameter('min', $min)
                         ->setParameter('max', $max);
        }

        $products = $queryBuilder->getQuery()->getResult();

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
public function addToCart(Product $product, Request $request, SessionInterface $session): Response
{
    // Récupère le panier depuis la session
    $cart = $session->get('cart', []);

    // Taille sélectionnée (par défaut "M")
    $size = $request->request->get('size', 'M');

    // ID du produit
    $productId = $product->getId();

    // Si le produit existe déjà dans le panier, augmente la quantité
    if (isset($cart[$productId])) {
        $cart[$productId]['quantity']++;
    } else {
        // Sinon, ajoute le produit au panier avec ses détails
        $cart[$productId] = [
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'image' => $product->getImage(), // Inclut l'image
            'size' => $size,               // Inclut la taille
            'quantity' => 1,
        ];
    }

    // Sauvegarde le panier dans la session
    $session->set('cart', $cart);

    // Redirige vers la page du panier
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

    #[Route('/checkout', name: 'checkout')]
public function checkout(SessionInterface $session): Response
{
    $cart = $session->get('cart', []);

    // Vérifier que le panier n'est pas vide
    if (empty($cart)) {
        $this->addFlash('error', 'Votre panier est vide.');
        return $this->redirectToRoute('product_list');
    }

    // Logique pour finaliser la commande (par exemple, sauvegarder dans la base de données)

    // Vider le panier
    $session->set('cart', []);

    // Rediriger vers une page de confirmation ou un message de succès
    return $this->render('cart/checkout.html.twig', [
        'message' => 'Votre commande a été finalisée avec succès.',
    ]);
}

#[Route('/admin', name: 'backoffice')]
public function backoffice(EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur a le rôle administrateur
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Récupérer tous les produits pour l'affichage
    $products = $entityManager->getRepository(Product::class)->findAll();

    return $this->render('admin/backoffice.html.twig', [
        'products' => $products,
    ]);
}


}
