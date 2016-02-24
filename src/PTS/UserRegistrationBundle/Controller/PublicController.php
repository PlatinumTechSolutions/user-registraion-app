<?php

namespace PTS\UserRegistrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
            'last_username' => $request->get('email', ''),
        ]);
    }

    /**
     * @Route("/login/forgottenPassword_check", name="forgottenPassword_check")
     */
    public function forgottenPasswordCheckAction(Request $request)
    {
        $this->addFlash('error', 'DOH!');

        return $this->redirectToRoute('forgottenPassword');
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        return $this->render('PTSUserRegistrationBundle:Public:register.html.twig');
    }
}
