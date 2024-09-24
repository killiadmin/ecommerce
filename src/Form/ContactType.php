<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname_contact',TextType::class, [
                'required' => false,
                'label' => 'Votre prénom',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lastname_contact',TextType::class, [
                'required' => false,
                'label' => 'Votre nom',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email_contact', EmailType::class, [
                'required' => true,
                'label' => 'Votre adresse email',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('object_msg', ChoiceType::class, [
                'required' => true,
                'label' => 'Objet',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    'Informations' => 'Informations',
                    'Retour' => 'Retour',
                    'Boite à idées' => 'Boite à idées',
                    'Autres ...' => 'Autres ...'
                ]
            ])
            ->add('content_msg', TextareaType::class, [
                'required' => true,
                'label' => 'Description',
                'label_attr' => [
                    'class' => 'fw-bold form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
