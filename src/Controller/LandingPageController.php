<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\DeliveryAdress;
use App\Entity\Order;
use App\Entity\Paiement;
use App\Entity\Product;
use App\Form\ClientType;
use App\Form\PaiementType;
use App\Repository\ClientRepository;
use App\Repository\DeliveryAdressRepository;
use App\Repository\PaiementRepository;
use App\Repository\ProductRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $order = new Order();
        $client = new Client();
        $client->getDeliveryAdress(new DeliveryAdress());
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        $product = $productRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $productId = $request->get('product');
            $product = $productRepository->findOneBy(['id' => $productId]);

            if ($client->getDeliveryAdress()->getFirstName() == null && $client->getDeliveryAdress()->getLastName() == null && $client->getDeliveryAdress()->getAdress() == null && $client->getDeliveryAdress()->getOtherAdress() == null && $client->getDeliveryAdress()->getPostalCode() == null && $client->getDeliveryAdress()->getCity() == null && $client->getDeliveryAdress()->getPhone() == null) {
                $delivery_adress =  $this->initDeleveryAdress($client); // Check function on bottom
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();
            $order->setClient($client);
            $order->setProduct($product);


            $order->setDeliveryAdress($client->getDeliveryAdress());

            // SEND ORDER IN DATABASE 
            $entityManager->persist($order);
            $entityManager->flush();

            $payment = new Paiement();
            $payment->setOrders($order);
            $payment->setPaiementStatus('WAITING');
            $payment->setMethod('stripe');
            $payment->setAmount($product->getPrice());

            $content =  $this->httpClient($client, $product, $payment, $order);
            $payment->setPaiementApi($content['order_id']);
            $paiementMethod = $payment->getMethod();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($payment);
            $entityManager->flush();

            if ($paiementMethod === 'stripe') {
                return $this->redirectToRoute('stripe', [
                    'id' => $order->getId(),
                    'paymentApi' => $payment->getPaiementApi(),
                ]);
            } else {
                return $this->redirectToRoute('paypal', [
                    'id' => $order->getId()
                ]);
            }
        }

        return $this->render('landing_page/index.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
            'products' => $product,
            'order' => $order
        ]);
    }


    public function httpClient(Client $client, Product $product, Paiement $payment)
    {
        $token = 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX';
        $data =
            [
                "order" => [
                    "id" => "1",
                    "product" => $product->getName(),
                    "payment_method" => $payment->getMethod(),
                    "status" =>  $payment->getPaiementStatus(),
                    "client" => [
                        "firstname" => $client->getFirstName(),
                        "lastname" => $client->getLastName(),
                        "email" => $client->getMail()
                    ],
                    "addresses" => [
                        "billing" => [
                            "address_line1" =>  $client->getAdress(),
                            "address_line2" => $client->getOtherAdress(),
                            "city" =>  $client->getCity(),
                            "zipcode" => $client->getPostalCode(),
                            "country" => $client->getCountry(),
                            "phone" => $client->getPhone()
                        ],
                        "shipping" => [
                            "address_line1" => $client->getDeliveryAdress()->getAdress(),
                            "address_line2" => $client->getDeliveryAdress()->getOtherAdress(),
                            "city" => $client->getDeliveryAdress()->getCity(),
                            "zipcode" => $client->getDeliveryAdress()->getPostalCode(),
                            "country" => $client->getDeliveryAdress()->getCountry(),
                            "phone" => $client->getDeliveryAdress()->getPhone()
                        ]
                    ]

                ]
            ];

        $data = json_encode($data);
        $httpClient = HttpClient::create([]);
        $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com/order', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    /**
     * @Route("/confirmation", name="confirmation")
     */
    public function confirmation(MailerInterface $mailer)
    {

        return $this->render('landing_page/confirmation.html.twig', []);
    }

    public function initDeleveryAdress($client)
    {
        $client->getDeliveryAdress()->setFirstName($client->getFirstName());
        $client->getDeliveryAdress()->setLastname($client->getLastname());
        $client->getDeliveryAdress()->setAdress($client->getAdress());
        $client->getDeliveryAdress()->setOtherAdress($client->getOtherAdress());
        $client->getDeliveryAdress()->setCountry($client->getCountry());
        $client->getDeliveryAdress()->setPostalCode($client->getPostalCode());
        $client->getDeliveryAdress()->setCity($client->getCity());
        $client->getDeliveryAdress()->setPhone($client->getPhone());
        return $client->getDeliveryAdress();
    }

    /**
     * @Route("/stripe/{id}", name="stripe", methods={"GET","POST"})
     */
    public function stripe(Request $request, Order $order, ProductRepository $productRepository, PaiementRepository $paiementRepository, ClientRepository $clientRepository, DeliveryAdressRepository $deliveryAdressRepository, MailerInterface $mailer): Response
    {
        $product = $productRepository->findOneBy(['id' => $order->getProduct()]);
        $paymentApi = $request->get('paymentApi');
        $paiement = $paiementRepository->findOneBy(['paiementApi' => $paymentApi]);
        $delivery = $deliveryAdressRepository->findOneBy(['id' => $paiement->getOrders()->getDeliveryAdress()]);
        $client = $clientRepository->findOneBy(['id' => $paiement->getOrders()->getClient()]);

        $paiement->setAmount($product->getPrice() - $product->getReduction() * 100);

        if ($request->isMethod('post')) {

            \Stripe\Stripe::setApiKey('sk_test_51IuZljBeRLZv7zwma4Vf5nWy7Vzxl6zoJ2AI8pj2sZyVwxzQx7dYeBjmEjLVKa7crxrsXgoHNhpyts9x4fJJkXic00qkZkziNf');
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $paiement->getAmount(),
                'currency' => 'eur',
            ]);

            $data = ["status" => "PAID"];
            $data = json_encode($data);

            $token = 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX';

            $httpClient = HttpClient::create([]);
            $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com/order/' . $paiement->getPaiementApi() . "/status", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $data
            ]);
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();

            $paiement->setPaiementStatus("PAID");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();
            $this->sendEmail($mailer, $client, $delivery, $product);
            //redirection
            return $this->redirectToRoute('confirmation');
        }

        return $this->render('paiement/Stripe.html.twig', [
            'paiement' => $paiement,
            'amount' => $product->getPrice(),
            'id' => $order->getId(),
            'paymentApi' => $paymentApi
        ]);
    }

    public function sendEmail(MailerInterface $mailer, Client $client, DeliveryAdress $deliveryAdress, Product $product)
    {


        $email = (new TemplatedEmail())
            ->from('fabien@example.com')
            ->to(new Address('ryan@example.com'))
            ->subject('Thanks for signing up!')

            // path of the Twig template to render
            ->htmlTemplate('landing_page/mail.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'client' => $client,
                'deliveryAdress' => $deliveryAdress,
                'product' => $product,
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'foo',
            ]);

        $mailer->send($email);
    }
}
