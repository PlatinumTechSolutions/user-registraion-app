<?php

namespace PTS\UserRegistrationBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PTS\UserRegistrationBundle\Entity\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, [
                'attr' => ['placeholder' => 'First Name']
            ])
            ->add('last_name', TextType::class, [
                'attr' => ['placeholder' => 'Last Name']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Email']
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['attr' => ['placeholder' => 'Password']],
                'second_options' => ['attr' => ['placeholder' => 'Confirm Password']],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
