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
use App\Service\GuzzleClient;
use GuzzleHttp\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LandingPageController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository)
    {

        $bearer = $_ENV['BEARER_API_KEY'];

        $allproduct = $entityManager->getRepository(Product::class)->findAll();

        $cart = new Cart();
        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartRepository->save($cart, true);

            $json = json_encode([
                'order' => [
                    'id'   => $cart->getId(),
                    'product' => $cart->getProduct()->getName(),
                    'payment_method' => 'stripe',
                    'status' => 'WAITING',
                    'client' => [
                        "firstname" => $cart->getUser()->getFirstName(),
                        "lastname" => $cart->getUser()->getLastName(),
                        "email" => $cart->getUser()->getEmail()
                    ],

                    'addresses' => [
                        "billing" => [
                            "address_line1" => $cart->getUser()->getAdress(),
                            "address_line2" => $cart->getUser()->getAdress(),
                            "city" => $cart->getUser()->getCity(),
                            "zipcode" => $cart->getUser()->getPosteCode(),
                            "country" => $cart->getUser()->getCountry(),
                            "phone" => $cart->getUser()->getPhone(),
                        ],

                        "shipping" => [
                            "address_line1" => $cart->getUser()->getAdress(),
                            "address_line2" => $cart->getUser()->getAdress(),
                            "city" => $cart->getUser()->getCity(),
                            "zipcode" => $cart->getUser()->getPosteCode(),
                            "country" => $cart->getUser()->getCountry(),
                            "phone" => $cart->getUser()->getPhone(),

                        ]
                    ]
                ]
            ]);

            $response = $this->client->request(
                'POST',
                'https://api-commerce.simplon-roanne.com/order',
                [
                    'headers' => [
                        'Authorization' => $bearer
                    ],
                    'body' => $json
                ]
            );

            return $this->render('landing_page/confirmation.html.twig', [

                'response' => $response, // Passez les données de réponse à la vue si nécessaire
            ]);
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
