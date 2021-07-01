<?php

namespace App\Controller;

use App\Entity\DeliveryAdress;
use App\Form\DeliveryAdressType;
use App\Repository\DeliveryAdressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/delivery/adress")
 */
class DeliveryAdressController extends AbstractController
{
    /**
     * @Route("/", name="delivery_adress_index", methods={"GET"})
     */
    public function index(DeliveryAdressRepository $deliveryAdressRepository): Response
    {
        return $this->render('delivery_adress/index.html.twig', [
            'delivery_adresses' => $deliveryAdressRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="delivery_adress_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $deliveryAdress = new DeliveryAdress();
        $form = $this->createForm(DeliveryAdressType::class, $deliveryAdress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($deliveryAdress);
            $entityManager->flush();

            return $this->redirectToRoute('delivery_adress_index');
        }

        return $this->render('delivery_adress/new.html.twig', [
            'delivery_adress' => $deliveryAdress,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_adress_show", methods={"GET"})
     */
    public function show(DeliveryAdress $deliveryAdress): Response
    {
        return $this->render('delivery_adress/show.html.twig', [
            'delivery_adress' => $deliveryAdress,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="delivery_adress_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryAdress $deliveryAdress): Response
    {
        $form = $this->createForm(DeliveryAdressType::class, $deliveryAdress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('delivery_adress_index');
        }

        return $this->render('delivery_adress/edit.html.twig', [
            'delivery_adress' => $deliveryAdress,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_adress_delete", methods={"POST"})
     */
    public function delete(Request $request, DeliveryAdress $deliveryAdress): Response
    {
        if ($this->isCsrfTokenValid('delete'.$deliveryAdress->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryAdress);
            $entityManager->flush();
        }

        return $this->redirectToRoute('delivery_adress_index');
    }
}
