<?php

namespace PTS\UserRegistrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PTS\UserRegistrationBundle\Entity\User;
use PTS\UserRegistrationBundle\Entity\UserHash;

class PublicController extends Controller
{
    /**
     * @Route("/login", name="login_route")
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser()) {
            // we have logged in, redirect to the homepage
            return $this->redirectToRoute('homepage');
        }
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->render('PTSUserRegistrationBundle:Public:login.html.twig', [
            // last username entered by the user
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    /**
    * @Route("/login_check", name="login_check")
    */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/login/forgottenPassword", name="forgottenPassword")
     */
    public function forgottenPasswordAction(Request $request)
    {
        return $this->render('PTSUserRegistrationBundle:Public:forgottenPassword.html.twig', [
            'last_email' => $request->get('_email', ''),
        ]);
    }

    /**
     * @Route("/login/forgottenPassword_check", name="forgottenPassword_check")
     */
    public function forgottenPasswordCheckAction(Request $request)
    {
        $user = $this->getRepository(User::class)->findOneByEmail($request->get('_email', ''));
        if (!$user) {
            $this->addFlash('error', 'Sorry, There is no user matching with email address.');

            // We sleep to make it a little harder for people to brute force valid email addresses
            sleep($this->getRandomSleepTime());

            return $this->redirectToRoute('forgottenPassword');
        }

        if (!$user->isEnabled()) {
            $this->addFlash('warning', 'It looks like you\'ve not confirmed your email address, so we\'ve resent the confirmation email.');
            $userHash = $this->generateUserHash($user, UserHash::TYPE_EMAIL_CONFIRMATION);
        } else {
            $this->addFlash('success', 'Please check you email for instructions on home to reset your password.');
            $userHash = $this->generateUserHash($user, UserHash::TYPE_PASSWORD_RESET);
        }

        // TODO: Send Email

        return $this->redirectToRoute('forgottenPassword');
    }

    /**
     * @Route("/login/resetPassword", name="resetPassword")
     */
    public function resetPassword(Request $request)
    {
        return $this->render('PTSUserRegistrationBundle:Public:resetPassword.html.twig');
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        return $this->render('PTSUserRegistrationBundle:Public:register.html.twig', [
            'first_name' => $request->get('_first_name', ''),
            'last_name'  => $request->get('_last_name', ''),
            'email'      => $request->get('_email', ''),
        ]);
    }

    /**
     * @Route("/register/new", name="register_new")
     */
    public function registerNewAction(Request $request)
    {
        return $this->redirectToRoute('register');
    }

    // Utilities

    /**
     * Generate a new UserHash for a given user of a given type
     *
     * @param User   $user
     * @param string $type
     */
    public function generateUserHash(User $user, $type)
    {
        $repository = $this->getRepository(UserHash::class);

        $userHash = $repository->newUserHash()
            ->setUser($user)
            ->setType($type)
            ->setValue($repository->generateNewValue());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($userHash);
        $entityManager->flush();

        return $userHash;
    }

    /**
     * Get a repository for a given namespace
     */
    public function getRepository($namespace)
    {
         return $this->getDoctrine()->getManager()->getRepository($namespace);
    }

    /**
     * Return a random number of seconds to sleep for
     * @return int
     */
    public function getRandomSleepTime()
    {
        return rand(2,6);
    }
}
