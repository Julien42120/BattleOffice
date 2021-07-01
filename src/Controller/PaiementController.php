<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Product;
use App\Form\PaiementType;
use App\Repository\OrderRepository;
use App\Repository\PaiementRepository;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/paiement")
 */
class PaiementController extends AbstractController
{
    /**
     * @Route("/", name="paiement_index", methods={"GET"})
     */
    public function index(PaiementRepository $paiementRepository): Response
    {
        return $this->render('paiement/index.html.twig', [
            'paiements' => $paiementRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="paiement_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('paiement/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Stripe", name="Stripe", methods={"GET","POST"})
     */
    public function stripe(Request $request, OrderRepository $orderRepository): Response
    {
        $paiement = new Paiement();
        $form = $this->createForm(PaymentType::class, $paiement);
        $form->handleRequest($request);
        $order = $orderRepository->findAll();
        $order_id = $request->get('stripe');
        $order = $orderRepository->findOneBy(['id' => $order_id]);

        if ($form->isSubmitted() && $form->isValid()) {
            \Stripe\Stripe::setApiKey('sk_test_51IuZljBeRLZv7zwma4Vf5nWy7Vzxl6zoJ2AI8pj2sZyVwxzQx7dYeBjmEjLVKa7crxrsXgoHNhpyts9x4fJJkXic00qkZkziNf');
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $paiement->getAmount() * 100,
                'currency' => 'eur',
            ]);
            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            //envoi à la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();

            //redirection
            return $this->redirectToRoute('campaign_show');
        }

        return $this->render('payment/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
            'order' => $order,
        ]);
    }

    /**
     * @Route("/paypal", name="paypal", methods={"GET","POST"})
     */
    // public function paypal(Request $request): Response
    // {
    //     $paiement = new Paiement();
    //     $form = $this->createForm(PaiementType::class, $paiement);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($paiement);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('paiement_index');
    //     }

    //     return $this->render('paiement/new.html.twig', [
    //         'paiement' => $paiement,
    //         'form' => $form->createView(),
    //     ]);
    // }


    /**
     * @Route("/{id}", name="paiement_show", methods={"GET"})
     */
    public function show(Paiement $paiement): Response
    {
        return $this->render('paiement/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="paiement_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Paiement $paiement): Response
    {
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('paiement/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_delete", methods={"POST"})
     */
    public function delete(Request $request, Paiement $paiement): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paiement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('paiement_index');
    }
}
