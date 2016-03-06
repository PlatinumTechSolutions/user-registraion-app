<?php

namespace PTS\UserRegistrationBundle\Tests\Type;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Type\UserType;

class UserTypeTest extends WebTestCase
{
    /**
     * @test
     */
    public function buildForm()
    {
        $builder = $this->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMockForAbstractClass();

        $builder->expects(self::exactly(4))
            ->method('add')
            ->withConsecutive(
                [
                    'first_name', TextType::class, [
                        'attr' => ['placeholder' => 'First Name']
                    ]
                ],
                [
                    'last_name', TextType::class, [
                        'attr' => ['placeholder' => 'Last Name']
                    ]
                ],
                [
                    'email', EmailType::class, [
                        'attr' => ['placeholder' => 'Email']
                    ]
                ],
                [
                    'newPassword', RepeatedType::class, [
                        'type'           => PasswordType::class,
                        'first_options'  => ['attr' => ['placeholder' => 'Password']],
                        'second_options' => ['attr' => ['placeholder' => 'Confirm Password']],
                    ]
                ]
            )
            ->will(self::returnValue($builder));

        $type = new UserType();
        $type->buildForm($builder, []);
    }

    /**
     * @test
     */
    public function configureOptions()
    {
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDefaults'])
            ->getMock();

        $resolver->expects(self::once())
            ->method('setDefaults')
            ->with(self::equalTo([
                'data_class' => User::class,
            ]));

        $type = new UserType();
        $type->configureOptions($resolver);
    }
}
