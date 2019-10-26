<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\UpdateCartType;
use App\Entity\Product;

class CartsController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

        //if is no cart in session yet -> make one.
        if (!$this->session->get('cart')) {
            $this->session->set('cart', array());
        }
    }
    
    /**
     * @Route("/my-cart", name="my_cart")
     */
    public function index()
    {
        //fetch cart and all products
        $cart = $this->session->get('cart');
        $products = array();

        //if cart is empty render view
        if (count($cart) < 1) {
            return $this->render('carts/index.html.twig', ['forms' => null]);
        }

        //create form for every product
        foreach ($cart as $product_id => $quantity) {
            //Add cart form
            $forms[$product_id] = $this->createForm(UpdateCartType::class, [], [
                'action' => '/add-cart/'.$product_id
            ]);
            $forms[$product_id] = $forms[$product_id]->createView();

            //fetch all products by ids
            $products[$product_id] = $this->getDoctrine()->getRepository(Product::class)->find($product_id);
        }
        
        return $this->render('carts/index.html.twig', ['forms' => $forms, 'products' => $products]);
    }

    /**
     * @Route("/add-cart/{product_id}", name="add_cart")
     */
    public function addToCart(Request $request, $product_id)
    {
        //fetch cart from session
        $cart = $this->session->get('cart');

        //quantity from request
        $req_all = $request->request->all();
        //if you added product, then key is add_to_cart,
        //if you updated quantity the key is update_cart
        if (array_key_exists('add_to_cart', $req_all)) {
            $quantity = $req_all['add_to_cart']['quantity'];
        } else {
            $quantity = $req_all['update_cart']['quantity'];
        }

        //save product and quantity
        $cart[$product_id] = $quantity;
        $this->session->set('cart', $cart);

        //return cart view
        return $this->redirect('/products');
    }

    /**
     * @Route("/remove-product/{id}", name="remove_product")
     */
    public function removeProduct($id)
    {
        //fetch cart
        $cart = $this->session->get('cart');
        unset($cart[$id]);
        $this->session->set('cart', $cart);

        return $this->redirect('/my-cart');
    }

    /**
     * @Route("/d-cart", name="destroy_cart")
     */
    public function removeCart()
    {
        $this->session->remove('cart');
        return $this->redirect('/user/dashboard');
    }
}