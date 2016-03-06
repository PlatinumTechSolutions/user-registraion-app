<?php

namespace PTS\UserRegistrationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use PTS\UserRegistrationBundle\Controller\PublicController;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserRepository;
use PTS\UserRegistrationBundle\Entity\UserHash;
use PTS\UserRegistrationBundle\Entity\UserHashRepository;
use PTS\UserRegistrationBundle\Type\UserType;

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

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByEmail'])
            ->getMock();

        $repository->expects(self::once())
            ->method('findOneByEmail')
            ->will(self::returnValue(null));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'addFlash', 'getRandomSleepTime', 'redirectToRoute'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($repository));

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
    public function forgottenPasswordCheckActionDisabledUser()
    {
        $email = 'foo@bar.com';

        $user = $this->getBlankMock(User::class);

        $user->expects(self::once())
            ->method('isEnabled')
            ->will(self::returnValue(false));

        $userHash = $this->getBlankMock(UserHash::class);

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $request->expects(self::once())
            ->method('get')
            ->with(self::equalTo('_email'), self::equalTo(''))
            ->will(self::returnValue($email));

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByEmail'])
            ->getMock();

        $repository->expects(self::once())
            ->method('findOneByEmail')
            ->will(self::returnValue($user));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'addFlash', 'generateUserHash', 'redirectToRoute'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('addFlash')
            ->with(
                self::equalTo('warning'),
                self::equalTo('It looks like you\'ve not confirmed your email address, so we\'ve resent the confirmation email.')
            );

        $controller->expects(self::once())
            ->method('generateUserHash')
            ->with(
                self::equalTo($user),
                self::equalTo(UserHash::TYPE_EMAIL_CONFIRMATION)
            )
            ->will(self::returnValue($userHash));

        $controller->expects(self::once())
            ->method('redirectToRoute')
            ->with(self::equalTo('forgottenPassword'))
            ->will(self::returnValue($response));

        self::assertSame($response, $controller->forgottenPasswordCheckAction($request));
    }

    /**
     * @test
     */
    public function forgottenPasswordCheckActionEnabledUser()
    {
        $email = 'foo@bar.com';

        $user = $this->getBlankMock(User::class);

        $user->expects(self::once())
            ->method('isEnabled')
            ->will(self::returnValue(true));

        $userHash = $this->getBlankMock(UserHash::class);

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $request->expects(self::once())
            ->method('get')
            ->with(self::equalTo('_email'), self::equalTo(''))
            ->will(self::returnValue($email));

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByEmail'])
            ->getMock();

        $repository->expects(self::once())
            ->method('findOneByEmail')
            ->will(self::returnValue($user));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'addFlash', 'generateUserHash', 'redirectToRoute'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('addFlash')
            ->with(
                self::equalTo('success'),
                self::equalTo('Please check you email for instructions on home to reset your password.')
            );

        $controller->expects(self::once())
            ->method('generateUserHash')
            ->with(
                self::equalTo($user),
                self::equalTo(UserHash::TYPE_PASSWORD_RESET)
            )
            ->will(self::returnValue($userHash));

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
        $hashValue = uniqid();

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);
        $userHash = $this->getBlankMock(UserHash::class);

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy'])
            ->getMock();

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(self::equalTo([
                'type'  => UserHash::TYPE_PASSWORD_RESET,
                'value' => $hashValue,
            ]))
            ->will(self::returnValue($userHash));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(UserHash::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('render')
            ->with(
                self::equalTo('PTSUserRegistrationBundle:Public:resetPassword.html.twig'),
                self::equalTo([
                    'userHash' => $userHash,
                ])
            )
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->resetPassword($request, $hashValue));
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Invalid UserHash provided
     */
    public function resetPasswordNotFoundException()
    {
        $hashValue = uniqid();

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy'])
            ->getMock();

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(self::equalTo([
                'type'  => UserHash::TYPE_PASSWORD_RESET,
                'value' => $hashValue,
            ]))
            ->will(self::returnValue(null));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(UserHash::class))
            ->will(self::returnValue($repository));

        $request = $this->getBlankMock(Request::class);

        $controller->resetPassword($request, $hashValue);
    }

    /**
     * @test
     */
    public function registerAction()
    {
        $user = $this->getBlankMock(User::class);

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['newUser'])
            ->getMock();

        $repository->expects(self::once())
            ->method('newUser')
            ->will(self::returnValue($user));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleRequest', 'isSubmitted', 'isValid', 'createView'])
            ->getMock();

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'createForm', 'render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('createForm')
            ->with(
                self::equalTo(UserType::class),
                self::equalTo($user)
            )
            ->will(self::returnValue($form));

        $controller->expects(self::once())
            ->method('render')
            ->with(
                self::equalTo('PTSUserRegistrationBundle:Public:register.html.twig'),
                self::equalTo([
                    'form' => null
                ]))
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->registerAction($request));
    }

    /**
     * @test
     */
    public function registerActionSubmitted()
    {
        $user = $this->getBlankMock(User::class);

        $user->expects(self::once())->method('getNewPassword');
        $user->expects(self::once())->method('setPassword');

        $response = $this->getBlankMock(Response::class);
        $request  = $this->getBlankMock(Request::class);

        $repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['newUser'])
            ->getMock();

        $repository->expects(self::once())
            ->method('newUser')
            ->will(self::returnValue($user));

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleRequest', 'isSubmitted', 'isValid'])
            ->getMock();

        $form->expects(self::once())->method('isSubmitted')->will(self::returnValue(true));
        $form->expects(self::once())->method('isValid')->will(self::returnValue(true));

        $manager = $this->getBlankMock(EntityManager::class);
        $manager->expects(self::once())->method('persist')->with($user);
        $manager->expects(self::once())->method('flush');

        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects(self::once())
            ->method('getManager')
            ->will(self::returnValue($manager));

        $password_encoder = $this->getBlankMock(BasePasswordEncoder::class);
        $password_encoder->expects(self::once())->method('encodePassword');

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'createForm', 'get', 'getDoctrine', 'render'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(User::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('createForm')
            ->with(
                self::equalTo(UserType::class),
                self::equalTo($user)
            )
            ->will(self::returnValue($form));

        $controller->expects(self::once())
            ->method('get')
            ->with(self::equalTo('security.password_encoder'))
            ->will(self::returnValue($password_encoder));

        $controller->expects(self::once())
            ->method('getDoctrine')
            ->will(self::returnValue($registry));

        $controller->expects(self::once())
            ->method('render')
            ->with(self::equalTo('PTSUserRegistrationBundle:Public:registerComplete.html.twig'))
            ->will(self::returnValue($response));

        self::assertEquals($response, $controller->registerAction($request));
    }

    /**
     * @test
     * @dataProvider userHashTypes
     */
    public function generateUserHash($type)
    {
        $user = $this->getBlankMock(User::class);

        $value = uniqid();

        $userHash = $this->getBlankMock(UserHash::class);

        $userHash->expects(self::once())
            ->method('setUser')
            ->with(self::equalTo($user))
            ->will(self::returnValue($userHash));

        $userHash->expects(self::once())
            ->method('setType')
            ->with(self::equalTo($type))
            ->will(self::returnValue($userHash));

        $userHash->expects(self::once())
            ->method('setValue')
            ->with(self::equalTo($value))
            ->will(self::returnValue($userHash));

        $repository = $this->getBlankMock(UserHashRepository::class);

        $repository->expects(self::once())
            ->method('newUserHash')
            ->will(self::returnValue($userHash));

        $repository->expects(self::once())
            ->method('generateNewValue')
            ->will(self::returnValue($value));

        $manager = $this->getBlankMock(EntityManager::class);
        $manager->expects(self::once())->method('persist')->with($userHash);
        $manager->expects(self::once())->method('flush');

        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects(self::once())
            ->method('getManager')
            ->will(self::returnValue($manager));

        $controller = $this->getMockBuilder(PublicController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getDoctrine'])
            ->getMock();

        $controller->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(UserHash::class))
            ->will(self::returnValue($repository));

        $controller->expects(self::once())
            ->method('getDoctrine')
            ->will(self::returnValue($registry));

        self::assertSame($userHash, $controller->generateUserHash($user, $type));
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

        $manager = $this->getBlankMock(EntityManager::class);

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

    // data providers

    public function userHashTypes()
    {
        return [
            [UserHash::TYPE_PASSWORD_RESET],
            [UserHash::TYPE_EMAIL_CONFIRMATION],
        ];
    }
}
