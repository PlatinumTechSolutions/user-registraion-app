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
        self::assertEquals(null, $entity->getPassword());
        self::assertEquals(null, $entity->getFirstName());
        self::assertEquals(null, $entity->getLastName());
        self::assertEquals(null, $entity->getSalt());

        self::assertEquals('', $entity->getFullName());
        self::assertEquals('Unnamed User', $entity->getUsername());

        // booleans
        self::assertTrue($entity->getEnabled());
        self::assertFalse($entity->getActivated());
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

        self::assertFalse($entity->hasActivated());
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

        $user->setEmail('email')
            ->setPassword('password')
            ->setFirstName('Joe')
            ->setLastName('Bloggs');

        $data = 'a:5:{i:0;N;i:1;s:5:"email";i:2;s:8:"password";i:3;s:3:"Joe";i:4;s:6:"Bloggs";}';

        self::assertEquals($user->serialize(), $data);
    }

    /**
     * @test
     */
    public function unserialize()
    {
        $data = serialize([12345, 'email', 'password', 'joe', 'bloggs']);
        $user = new User();
        $user->unserialize($data);
        self::assertEquals($user->getId(),        12345);
        self::assertEquals($user->getEmail(),     'email');
        self::assertEquals($user->getPassword(),  'password');
        self::assertEquals($user->getFirstName(), 'joe');
        self::assertEquals($user->getLastName(),  'bloggs');
        self::assertEquals($user->getUsername(),  'joe bloggs');
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

    /**
     * @test
     * @dataProvider fullNameValues
     */
    public function getFullName($first_name, $last_name, $full_name)
    {
        $entity = new User();

        $entity->setFirstName($first_name)
            ->setLastName($last_name);

        self::assertEquals($full_name, $entity->getFullName());
    }

    // data providers

    public function mutatorValues()
    {
        return [
            ['email', sprintf('%s@%s.com', uniqid(), uniqid())],

            ['password',    uniqid()],
            ['newPassword', uniqid()],
            ['firstName',   uniqid()],
            ['lastName',    uniqid()],
            ['lastName',    uniqid()],

            ['enabled',     true],
            ['adminStatus', true],
            ['activated',   true],
        ];
    }

    public function fullNameValues()
    {
        return [
            ['foo', '', 'foo'],
            ['', 'foo', 'foo'],

            ['foo', 'bar', 'foo bar'],

            ['', '', ''],
        ];
    }
}
