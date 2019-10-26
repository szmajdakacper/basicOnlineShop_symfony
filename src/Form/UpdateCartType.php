<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UpdateCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', TextType::class, [
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control'
                ]
            ])
            ->add('category', TextType::class, [
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control'
                ]
            ])
            ->add('quantity', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('update', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-info btn-block btn-sm mt-3 mb-0'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr'  => [
                'class' => 'form-group'
            ]
        ]);
    }
}
