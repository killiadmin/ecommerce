<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'required' => true,
                'label' => 'Titre',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Description',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ],
                'required' => true
            ])
            ->add('pictureFile', FileType::class, [
                'label' => 'Pictures',
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control mb-5'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'attr' => [
                    'class' => 'form-control mb-5'
                ],
                'label_attr' => [
                    'class' => 'fw-bold'
                ],
                'required' => true
            ])
            ->add('active', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input mb-5'
                ],
                'label_attr' => [
                    'class' => 'form-check-label fw-bold'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
