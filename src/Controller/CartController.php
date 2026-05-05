<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('', name: 'app_cart')]
    public function index(CartService $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getDetailed(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function add(int $id, CartService $cart): Response
    {
        $cart->add($id);
        $this->addFlash('success', 'Produit ajouté au panier.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $req, CartService $cart): Response
    {
        $cart->update($id, (int) $req->request->get('qty', 1));
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function remove(int $id, CartService $cart): Response
    {
        $cart->remove($id);
        return $this->redirectToRoute('app_cart');
    }
}