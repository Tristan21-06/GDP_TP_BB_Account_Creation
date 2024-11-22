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
                'required'  => true
            ])
            ->add('firstname', TextType::class, [
                'label'     => 'Prénom',
                'required'  => true
            ])
            ->add('password', RepeatedType::class, [
                'first_options'     => ['label' => 'Mot de passe'],
                'second_options'   => ['label' => 'Confirmation de mot de passe'],
                'type'      => PasswordType::class,
                'options'   => ['attr' => ['class' => 'form']],
                'required'  => true
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['class' => 'form']
        ]);
    }
}