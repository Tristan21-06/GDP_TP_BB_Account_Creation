<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, [
                'label'     => 'Nom',
                'required'  => true,
                'attr'  => [
                    'class' => 'form-control',
                ],
                'row_attr'  => [
                    'class' => 'col-6',
                ]
            ])
            ->add('firstname', TextType::class, [
                'label'     => 'Prénom',
                'required'  => true,
                'attr'  => [
                    'class' => 'form-control',
                ],
                'row_attr'  => [
                    'class' => 'col-6',
                ]
            ])
            ->add('password', RepeatedType::class, [
                'first_options'     => ['label' => 'Mot de passe'],
                'second_options'   => ['label' => 'Confirmation de mot de passe'],
                'type'      => PasswordType::class,
                'options'           => [
                    'attr' => [
                        'class' => 'form-control',
                        'data-pwd' => '',
                    ],
                    'row_attr' => [
                        'class' => 'col-6',
                    ],
                    'empty_data' => '',
                ],
                'error_bubbling' => true,
                'required'  => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary col-6 col-offset-2',
                ],
                'row_attr' => [
                    'class' => 'text-center',
                ],
                'label' => 'Modifier mes informations'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['class' => 'form row']
        ]);
    }
}