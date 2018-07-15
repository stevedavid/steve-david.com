<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BlogController extends Controller
{
    /**
     * @Route("/admin/blog/", name="admin_blog_index")
     */
    public function indexAction()
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();

        $posts = $em->getRepository('AppBundle:Post')->findAll();

        return $this->render('AdminBundle:blog:index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/admin/blog/{idPost}", name="admin_blog_modifier", requirements={"idPost": "\d+"})
     */
    public function modifierAction($idPost)
    {

    }

    /**
     * @Route("/admin/blog/", name="admin_blog_supprimer")
     */
    public function supprimerAction()
    {

    }

}
