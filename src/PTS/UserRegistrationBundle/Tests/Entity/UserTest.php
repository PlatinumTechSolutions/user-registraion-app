<?php

namespace PTS\UserRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserHash;

class UserTest extends WebTestCase
{
    /**
     * @test
     */
    public function blankEntity()
    {
        $entity = new User();

        self::assertEquals(null, $entity->getId());
        self::assertEquals(null, $entity->getUsername());
        self::assertEquals(null, $entity->getPassword());
        self::assertEquals(null, $entity->getFirstName());
        self::assertEquals(null, $entity->getLastName());
        self::assertEquals(null, $entity->getSalt());

        // booleans
        self::assertTrue($entity->getEnabled());
        self::assertFalse($entity->getAdminStatus());

        // arrays
        self::assertEquals(['ROLE_USER'], $entity->getRoles());

        // Array Collections
        self::assertEquals(new ArrayCollection(), $entity->getUserHashes());

        // advanced user interface (not in use at the moment)

        self::assertTrue($entity->eraseCredentials());
        self::assertTrue($entity->isAccountNonExpired());
        self::assertTrue($entity->isAccountNonLocked());
        self::assertTrue($entity->isCredentialsNonExpired());
        self::assertTrue($entity->isEnabled());
    }

    /**
     * @test
     * @dataProvider mutatorValues
     */
    public function mutators($name, $value)
    {
        $setter = sprintf('set%s', ucfirst(strtolower($name)));
        $getter = sprintf('get%s', ucfirst(strtolower($name)));

        $entity = new User();

        $entity->$setter($value);
        self::assertEquals($value, $entity->$getter());
    }

    /**
     * @test
     */
    public function serialize()
    {
        $user = new User();

        $user->setUsername('username')->setPassword('password');

        $data = 'a:3:{i:0;N;i:1;s:8:"username";i:2;s:8:"password";}';

        self::assertEquals($user->serialize(), $data);
    }

    /**
     * @test
     */
    public function unserialize()
    {
        $data = serialize([12345, 'username', 'password']);
        $user = new User();
        $user->unserialize($data);
        self::assertEquals($user->getId(),       12345);
        self::assertEquals($user->getUsername(), 'username');
        self::assertEquals($user->getPassword(), 'password');
    }

    /**
     * @test
     */
    public function adminRole()
    {
        $user = new User();

        self::assertEquals(['ROLE_USER'], $user->getRoles());

        $user->setAdminStatus(true);

        self::assertEquals(['ROLE_ADMIN'], $user->getRoles());
    }

    /**
     * @test
     */
    public function userHashes()
    {
        $user = new User();

        $userHash = $repository = $this->getMockBuilder(UserHash::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUser'])
            ->getMock();

        $userHash->expects(self::once())->method('setUser')->with(self::equalTo($user));

        self::assertEquals($user, $user->addUserHash($userHash));

        $userHashes = new ArrayCollection();
        $userHashes[] = $userHash;

        self::assertEquals($userHashes, $user->getUserHashes());

        $user->removeUserHash($userHash);

        self::assertEquals(new ArrayCollection(), $user->getUserHashes());
    }

    // data providers

    public function mutatorValues()
    {
        return [
            ['username',  uniqid()],
            ['password',  uniqid()],
            ['firstName', uniqid()],
            ['lastName',  uniqid()],
            ['lastName',  uniqid()],

            ['email', sprintf('%s@%s.com', uniqid(), uniqid())],

            ['enabled',     true],
            ['adminStatus', true],
        ];
    }
}
