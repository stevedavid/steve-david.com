<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    /**
     * @Route("/blog/{slugTheme}", name="blog_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($slugTheme)
    {
        $doctrine = $this->get('doctrine');

        $theme = $doctrine->getRepository('AppBundle:Theme')->findOneBySlug($slugTheme);

        if (empty($theme)) {
            throw new NotFoundHttpException('Thème non trouvé');
        }

        return $this->render('blog/index.html.twig', [
            'theme' => $theme,
        ]);
    }

    /**
     * @Route("/blog/{date}/{slug}", name="blog_post")
     */
    public function postAction($date, $slug)
    {
        $doctrine = $this->get('doctrine');

        $post = $doctrine->getRepository('AppBundle:Post')->findOneBySlug($slug);

        if (empty($post)) {
            throw new NotFoundHttpException('Billet non trouvé');
        }

        return $this->render('blog/post.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Not routable
     *
     * Called in ::base.html.twig
     */
    function menuAction()
    {
        $doctrine = $this->get('doctrine');

        $themes = $doctrine->getRepository('AppBundle:Theme')->findAll();

        return $this->render('blog/menu.html.twig', [
            'themes' => $themes,
        ]);
    }

    /**
     * Not routable
     *
     * Called in :blog:index.html.twig
     * Called in :blog:post.html.twig
     */
    function sidebarAction($idTheme)
    {

        $doctrine = $this->get('doctrine');

        $themes = $doctrine->getRepository('AppBundle:Theme')->findAll();
        $tags = $doctrine->getRepository('AppBundle:Tag')->findAll();
        $postThumbsInTheme = $doctrine->getRepository('AppBundle:Post')->findByTheme($idTheme);
        $postThumbs = $doctrine->getRepository('AppBundle:Post')->findBy([], [
            'date' => 'DESC'
        ], 5);



        return $this->render('blog/sidebar.html.twig', [
            'themes' => $themes,
            'tags' => $tags,
            'post_thumbs_in_theme' => $postThumbsInTheme,
            'post_thumbs' => $postThumbs,
        ]);
    }
}