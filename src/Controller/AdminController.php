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
    #[Route('/admin/add-product', name: 'admin_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $product = new Product();

            // Récupérer les données du formulaire
            $name = $request->request->get('name');
            $price = $request->request->get('price');
            $stockXS = $request->request->get('stock_xs'); // Correspondance avec le formulaire
            $stockS = $request->request->get('stock_s');
            $stockM = $request->request->get('stock_m');
            $stockL = $request->request->get('stock_l');
            $stockXL = $request->request->get('stock_xl');

            // Attribuer les données à l'entité
            $product->setName($name);
            $product->setPrice((float) $price); // Assurer le typage
            $product->setStockXS((int) $stockXS); // Assurer le typage
            $product->setStockS((int) $stockS);
            $product->setStockM((int) $stockM);
            $product->setStockL((int) $stockL);
            $product->setStockXL((int) $stockXL);

            // Enregistrer dans la base de données
            $entityManager->persist($product);
            $entityManager->flush();

            // Rediriger après l'ajout
            $this->addFlash('success', 'Produit ajouté avec succès !');

            return $this->redirectToRoute('backoffice');
        }

        return $this->redirectToRoute('backoffice');
    }

    #[Route('/admin/update-product/{id}', name: 'admin_update_product', methods: ['POST'])]
    public function updateProduct(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère le produit depuis la base de données
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        // Mise à jour des champs
        $product->setName($request->request->get('name'));
        $product->setPrice((float) $request->request->get('price'));
        $product->setStockXS((int) $request->request->get('stock_xs')); // Harmonisation avec le formulaire
        $product->setStockS((int) $request->request->get('stock_s'));
        $product->setStockM((int) $request->request->get('stock_m'));
        $product->setStockL((int) $request->request->get('stock_l'));
        $product->setStockXL((int) $request->request->get('stock_xl'));

        $entityManager->flush();

        // Redirection vers la page backoffice après la mise à jour
        return $this->redirectToRoute('backoffice');
    }

    #[Route('/admin/delete-product/{id}', name: 'admin_delete_product', methods: ['GET', 'DELETE'])]
public function deleteProduct(int $id, EntityManagerInterface $entityManager): Response
{
    // Récupère le produit depuis la base de données
    $product = $entityManager->getRepository(Product::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Produit non trouvé.');
    }

    // Supprime le produit
    $entityManager->remove($product);
    $entityManager->flush();

    // Message flash pour informer de la suppression
    $this->addFlash('success', 'Produit supprimé avec succès.');

    // Redirection vers la page backoffice
    return $this->redirectToRoute('backoffice');
}

}
