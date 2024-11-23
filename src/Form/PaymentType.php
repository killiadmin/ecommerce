<?php

namespace App\Form;

use App\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname_payment' ,TextType::class, [
                'required' => true,
                'label' => 'Prénom',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prénom titulaire de la carte',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom du titulaire de la carte ne peut pas être vide.',
                    ]),
                ],
            ])
            ->add('lastname_payment',TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom titulaire de la carte',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom du titulaire de la carte ne peut pas être vide.',
                    ]),
                ],
            ])
            ->add('number_payment', TextType::class, [
                'required' => true,
                'label' => 'Numéro carte bancaire',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '**** **** **** ****',
                    'pattern' => '(\\d{4} ?){4}',
                    'title' => 'Le numéro de carte bancaire doit comporter 16 chiffres',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le numéro de carte bancaire ne peut pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(\d{4} ?){4}$/',
                        'message' => 'Le numéro de carte bancaire doit comporter 16 chiffres',
                    ]),
                ],
            ])
            ->add('expiration_date_payment', TextType::class, [
                'required' => true,
                'label' => 'Date d\'expiration',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MM/AA',
                    'pattern' => '^(0[1-9]|1[0-2])\/\d{2}$',
                    'title' => 'La date d\'expiration doit être au format MM/AA.',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date d\'expiration ne peut pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/',
                        'message' => 'La date d\'expiration doit être au format MM/AA.',
                    ]),
                ],
            ])
            ->add('select_payment', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Selectionner en tant que paiement principal : ',
                'attr' => [
                    'class' => 'm-3 pointer'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
