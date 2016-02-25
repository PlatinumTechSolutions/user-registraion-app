<?php

namespace PTS\UserRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserRepository;

class UserRepositoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function newUser()
    {
        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        self::assertInstanceOf(User::class, $repository->newUser());
    }
}
