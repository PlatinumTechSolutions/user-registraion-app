<?php

namespace PTS\UserRegistrationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PTS\UserRegistrationBundle\Controller\PublicController;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserRepository;

class PublicControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function loginActionRedirectToRoute()
    {
        $request  = $this->getBlankMock(Request::class);
        $response = $this->getBlankMock(Response::class);
        $user     = $this->getBlankMock(User::class);

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'redirectToRoute'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getUser')
            ->will(self::returnValue($user));

        $controller->expects(self::once())
            ->method('redirectToRoute')
            ->with(self::equalTo('homepage'))
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->loginAction($request));
    }

    /**
     * @test
     */
    public function loginActionRender()
    {
        $username = 'username-' . uniqid();
        $error    = 'error: ' . uniqid();

        $request  = $this->getBlankMock(Request::class);
        $response = $this->getBlankMock(Response::class);

        $authenticationUtils = $this->getMockBuilder(AuthenticationUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastAuthenticationError', 'getLastUsername'])
            ->getMock();

        $authenticationUtils->expects(self::once())
            ->method('getLastAuthenticationError')
            ->will(self::returnValue($error));

        $authenticationUtils->expects(self::once())
            ->method('getLastUsername')
            ->will(self::returnValue($username));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'get', 'render', 'addFlash'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getUser')
            ->will(self::returnValue(null));

        $controller->expects(self::once())
            ->method('get')
            ->with(self::equalTo('security.authentication_utils'))
            ->will(self::returnValue($authenticationUtils));

        $controller->expects(self::once())
            ->method('render')
            ->with(
                self::equalTo('PTSUserRegistrationBundle:Public:login.html.twig'),
                self::equalTo([
                    'last_username' => $username
                ])
            )
            ->will(self::returnValue($response));

        $controller->expects(self::once())
            ->method('addFlash')
            ->with(self::equalTo('error'), self::equalTo($error));

        self::assertEquals($response, $controller->loginAction($request));
    }

    /**
     * @test
     */
    public function loginCheckAction()
    {
        $request = $this->getBlankMock(Request::class);

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        self::assertEquals(null, $controller->loginCheckAction($request));
    }

    /**
     * @test
     */
    public function forgottenPasswordAction()
    {
        $email = 'foo@bar.com';

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $request->expects(self::once())
            ->method('get')
            ->with(self::equalTo('_email'), self::equalTo(''))
            ->will(self::returnValue($email));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('render')
            ->with(
                self::equalTo('PTSUserRegistrationBundle:Public:forgottenPassword.html.twig'),
                self::equalTo([
                    'last_email' => $email,
                ])
            )
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->forgottenPasswordAction($request));
    }

    /**
     * @test
     */
    public function forgottenPasswordCheckActionNoUser()
    {
        $email = 'foo@bar.com';

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $request->expects(self::once())
            ->method('get')
            ->with(self::equalTo('_email'), self::equalTo(''))
            ->will(self::returnValue($email));

        $respository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByEmail'])
            ->getMock();

        $respository->expects(self::once())
            ->method('findOneByEmail')
            ->will(self::returnValue(null));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'addFlash', 'getRandomSleepTime', 'redirectToRoute'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($respository));

        $controller->expects(self::once())
            ->method('addFlash')
            ->with(
                self::equalTo('error'),
                self::equalTo('Sorry, There is no user matching with email address.')
            );

        $controller->expects(self::once())
            ->method('getRandomSleepTime')
            ->will(self::returnValue(0));

        $controller->expects(self::once())
            ->method('redirectToRoute')
            ->with(self::equalTo('forgottenPassword'))
            ->will(self::returnValue($response));

        self::assertSame($response, $controller->forgottenPasswordCheckAction($request));
    }

    /**
     * @test
     */
    public function resetPassword()
    {
        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('render')
            ->with(self::equalTo('PTSUserRegistrationBundle:Public:resetPassword.html.twig'))
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->resetPassword($request));
    }

    /**
     * @test
     */
    public function registerAction()
    {
        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('render')
            ->with(self::equalTo('PTSUserRegistrationBundle:Public:register.html.twig'))
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->registerAction($request));
    }

    /**
     * @test
     */
    public function getRepository()
    {
        $namespace = User::class;

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo($namespace))
            ->will(self::returnValue($repository));

        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects(self::once())
            ->method('getManager')
            ->will(self::returnValue($manager));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDoctrine'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getDoctrine')
            ->will(self::returnValue($registry));

        self::assertSame($repository, $controller->getRepository($namespace));
    }

    /**
     * @test
     */
    public function getRandomSleepTime()
    {
        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        for ($i = 0; $i < 10; $i++) {
            $value = $controller->getRandomSleepTime();

            $this->assertTrue($value > 1);
            $this->assertTrue($value < 7);
        }
    }

    // utilities

    public function getBlankMock($namespace)
    {
        return $this->getMockBuilder($namespace)->disableOriginalConstructor()->getMock();
    }
}
