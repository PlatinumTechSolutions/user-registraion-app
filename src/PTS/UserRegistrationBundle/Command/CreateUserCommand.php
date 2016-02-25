<?php

namespace PTS\UserRegistrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Config\FileLocator;

use PTS\UserRegistrationBundle\Entity\User;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * configure the name, description and options for this command
     */
    public function configure()
    {
        $this->setName('pts:createUser')
            ->setDescription('create a new user')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username for this user')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password for this user');
    }

    /**
     * execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = trim($input->getArgument('password'));

        if (!($username && $password)) {
            $output->writeln('<comment>Please provide a username and password:</comment>');

            $helper   = $this->getHelper('question');
            $username = $helper->ask($input, $output, $this->getUsernameQuestion());
            $password = $helper->ask($input, $output, $this->getPasswordQuestion());
        }
        if ($this->createUser($username, $password) === true) {
            $output->writeln('<info>DONE</info>');
        } else {
            $output->writeln('<error>ERROR</error>');
        }
    }

    /**
     * Create a new user
     *
     * @param
     */
    public function createUser($username, $password)
    {
        $container = $this->getContainer();

        $entityManager = $container->get('doctrine')->getManager();

        $user = $entityManager->getRepository(User::class)->newUser();

        $encPassword = $container->get('security.password_encoder')->encodePassword($user, $password);

        $user->setUsername($username);
        $user->setPassword($encPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return true;
    }

    // utilities

    public function newQuestion($text)
    {
        return new Question($text);
    }

    public function getUsernameQuestion()
    {
        return $this->newQuestion('  <info>Username</info>: ');
    }

    public function getPasswordQuestion()
    {
        $question = $this->newQuestion('  <info>Password</info>: ');

        $question->setHidden(true)
            ->setHiddenFallback(false)
            ->setValidator([$this, 'validatePassword']);

        return $question;
    }

    public function validatePassword($value)
    {
        if (trim($value) == '') {
            throw new \Exception('The password can not be empty');
        }
        return $value;
    }
}
