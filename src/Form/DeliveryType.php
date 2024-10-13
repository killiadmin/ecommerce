<?php

namespace App\Form;

use App\Entity\UserAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number_delivery', IntegerType::class, [
                'required' => false,
                'label' => 'Numéro',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('libelle_delivery', TextType::class, [
                'required' => false,
                'label' => 'Rue',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('code_delivery', IntegerType::class, [
                'required' => false,
                'label' => 'Code postal',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('city_delivery', TextType::class, [
                'required' => false,
                'label' => 'Ville',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('additionnal_information', TextType::class, [
                'required' => false,
                'label' => 'Informations complémentaires',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('billing', CheckboxType::class, [
                'required' => false,
                'label' => 'Mon adresse de facture est différente : ',
                'label_attr' => [
                    'class' => 'fw-bold form-label me-3'
                ],
                'attr' => [
                    'class' => 'form-check-input mb-3'
                ]
            ])
            ->add('number_billing', IntegerType::class, [
                'required' => false,
                'label' => 'Numéro',
                'label_attr' => [
                    'class' => 'fw-bold form-label billing-field'
                ],
                'attr' => [
                    'class' => 'form-control mb-3 billing-field'
                ]
            ])
            ->add('libelle_billing', TextType::class, [
                'required' => false,
                'label' => 'Rue',
                'label_attr' => [
                    'class' => 'fw-bold form-label billing-field'
                ],
                'attr' => [
                    'class' => 'form-control  mb-3 billing-field'
                ]
            ])
            ->add('code_billing', IntegerType::class, [
                'required' => false,
                'label' => 'Code postal',
                'label_attr' => [
                    'class' => 'fw-bold form-label billing-field'
                ],
                'attr' => [
                    'class' => 'form-control billing-field mb-3'
                ]
            ])
            ->add('city_billing', TextType::class, [
                'required' => false,
                'label' => 'Ville',
                'label_attr' => [
                    'class' => 'fw-bold form-label billing-field'
                ],
                'attr' => [
                    'class' => 'form-control billing-field mb-3'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserAddress::class,
        ]);
    }
}
