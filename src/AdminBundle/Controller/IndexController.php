<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
    /**
     * @Route("/admin", name="admin_index_index")
     */
    public function indexAction()
    {
        return $this->render('AdminBundle:index:index.html.twig');
    }

    public function chatAction()
    {
        return $this->render('AdminBundle:index:chat.html.twig');
    }
}
