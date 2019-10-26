<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Rate;
use App\Form\AddRateType;
use App\Form\AddToCartType;

/**
 * @Route("/products", name="products")
 */
class ProductsController extends AbstractController
{
    /**
     * @Route("/", name="_all")
     */
    public function all()
    {
        //all categories for sidebar
        $repository_category = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository_category->findAll();

        $repository_product = $this->getDoctrine()->getRepository(Product::class);
        $products = $repository_product->findAll();

        return $this->render("products/products_all.html.twig", ["categories" => $categories, "products" => $products]);
    }

    /**
     * @Route("/category/{category_toSearch}", name="_category")
     */
    public function byCategory($category_toSearch)
    {
        //all categories for sidebar
        $repository_category = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository_category->findAll();
        
        $repository_category_toSearch = $this->getDoctrine()->getRepository(Category::class)->findOneBy(["name" => $category_toSearch]);
        $products = $repository_category_toSearch->getProducts();

        return $this->render("products/products_category.html.twig", ["categories" => $categories, "products" => $products]);
    }

    /**
     * @Route("/{id}", name="_one")
     */
    public function product($id)
    {
        //all categories for sidebar
        $repository_category = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository_category->findAll();

        $repository_product = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository_product->find($id);

        //Rates form
        $rate = new Rate();
        $rate->setComment('I Like It!');
        $rate->setRate(5);
        $form_rate = $this->createForm(AddRateType::class, $rate, [
            'action' => '/rate/add/'.$id
        ]);

        //Add To Cart form
        $form_addToCart = $this->createForm(AddToCartType::class, [], [
            'action' => '/add-cart/'.$id
        ]);
        
        return $this->render("products/products_one.html.twig", [
            "form_rate" => $form_rate->createView(),
            "form_addToCart" => $form_addToCart->createView(),
            "categories" => $categories,
            "product" => $product
            ]);
    }
}
