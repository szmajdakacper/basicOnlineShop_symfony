<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Rate;
use App\Entity\Product;
use App\Entity\User;
use App\Form\AddRateType;

/**
 * @Route("/rate", name="rate")
 */
class RatesController extends AbstractController
{
    /**
     * @Route("/add/{id}", name="_add")
     */
    public function addRate(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        $user = $this->getUser();
        
        $rate = new Rate();
        $form = $this->createForm(AddRateType::class, $rate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rate = $form->getData();
            $rate->setProduct($product);
            $rate->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();
        }

        $this->addFlash(
            'success',
            'Your Rate Added Correct'
        );
        return $this->redirect("/products/".$id);
    }
}