<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Service;
use AppBundle\Form\CommandeType;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RendezVousController extends Controller
{
    const PAYMENT_MODES = [Cart::MODE_CASH, Cart::MODE_BANK_CARD, Cart::MODE_PAYPAL];

    /**
     * @Route("/prendre-rendez-vous/service/{mode}", name="rendez_vous_service", defaults={"mode": "face-a-face"}, requirements={"mode": "a-distance|face-a-face"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serviceAction($mode)
    {
        $session = new Session();
        $doctrine = $this->get('doctrine');
        $request = Request::createFromGlobals();
        $categories = $doctrine->getRepository('AppBundle:Category')->findAll();

        /** @var Cart $cart */
        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

        $em = $doctrine->getEntityManager();

        $distance =  $mode == 'a-distance';

        if (empty($cart)) {
            $cart = (new Cart)
                ->setIdSession($session->getId())
                ->setRemoteMode($distance);

            $em->persist($cart);

            $em->flush();

        } else {

            if (!empty($cart->getService()) && !$cart->getService()->getRemoteMode() && $distance) {
                throw new ConflictHttpException('Le service sélectionné « ' . $cart->getService()->getName() . ' » n\'est pas compatible avec la consultation à distance.');
            }

            $cart
                ->setRemoteMode($distance)
                ->setService(null)
                ->setAppointment(null)
                ->setPayment(null)
            ;

            $em->persist($cart);
            $em->flush();
        }



        return $this->render('rendez-vous/service.html.twig', [
            'categories' => $categories,
            'service_a_distance' => $distance,
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/prendre-rendez-vous/calendrier", name="rendez_vous_calendrier")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function calendrierAction()
    {
        $session = new Session();
        $request = Request::createFromGlobals();
        $doctrine = $this->get('doctrine');
        $idService = $request->request->get('idService');
        $remoteMode = $request->request->get('remoteMode');

        if (!empty($idService)) {
            $service = $doctrine->getRepository('AppBundle:Service')->findOneById($idService);
            $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

            $em = $doctrine->getEntityManager();

            if (empty($cart)) {

                $cart = (new Cart)
                    ->setRemoteMode($remoteMode)
                    ->setIdSession($session->getId())
                    ->setService($service);

            } else {
                $cart->setService($service);
            }

            $em->persist($cart);

            $em->flush();
        }


        return $this->render('rendez-vous/calendrier.html.twig');
    }

    /**
     * @Route("/prendre-rendez-vous/choix-paiement", name="rendez_vous_choix_paiement")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choixPaiementAction()
    {
        $session = new Session();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();
        $request = Request::createFromGlobals();

        $rdvDatetime = $request->request->get('rdv');
        /** @var Cart $cart */
        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

        if (empty($cart)) {
            throw new AccessDeniedHttpException('Erreur : vous n\'avez pas de commande enregistrée.');
        }

        $cart->setAppointment(new \DateTime($rdvDatetime));

        $em->persist($cart);

        $em->flush();

        return $this->render('rendez-vous/choix_paiement.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/prendre-rendez-vous/confirmation", name="rendez_vous_confirmation")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction()
    {
        $session = new Session();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();
        $request = Request::createFromGlobals();

        $paymentMode = $request->request->get('choix_paiement');

        dump($paymentMode);

        /** @var Cart $cart */
        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

        if (empty($cart)) {
            throw new AccessDeniedHttpException('Erreur : vous n\'avez pas de commande enregistrée.');
        }

        if ($paymentMode != null && in_array($paymentMode, self::PAYMENT_MODES)) {
            $cart->setPayment($paymentMode);
            $em->persist($cart);
            $em->flush();
        }

        if(!in_array($paymentMode, self::PAYMENT_MODES) && !empty($paymentMode)) {
            throw new HttpException(500);
        }

        $form = $this->createForm(CommandeType::class, null, [
            'attr' => [
                'action' => $this->generateUrl('rendez_vous_payer'),
            ],
        ]);

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
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();

        /** @var Cart $cart */
        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

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
        }


        $session->getFlashBag()->add('success', 'Commande validée.');

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
        $request = Request::createFromGlobals();
        $doctrine = $this->get('doctrine');
        $em = $doctrine->getEntityManager();

        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

        if (!empty($cart)) {
            $em->remove($cart);
            $em->flush();
        }

        $session->getFlashBag()->add('success', 'Rendez-vous annulé');

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
        $session = new Session();

        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

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

        if ($cart->getPaid()) {
            $cart = null;
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
        $session = new Session();

        $idService = $request->get('id');

        $doctrine = $this->get('doctrine');

        /** @var Service $service */
        $service = $doctrine->getRepository('AppBundle:Service')->findOneBy([
            'id' => $idService,
        ]);

        /** @var Cart $cart */
        $cart = $doctrine->getRepository('AppBundle:Cart')->findOneByIdSession($session->getId());

        return $this->render('rendez-vous/service-details.ajax.html.twig', [
            'cart' => $cart,
            'service' => $service,
        ]);
    }
}