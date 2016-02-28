<?php

namespace PTS\UserRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserHash;

class UserHashTest extends WebTestCase
{
    /**
     * @test
     */
    public function blankEntity()
    {
        $entity = new UserHash();

        self::assertEquals(null, $entity->getId());
        self::assertEquals(null, $entity->getType());
        self::assertEquals(null, $entity->getValue());
        self::assertEquals(null, $entity->getUser());
    }

    /**
     * @test
     * @dataProvider mutatorValues
     */
    public function mutators($name, $value)
    {
        $setter = sprintf('set%s', ucfirst(strtolower($name)));
        $getter = sprintf('get%s', ucfirst(strtolower($name)));

        $entity = new UserHash();

        $entity->$setter($value);
        self::assertEquals($value, $entity->$getter());
    }

    /**
     * @test
     * @dataProvider invalidTypes
     * @expectedException PTS\UserRegistrationBundle\Exception\ValidationException
     */
    public function validateException($type)
    {
        $userHash = new UserHash();
        $userHash->setType($type);
        $userHash->validate();
    }

    /**
     * @test
     * @dataProvider validTypes
     */
    public function validateTrue($type)
    {
        $userHash = new UserHash();
        $userHash->setType($type);

        self::assertTrue($userHash->validate());
    }

    // data providers

    public function mutatorValues()
    {
        return [
            ['type', uniqid()],
            ['value', uniqid()],
            ['user', new User()],
        ];
    }

    public function invalidTypes()
    {
        return [
            [''],
            [null],
            ['foobar'],
            [234],
        ];
    }

    public function validTypes()
    {
        return [
            [UserHash::TYPE_EMAIL_CONFIRMATION],
            [UserHash::TYPE_PASSWORD_RESET],
        ];
    }
}
