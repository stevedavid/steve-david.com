<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\ServiceType;
use AppBundle\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    /**
     * @Route("/admin/services", name="admin_service_index")
     */
    public function indexAction()
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();

        $services = $em->getRepository('AppBundle:Service')->findAll();

        return $this->render('AdminBundle:service:index.html.twig', [
            'services' => $services,
        ]);
    }


    /**
     * @Route("/admin/services/ajouter", name="admin_service_ajouter", requirements={"idService": "\d+"})
     */
    public function ajouterAction(Request $request)
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();


        $form = $this->createForm(ServiceType::class, new Service());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service = $form->getData();

            $em->persist($service);
            $em->flush();

            $this->redirectToRoute('admin_service_index');

        }

        return $this->render('AdminBundle:service:formulaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/services/{idService}", name="admin_service_modifier", requirements={"idService": "\d+"})
     */
    public function modifierAction($idService, Request $request)
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();

        $service = $em->getRepository('AppBundle:Service')->findOneById($idService);

        $form = $this->createForm(ServiceType::class, $service);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service = $form->getData();

            $em->persist($service);
            $em->flush();

            $this->redirectToRoute('admin_service_index');
        }

        return $this->render('AdminBundle:service:formulaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/services/supprimer/{idService}", name="admin_service_supprimer", requirements={"idService": "\d+"})
     */
    public function supprimerAction($idService)
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();

        $service = $em->getRepository('AppBundle:Service')->findOneById($idService);

        $em->remove($service);
        $em->flush();

        return $this->redirectToRoute('admin_service_index');
    }

    /**
     * @Route("/admin/services/upload", name="admin_service_upload")
     */
    public function uploadction(Request $request)
    {


        $files = $request->files;

        var_dump($files);

        return new Response();
    }

    /**
     * @Route("/admin/services/{idService}/mettre-en-avant", name="admin_service_feature", requirements={"idService": "\d+"})
     */
    public function featureAction($idService)
    {
        $doctrine = $this->get('doctrine');

        $em = $doctrine->getManager();

        $services = $em->getRepository('AppBundle:Service')->findAll();

        foreach ($services as $service) {
            if ($service->getId() == $idService) {
                $service->setIsFeatured(true);
            } else {
                $service->setIsFeatured(false);
            }

            $em->persist($service);
        }

        $em->flush();

        return $this->forward('AdminBundle:Service:index');

    }
}
