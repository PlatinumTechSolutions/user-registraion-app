<?php

namespace PTS\UserRegistrationBundle\Tests\Command;

use PTS\UserRegistrationBundle\Command\CreateUserCommand;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateUserCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function configure()
    {
        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setDescription', 'addArgument'])
            ->getMock();

        $command->expects(self::once())
            ->method('setName')
            ->with(self::equalTo('pts:createUser'))
            ->will(self::returnValue($command));

        $command->expects(self::once())
            ->method('setDescription')
            ->with(self::equalTo('create a new user'))
            ->will(self::returnValue($command));

        $command->expects(self::exactly(2))
            ->method('addArgument')
            ->withConsecutive(
                ['username', InputArgument::OPTIONAL, 'Username for this user'],
                ['password', InputArgument::OPTIONAL, 'Password for this user']
            )
            ->will(self::returnValue($command));

        self::assertNull($command->configure());
    }

    /**
     * @test
     */
    public function newQuestion()
    {
        $text = 'What is the meaning of life the universe and everything?';

        $question = new Question($text);

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['configure'])
            ->getMock();

        $this->assertEquals($question, $command->newQuestion($text));
    }

    /**
     * @test
     */
    public function getUsernameQuestion()
    {
        $question = $this->getBlankMock(Question::class);

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['newQuestion'])
            ->getMock();

        $command->expects(self::once())
            ->method('newQuestion')
            ->with(self::equalTo('  <info>Username</info>: '))
            ->will(self::returnValue($question));

        self::assertEquals($question, $command->getUsernameQuestion());
    }

    /**
     * @test
     */
    public function getPasswordQuestion()
    {
        $question = $this->getMockBuilder(Question::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHidden', 'setHiddenFallback', 'setValidator'])
            ->getMock();

        $question->expects(self::once())
            ->method('setHidden')
            ->will(self::returnValue($question));

        $question->expects(self::once())
            ->method('setHiddenFallback')
            ->will(self::returnValue($question));

        $question->expects(self::once())
            ->method('setValidator')
            ->will(self::returnValue($question));

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['newQuestion'])
            ->getMock();

        $command->expects(self::once())
            ->method('newQuestion')
            ->with(self::equalTo('  <info>Password</info>: '))
            ->will(self::returnValue($question));

        self::assertEquals($question, $command->getPasswordQuestion());
    }

    /**
     * @test
     * @dataProvider executeWithArgumentsValues
     */
    public function executeWithArguments($creatUserResponse, $writelnMessage)
    {
        $username = uniqid();
        $password = uniqid();

        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getArgument'])
            ->getMockForAbstractClass();

        $input->expects(self::exactly(2))
            ->method('getArgument')
            ->withConsecutive(
                [self::equalTo('username')],
                [self::equalTo('password')]
            )
            ->will(self::onConsecutiveCalls($username, $password));

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['createUser'])
            ->getMock();

        $command->expects(self::once())
            ->method('createUser')
            ->with(self::equalTo($username), self::equalTo($password))
            ->will(self::returnValue($creatUserResponse));

        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeln'])
            ->getMockForAbstractClass();

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo($writelnMessage));

        $command->execute($input, $output);
    }

    /**
     * @test
     */
    public function executeWithoutArguments()
    {
        $username = uniqid();
        $password = uniqid();

        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getArgument'])
            ->getMockForAbstractClass();

        $input->expects(self::exactly(2))
            ->method('getArgument')
            ->withConsecutive(
                [self::equalTo('username')],
                [self::equalTo('password')]
            )
            ->will(self::returnValue(null));

        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeln'])
            ->getMockForAbstractClass();

        $output->expects(self::exactly(2))
            ->method('writeln')
            ->withConsecutive(
                [self::equalTo('<comment>Please provide a username and password:</comment>')],
                [self::equalTo('<info>DONE</info>')]
            );

        $question1 = $this->getBlankMock(Question::class);
        $question2 = $this->getBlankMock(Question::class);

        $helper = $this->getMockBuilder(QuestionHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['ask'])
            ->getMockForAbstractClass();

        $helper->expects(self::exactly(2))
            ->method('ask')
            ->withConsecutive(
                [$input, $output, $question1],
                [$input, $output, $question2]
            );

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHelper', 'getUsernameQuestion', 'getPasswordQuestion', 'createUser'])
            ->getMockForAbstractClass();

        $command->expects(self::once())
            ->method('getHelper')
            ->with(self::equalTo('question'))
            ->will(self::returnValue($helper));

        $command->expects(self::once())
            ->method('getUsernameQuestion')
            ->will(self::returnValue($question1));

        $command->expects(self::once())
            ->method('getPasswordQuestion')
            ->will(self::returnValue($question2));

        $command->expects(self::once())
            ->method('createUser')
            ->will(self::returnValue(true));

        $command->execute($input, $output);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage The password can not be empty
     * @dataProvider invalidPasswords
     */
    public function validatePasswordException($password)
    {
        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $command->validatePassword($password);
    }

    /**
     * @test
     */
    public function validatePassword()
    {
        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $password = uniqid();

        self::assertEquals($password, $command->validatePassword($password));
    }

    /**
     * @test
     */
    public function createUser()
    {
        $username    = 'user-569e57646c372';
        $password    = 'password';
        $encPassword = md5($password);

        $user = $this->getBlankMock(User::class);

        $user->expects(self::once())->method('setUsername')->with($username);
        $user->expects(self::once())->method('setPassword')->with($encPassword);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['newUser'])
            ->getMock();

        $userRepository->expects(self::once())
            ->method('newUser')
            ->will(self::returnValue($user));

        $manager = $this->getBlankMock(EntityManager::class);
        $manager->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->will(self::returnValue($userRepository));

        $manager->expects(self::once())
            ->method('persist')
            ->with(self::equalTo($user));

        $manager->expects(self::once())
            ->method('flush');

        $doctrine = $this->getBlankMock(Registry::class);

        $doctrine->expects(self::once())
            ->method('getManager')
            ->will(self::returnValue($manager));

        $password_encoder = $this->getBlankMock(BasePasswordEncoder::class);

        $password_encoder->expects(self::once())
            ->method('encodePassword')
            ->with(self::equalTo($user), self::equalTo($password))
            ->will(self::returnValue($encPassword));

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [self::equalTo('doctrine')],
                [self::equalTo('security.password_encoder')]
            )
            ->will(self::onConsecutiveCalls($doctrine, $password_encoder));

        $command = $this->getMockBuilder(CreateUserCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContainer'])
            ->getMock();

        $command->expects(self::once())
            ->method('getContainer')
            ->will(self::returnValue($container));

        self::assertTrue($command->createUser($username, $password));
    }

    // data providers

    public function executeWithArgumentsValues()
    {
        return [
            [true,  '<info>DONE</info>'],
            [false, '<error>ERROR</error>'],
        ];
    }

    public function invalidPasswords()
    {
        return [
            [null],
            [''],
            ['      '],
            [false],
        ];
    }

    // utilities

    public function getBlankMock($namespace)
    {
        return $this->getMockBuilder($namespace)->disableOriginalConstructor()->getMock();
    }
}
