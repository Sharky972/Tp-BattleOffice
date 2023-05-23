<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Cart;
use App\Form\CartType;
use App\Form\ProductType;
use App\Form\UserFormType;
use App\Repository\CartRepository;
use Symfony\Component\Form\SubmitButton;


class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository)
    {
        $allproduct = $entityManager->getRepository(Product::class)->findAll();

        $cart = new Cart();
        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartRepository->save($cart, true);
            dd($cart);
        }


        return $this->render('landing_page/index_new.html.twig', ['form' => $form, 'allproduct' => $allproduct]);
    }


    /**
     * @Route("/confirmation", name="confirmation")
     */
    public function confirmation()
    {
        return $this->render('landing_page/confirmation.html.twig', []);
    }


    /**
     * @Route("/submit", name="app_submit")
     */
    public function submit(EntityManagerInterface $entityManager)
    {
        return $this->redirectToRoute('app_home');
    }
}
