<?php

namespace PTS\UserRegistrationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PTS\UserRegistrationBundle\Controller\DefaultController;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function indexAction()
    {
        $request  = $this->getBlankMock(Request::class);
        $response = $this->getBlankMock(Response::class);

        $controller = $this->getMockBuilder(DefaultController::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('render')
            ->with(self::equalTo('PTSUserRegistrationBundle:Default:index.html.twig'))
            ->will(self::returnValue($response));

        self::assertSame($response, $controller->indexAction($request));
    }

    // utilities

    public function getBlankMock($namespace)
    {
        return $this->getMockBuilder($namespace)->disableOriginalConstructor()->getMock();
    }
}
