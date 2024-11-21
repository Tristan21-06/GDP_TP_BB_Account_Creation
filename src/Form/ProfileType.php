<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
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
                'type'      => PasswordType::class,
                'options'   => ['attr' => ['class' => 'form']],
                'required'  => true
            ])
            ->add('public', CheckboxType::class, [
                'label'     => 'J\'accepte les Termes & Conditions',
                'required'  => true,
                'mapped'    => false
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