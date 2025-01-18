<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
public function cart(): Response
{
    // Logique pour récupérer les articles du panier (à implémenter)
    return $this->render('cart/index.html.twig', [
        'cartItems' => [], // Placeholder pour les articles
        'total' => 0, // Placeholder pour le total
    ]);
}

}
