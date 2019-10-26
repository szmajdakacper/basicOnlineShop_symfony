<?php

namespace App\Form;

use App\Entity\Rate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AddRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rate', ChoiceType::class, [
                'choices'  => [
                    '1/5' => 1,
                    '2/5' => 2,
                    '3/5' => 3,
                    '4/5' => 4,
                    '5/5' => 5,
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('add', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-outline-success btn-block'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rate::class,
            'attr' => [
                'class' => 'form-group form-opinion'
            ]
        ]);
    }
}
