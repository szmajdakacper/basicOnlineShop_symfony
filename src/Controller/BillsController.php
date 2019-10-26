<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Bill;
use App\Entity\Transaction;
use \Datetime;

/**
     * @Route("/bill", name="bill")
     */
class BillsController extends AbstractController
{
    private $bill;
    private $time;
    private $cart;
    private $products;
    private $sum;

    public function __construct(SessionInterface $session)
    {
        //create bill, timestamp
        $this->bill = new Bill();
        $this->time = new DateTime();

        //fetch cart from session
        $this->cart = $session->get('cart');
    }
    
    /**
     * @Route("/buy", name="_buy")
     * @IsGranted("ROLE_USER")
     */
    public function newBill()
    {

        //if user have no address yet, then redirect him to set one.
        $user = $this->getUser();
        if ($user->getAddress() == null) {
            $this->addFlash(
                'warning',
                'Set Your Address'
            );
            return $this->redirect("/user/address");
        }

        //check if is enough quantity in store and fetch products and their sum
        if (!$this->isEnoughQuantityInStore()) {
            return $this->redirect('/my-cart');
        }

        //save new bill
        $this->bill->setUser($user);
        $this->bill->setTime($this->time);
        $this->bill->setSum($this->sum);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($this->bill);
        $entityManager->flush();

        foreach ($this->cart as $product_id => $quantity) {
            //fetch product name and prise
            $productName = $this->products[$product_id]->getName();
            $productPrise = $this->products[$product_id]->getPrise();

            //subtract quantity from store
            $quantityInStore = $this->products[$product_id]->getQuantity();
            $newQuantityInStore = $quantityInStore - $quantity;
            $this->products[$product_id]->setQuantity($newQuantityInStore);

            //create new transaction
            $transaction = new Transaction();
            //set parameters to transaction
            $transaction->setProductName($productName);
            $transaction->setProductPrise($productPrise);
            $transaction->setQuantity($quantity);
            $transaction->setBill($this->bill);

            $entityManager->persist($transaction);
            $entityManager->flush();
        }

        $this->addFlash(
            'success',
            'You Bought New Product, Check Your Bill And Pay'
        );
        
        //destroy cart
        return $this->redirect('/d-cart');
    }

    //check if is enough quantity in store
    //and save products in array
    //and sum all prices
    private function isEnoughQuantityInStore()
    {
        $this->sum = 0;
        
        foreach ($this->cart as $product_id => $quantity) {
            $this->products[$product_id] = $this->getDoctrine()->getRepository(Product::class)->find($product_id);
            if ($this->products[$product_id]->getQuantity() < $quantity) {
                $this->addFlash(
                    'danger',
                    'We have not enough '.$this->products[$product_id]->getName().'(only: '.$this->products[$product_id]->getQuantity().') in our store'
                );
                return false;
            }
            //sum all prices * quantity
            $prise = $this->products[$product_id]->getPrise();
            $amount = $quantity * $prise;
            $this->sum += $amount;
        }
        return true;
    }
}