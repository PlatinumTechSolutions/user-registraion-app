<?php

namespace PTS\UserRegistrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends Controller
{
    /**
     * @Route("/admin")
     */
    public function indexAction()
    {
        return $this->render('PTSUserRegistrationBundle:Admin:index.html.twig');
    }
}
