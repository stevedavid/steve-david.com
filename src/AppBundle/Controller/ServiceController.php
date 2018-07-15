<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceController extends Controller
{

    /**
     * @Route("/services/{slugCategory}/{slugService}", name="service_index")
     */
    public function indexAction($slugCategory, $slugService)
    {
        $doctrine = $this->get('doctrine');
        $serviceRepository = $doctrine->getRepository('AppBundle:Service');

        $service = $serviceRepository->findOneBySlug($slugService);
        $randomServices = $serviceRepository->findRandom(0);
        $categories = $doctrine->getRepository('AppBundle:Category')->findAll();

        if (empty($service) || $service->getCategory()->getSlug() != $slugCategory) {
            throw new NotFoundHttpException('Service non trouvé');
        }

        return $this->render('service/index.html.twig', [
            'service' => $service,
            'categories' => $categories,
            'random_services' => $randomServices,
        ]);
    }

    /**
     * @Route("/services/{slug}", name="service_category")
     */
    public function categoryAction($slug)
    {
        $doctrine = $this->get('doctrine');

        $category = $doctrine->getRepository('AppBundle:Category')->findOneBySlug($slug);

        return $this->render('service/category.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Not routable
     *
     * Embedded in ::base.html.twig
     */
    function menuAction()
    {
        $doctrine = $this->get('doctrine');

        $categories = $doctrine->getRepository('AppBundle:Category')->findAll();
        $featuredService = $doctrine->getRepository('AppBundle:Service')->findOneBy([
            'isFeatured' => true,
        ], null, 1);
        
        return $this->render('service/menu.html.twig', [
            'categories' => $categories,
            'featured_service' => $featuredService,

        ]);
    }
}
