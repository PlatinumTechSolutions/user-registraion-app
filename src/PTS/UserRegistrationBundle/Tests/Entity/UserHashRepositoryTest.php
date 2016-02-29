<?php

namespace PTS\UserRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PTS\UserRegistrationBundle\Entity\UserHash;
use PTS\UserRegistrationBundle\Entity\UserHashRepository;

class UserHashRepositoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function newUser()
    {
        $repository = $this->getMockBuilder(UserHashRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        self::assertInstanceOf(UserHash::class, $repository->newUserHash());
    }

    /**
     * @test
     */
    public function generateNewValue()
    {
        $repository = $this->getMockBuilder(UserHashRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $value = $repository->generateNewValue();

        self::assertEquals(73, strlen($value));
    }
}
