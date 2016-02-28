<?php

namespace PTS\UserRegistrationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PTS\UserRegistrationBundle\Controller\PublicController;
use PTS\UserRegistrationBundle\Entity\User;

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

    // utilities

    public function getBlankMock($namespace)
    {
        return $this->getMockBuilder($namespace)->disableOriginalConstructor()->getMock();
    }
}
