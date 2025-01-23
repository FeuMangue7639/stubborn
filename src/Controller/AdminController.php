<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/backoffice', name: 'backoffice')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les produits
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('admin/backoffice.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/add-product', name: 'admin_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $product = new Product();

            $name = $request->request->get('name');
            $price = $request->request->get('price');
            $stockXS = $request->request->get('stock_xs');
            $stockS = $request->request->get('stock_s');
            $stockM = $request->request->get('stock_m');
            $stockL = $request->request->get('stock_l');
            $stockXL = $request->request->get('stock_xl');

            $product->setName($name);
            $product->setPrice((float) $price);
            $product->setStockXS((int) $stockXS);
            $product->setStockS((int) $stockS);
            $product->setStockM((int) $stockM);
            $product->setStockL((int) $stockL);
            $product->setStockXL((int) $stockXL);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('backoffice');
        }

        return $this->redirectToRoute('backoffice');
    }

    #[Route('/admin/update-product/{id}', name: 'admin_update_product', methods: ['POST'])]
    public function updateProduct(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $product->setName($request->request->get('name'));
        $product->setPrice((float) $request->request->get('price'));
        $product->setStockXS((int) $request->request->get('stock_xs'));
        $product->setStockS((int) $request->request->get('stock_s'));
        $product->setStockM((int) $request->request->get('stock_m'));
        $product->setStockL((int) $request->request->get('stock_l'));
        $product->setStockXL((int) $request->request->get('stock_xl'));

        $entityManager->flush();

        $this->addFlash('success', 'Produit modifié avec succès !');
        return $this->redirectToRoute('backoffice');
    }

    #[Route('/admin/delete-product/{id}', name: 'admin_delete_product', methods: ['GET'])]
    public function deleteProduct(int $id, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');
        return $this->redirectToRoute('backoffice');
    }
}

