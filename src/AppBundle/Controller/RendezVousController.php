<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Service;
use AppBundle\Form\CommandeType;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RendezVousController extends Controller
{
    const PAYMENT_MODES = [Cart::MODE_CASH, Cart::MODE_BANK_CARD, Cart::MODE_PAYPAL];
    const REMOTE = 'a-distance';

    /**
     * @Route("/prendre-rendez-vous/service/{mode}", name="rendez_vous_service", defaults={"mode": "face-a-face"}, requirements={"mode": "a-distance|face-a-face"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serviceAction($mode)
    {
        // Initialisation;
        $doctrine = $this->get('doctrine');
        $response = new Response();
        $request = Request::createFromGlobals();
        $distance = $mode == 'a-distance';

        // Récupération du panier;
        $cart = $this->getCart($response);
        $cart->setService(null)->setAppointment(null)->setPayment(null);

        // Saisie du mode du rendez-vous (à distance ou en face-à-face);
        $em = $this->getDoctrine()->getManager();
        $cart->setRemote($distance);
        $em->persist($cart);
        $em->flush();

        // Récupération des services;
        $categories = $doctrine->getRepository('AppBundle:Category')->findAll();

        // Création de la vue;
        $response->setContent($this->renderView('rendez-vous/service.html.twig', [
            'categories' => $categories,
            'service_a_distance' => $distance,
            'cart' => $cart,
        ]));

        // Envoie de la réponse;
        return $response;
    }

    private function getCart(&$response)
    {
        $request = Request::createFromGlobals();
        $doctrine = $this->get('doctrine');
        $uniqid = uniqid();
        $distance = $request->request->get('mode') == self::REMOTE;

        $cookie = $request->cookies->get('appointment');
        $em = $doctrine->getManager();

        if (count($cookie)) {

            $cookie = json_decode($cookie);
            $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdCookie($cookie->uniqid);

            if (!is_null($request->request->get('mode'))) {
                $cart->setRemote($distance);
            }

            return $cart;
        }

        $newCookie = new Cookie('appointment', json_encode([
            'uniqid' => $uniqid,
            'ip' => $request->getClientIp(),
        ]));
        $response->headers->setCookie($newCookie);

        $cart = (new Cart)
            ->setIdCookie($uniqid)
            ->setRemote($distance);

        $em->persist($cart);

        $em->flush();

        return $cart;

    }

    /**
     * @Route("/prendre-rendez-vous/calendrier", name="rendez_vous_calendrier")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function calendrierAction()
    {
        // Initialisation;
        $response = new Response();
        $request = Request::createFromGlobals();
        $doctrine = $this->get('doctrine');
        $idService = $request->request->get('idService');

        // Récupération du panier;
        $cart = $this->getCart($response);


        // Remise à zéro du rendez-vous si renseigné;
        if ($cart->getAppointment()) {
            $cart->setAppointment(null);
        }

        // Mise au panier du service choisi;
        $service = $doctrine->getRepository('AppBundle:Service')->findOneById($idService);

        $em = $doctrine->getManager();
        $cart->setService($service);

        // Sauvegarde du panier;
        $em->persist($cart);
        $em->flush();


        // Rendu de la vue
        $response->setContent($this->renderView('rendez-vous/calendrier.html.twig'));
        return $response;
    }

    /**
     * @Route("/prendre-rendez-vous/choix-paiement", name="rendez_vous_choix_paiement")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choixPaiementAction()
    {
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();
        $response = new Response();
        $request = Request::createFromGlobals();



        $cart = $this->getCart($response);

        if (!$cart->getService()) {
            throw new AccessDeniedHttpException('Erreur : vous n\'avez pas réalisé l\'étape 2 de la commande');
        }

        if (!is_null($rdvTime = $request->request->get('rdv'))) {
            $rdvDatetime = $rdvTime;

            $apointment = new \DateTime($rdvDatetime);
            $cart->setAppointment($apointment);
            $em->persist($cart);
            $em->flush();
        }


        return $this->render('rendez-vous/choix_paiement.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/prendre-rendez-vous/confirmation", name="rendez_vous_confirmation")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(\Swift_Mailer $mailer)
    {
        $response = new Response();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();
        $request = Request::createFromGlobals();

        $paymentMode = $request->request->get('choix_paiement');

        /** @var Cart $cart */
        $cart = $this->getCart($response);

        if (!$cart->getAppointment()) {
            throw new AccessDeniedHttpException('Erreur : vous n\'avez pas réalisé l\'étape 3 de la commande');
        }

        if ($paymentMode != null && in_array($paymentMode, self::PAYMENT_MODES)) {
            $cart->setPayment($paymentMode);
            $em->persist($cart);
            $em->flush();
        }

        if(!in_array($paymentMode, self::PAYMENT_MODES) && !empty($paymentMode)) {
            throw new HttpException(500);
        }

        $form = $this->createForm(CommandeType::class, null, [], [
            'isCash' => $cart->getPayment() == Cart::MODE_CASH,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('no-reply@steve-david.com')
                ->setTo('hello@steve-david.com')
                ->setBody(
                    $this->renderView(
                        'emails/commande.html.twig',
                        ['commande' => $form->getData(), 'cart' => $cart],
//                    ),
                    'text/html')
                );

            $mailer->send($message);

            return $this->redirectToRoute('rendez_vous_payer');


        }

        return $this->render('rendez-vous/confirmation.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prendre-rendez-vous/payer", name="rendez_vous_payer")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goToPayAction()
    {
        $session = new Session();
        $response = new Response();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();

        /** @var Cart $cart */
        $cart = $this->getCart($response);

        if (!empty($cart)) {

            /** @var Service $service */
            $service = $cart->getService();

            $payer = new Payer();
            $payer->setPaymentmethod('paypal');

            $item = new Item();
            $item->setName($service->getName())
                ->setcurrency('EUR')
                ->setQuantity(1)
                ->setSku($service->getId())
                ->setPrice($service->getPrice());



            $cart->setPaid(true);
            $em->persist($cart);
            $em->flush();



            $response->headers->clearCookie('appointment');

            $response->send();

        }


//        $session->getFlashBag()->add('success', 'Commande validée.');

        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/prendre-rendez-vous/paiement/confirmation", name="rendez_vous_paiement_effectue")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function paymentDoneAction()
    {
        dump('Payment done');
        exit;
    }

    /**
     * @Route("/prendre-rendez-vous/annuler", name="rendez_vous_supprimer_commande")
     */
    public function deleteCartAction()
    {

        $session = new Session();
        $response = new Response();
        $request = Request::createFromGlobals();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();

        $cart = $this->getCart($response);

        if (!empty($cart)) {
            $em->remove($cart);
            $em->flush();
        }

        $response->headers->clearCookie('appointment');

        $session->getFlashBag()->add('success', 'Rendez-vous annulé');

        $response->send();

        return $this->redirectToRoute('accueil');
    }

    /**
     * Not routable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cartAction()
    {
        $doctrine = $this->get('doctrine');

        $request = Request::createFromGlobals();
        $cookie = $request->cookies->get('appointment');

        if (count($cookie)) {

            $cookie = json_decode($cookie);
            $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdCookie($cookie->uniqid);

        } else {
            $cart = null;
        }


        if (!is_null($cart)) {

            if (!is_null($cart->getService())) {
                if (!is_null($cart->getAppointment())) {
                    if (!is_null($cart->getPayment())) {
                        $backlink = $this->generateUrl('rendez_vous_confirmation');
                    } else {
                        $backlink = $this->generateUrl('rendez_vous_choix_paiement');
                    }
                } else {
                    $backlink = $this->generateUrl('rendez_vous_calendrier');
                }
            } else {
                $backlink = $this->generateUrl('rendez_vous_service');
            }
        } else {
            $backlink = null;
        }




        return $this->render('rendez-vous/cart.render.html.twig', [
            'cart' => $cart,
            'back_link' => $backlink,
        ]);
    }

    /**
     * @Route("/ajax/service-details", name="rendez_vous_service_details")
     */
    public function serviceDetailsAction()
    {

        $request = Request::createFromGlobals();
        $response = new Response();

        $idService = $request->get('id');

        $doctrine = $this->get('doctrine');

        /** @var Service $service */
        $service = $doctrine->getRepository('AppBundle:Service')->findOneBy([
            'id' => $idService,
        ]);

        /** @var Cart $cart */
        $cart = $this->getCart($response);

        return $this->render('rendez-vous/service-details.ajax.html.twig', [
            'cart' => $cart,
            'service' => $service,
        ]);
    }
}