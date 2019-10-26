<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\AddAddressType;
use App\Entity\Address;
use App\Entity\User;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/user", name="user")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/dashboard", name="_dashboard")
     */
    public function dashboard()
    {
        return $this->render("users/index.html.twig");
    }

    /**
     * @Route("/address", name="_address")
     */
    public function address(Request $request)
    {
        $user_id = $this->getUser()->getId();
        $user = $this->getDoctrine()->getRepository(User::class)->find($user_id);

        if ($user->getAddress()) {
            $address = $user->getAddress();
        } else {
            $address = new Address();
        }
        
        $form = $this->createForm(AddAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();
            $address->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your Address Is Updated'
            );

            if ($this->isCartEmpty()) {
                return $this->redirect("/user/dashboard");
            } else {
                return $this->redirect("/my-cart");
            }
        }

        return $this->render('users/address.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/bills", name="_bills")
     */
    public function bills()
    {
        $user = $this->getUser();
        $bills = $user->getBills();

        return $this->render("users/bills.html.twig", ["bills" => $bills]);
    }

    public function isCartEmpty()
    {
        $session = new Session;
        //fetch cart from session
        $cart = $session->get('cart');

        if ($cart == null) {
            return 1;
        } else {
            return 0;
        }
    }
}