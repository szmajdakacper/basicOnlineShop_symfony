<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AddCategoryType;
use App\Form\AddProductType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Bill;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/admin", name="admin")
* @IsGranted("ROLE_ADMIN")
*/
class AdminController extends AbstractController
{
    /**
     * @Route("/dashboard", name="_dashboard")
     */
    public function dashboard()
    {
        return $this->render('admin/index.html.twig');
    }

    //SECTION PRODUCTS
    /**
     * @Route("/add_product", name="_add_product")
     */
    public function addNewProduct(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(AddProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['image']->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $this->createThumbnail($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Something went wrong with file upload');
                    return $this->redirectToRoute('admin_dashboard');
                }
                $product->setImg($newFilename);
            }
            $product = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Product Added Correct.'
            );
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/add_product.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit_product/{id}", name="_edit_product")
     */
    public function editProduct($id, Request $request)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        $form = $this->createForm(AddProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form['image']->getData();

            if ($imageFile) {
                //if is new image file added, than the old one must be deleted
                $filesystem = new Filesystem();
                $old_imgName = $product->getImg();
                $filesystem->remove($this->getParameter('images_directory').'/'.$old_imgName);
                $filesystem->remove($this->getParameter('thumbnails_directory').'/'.$old_imgName);

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $this->createThumbnail($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Something went wrong with file upload');
                    return $this->redirectToRoute('admin_dashboard');
                }
                $product->setImg($newFilename);
            }
            $product = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Product Updated Correct.'
            );
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/add_product.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove_product/{id}", name="_remove_product")
     */
    public function removeProduct($id, Request $request)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        //delete products image if isset
        $imgName = $product->getImg();
        if ($imgName !== null) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->getParameter('images_directory').'/'.$imgName);
            $filesystem->remove($this->getParameter('thumbnails_directory').'/'.$imgName);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);
        $entityManager->flush();
        
        $this->addFlash(
            'success',
            'Product Deleted Correct.'
        );
        return $this->redirectToRoute('admin_dashboard');
    }

    public function createThumbnail($image)
    {
        if (preg_match("/\.png$/i", $image)) {
            $img = imagecreatefrompng($this->getParameter('images_directory')."/".$image);
        } else {
            $img = imagecreatefromjpeg($this->getParameter('images_directory')."/".$image);
        }

        $src_image = $img;
        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = 400;
        $dst_h = 400;
        $src_w = imagesx($img);
        $src_h = imagesy($img);

        $newImage = imagecreatetruecolor($dst_w, $dst_h);
        $dst_image = $newImage;

        //fit to dst_y and _x
        if ($src_h > $src_w) {
            $dst_h = floor($src_h * ($dst_w/$src_w));
            $dst_y = (400 - $dst_h) / 2;
        } else {
            $dst_w = floor($src_w * ($dst_h/$src_h));
            $dst_x = (400 - $dst_w) / 2;
        }
        imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        imagejpeg($dst_image, 'uploads/thumbnails/'.$image);
        imagedestroy($dst_image);
    }

    //SECTION CATEGORIES
    /**
     * @Route("/add_category", name="_add_category")
     */
    public function addNewCategory(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(AddCategoryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Category Added Correct.'
            );
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/add_category.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit_category/{id}", name="_edit_category")
     */
    public function editCategory($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $form = $this->createForm(AddCategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Category Updated Correct.'
            );
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/add_category.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove_category/{id}", name="_remove_category")
     */
    public function removeCategory($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();

        //removing all products from this category
        $products = $category->getProducts();
        foreach ($products as $product) {
            $entityManager->remove($product);
            $entityManager->flush();
        }
        
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Category Deleted Correct.'
        );
        return $this->redirectToRoute('admin_dashboard');
    }

    //SECTION PAYMENTS
    /**
     * show unpaid bills
     * @Route("/unpaid", name="_unpaid")
     */
    public function showUnpaidBills()
    {
        $bills = $this->getDoctrine()->getRepository(Bill::class)->findBy(
            ['status' => null],
            ['time' => 'DESC']
        );
        return $this->render("admin/unpaid_bills.html.twig", ["bills" => $bills]);
    }

    /**
     * enter up payments
     * @Route("/paid/{id}", name="_paid")
     */
    public function enterUpPayment($id)
    {
        $bill = $this->getDoctrine()->getRepository(Bill::class)->find($id);
        $bill->setStatus(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($bill);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Payment Entered Up. Now You Can Send Product(s).'
        );
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @Route("/all-bills", name="_bills")
     */
    public function showAllBills()
    {
        $bills = $this->getDoctrine()->getRepository(Bill::class)->findBy(
            [],
            ['time' => 'DESC']
        );
        return $this->render("admin/all_bills.html.twig", ["bills" => $bills]);
    }

    /**
     * @Route("/bill/{id}", name="_bill_details")
     */
    public function showBillDetails($id)
    {
        $bill = $this->getDoctrine()->getRepository(Bill::class)->find($id);
        return $this->render("admin/one_bill_details.html.twig", ["bill" => $bill]);
    }
}
