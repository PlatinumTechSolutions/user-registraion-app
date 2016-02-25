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

    // utilities

    public function getBlankMock($namespace)
    {
        return $this->getMockBuilder($namespace)->disableOriginalConstructor()->getMock();
    }
}
