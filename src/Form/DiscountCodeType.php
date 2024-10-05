<?php

namespace App\Form;

use App\Entity\DiscountCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_code', TextType::class, [
                'required' => true,
                'label' => 'Code de Réduction',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ]
            ])
            ->add('reduction', NumberType::class, [
                'required' => true,
                'label' => 'Réduction (%)',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ]
            ])
            ->add('active', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input mb-5'
                ],
                'label' => 'Actif',
                'label_attr' => [
                    'class' => 'form-check-label fw-bold'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiscountCode::class,
        ]);
    }
}
