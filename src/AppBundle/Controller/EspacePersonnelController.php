<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EspacePersonnelController extends Controller
{
    /**
     * @Route("/votre-espace-personnel", name="espace_personnel_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $doctrine = $this->get('doctrine');

        $appointments = $doctrine->getRepository('AppBundle:Cart')->findBy([
            'user' => $this->getUser()->getId(),
            'paid' => true,
        ]);


        $daysNumber = $this->getUser()->getSubscriptionDate()->diff(new \DateTime())->format('%a');

        $futureAppointments = [];
        foreach ($appointments as $appointment) {
            if ($appointment->getAppointment() && $appointment->getAppointment()->diff(new \DateTime())->format('%r%a') < 0) {
                $futureAppointments[] = $appointment;
            }
        }

        $viewParams = [
            'nb_consultations' => count($appointments),
            'days_number' => $daysNumber,
        ];

        if (!empty($futureAppointments)) {
            $viewParams['next_appointment'] = array_shift($futureAppointments);
            $viewParams['future_appointments'] = $futureAppointments;
        }


        return $this->render('espace-personnel/index.html.twig', $viewParams);
    }

    /**
     * @Route("/votre-espace-personnel/votre-profil", name="espace_personnel_profil")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profilAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('espace-personnel/profil.html.twig', [
        ]);
    }

    /**
     * @Route("/votre-espace-personnel/vos-rendez-vous", name="espace_personnel_rendez-vous")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rendezVousAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $doctrine = $this->get('doctrine');

        $appointments = $doctrine->getRepository('AppBundle:Cart')->findBy([
            'user' => $this->getUser()->getId(),
            'paid' => true,

        ], [
            'appointment' => 'desc',
        ]);

        $sortedAppointments = [
            'last_week' => [],
            'last_month' => [],
            'last_year' => [],
            'this_week' => [],
            'this_month' => [],
            'this_year' => [],
        ];

        foreach($appointments as $appointment) {
            $timestamp = $appointment->getAppointment()->getTimestamp();
            $timestamps = [
                'last_week'  => [strtotime('-2 week'), strtotime('-1 week')],
                'last_month' => [strtotime('-2 month +1 day'), strtotime('-1 month')],
                'last_year'  => [strtotime('-2 year'), strtotime('-1 year')],

                'this_week'  => [strtotime('-1 week'), strtotime('today')],
                'this_month' => [strtotime('-1 month'), strtotime('today')],
                'this_year'  => [strtotime('-1 year'), strtotime('today')],
            ];
            if ($timestamp > $timestamps['last_week'][0] && $timestamp < $timestamps['last_week'][1]) {
                $sortedAppointments['last_week'][] = $appointment;
            } elseif ($timestamp > $timestamps['last_month'][0] && $timestamp < $timestamps['last_month'][1]) {
                $sortedAppointments['last_month'][] = $appointment;
            } elseif ($timestamp > $timestamps['last_year'][0] && $timestamp < $timestamps['last_year'][1]) {
                $sortedAppointments['last_year'][] = $appointment;

            } elseif ($timestamp < $timestamps['this_week'][1] && $timestamp > $timestamps['this_week'][0]) {
                $sortedAppointments['this_week'][] = $appointment;
            } elseif ($timestamp < $timestamps['this_month'][1] && $timestamp > $timestamps['this_month'][0]) {
                $sortedAppointments['this_month'][] = $appointment;
            } elseif ($timestamp < $timestamps['this_year'][1] && $timestamp > $timestamps['this_year'][0]) {
                $sortedAppointments['this_year'][] = $appointment;
            }
        }

        return $this->render('espace-personnel/rendez_vous.html.twig', [
            'appointments' => $appointments,
            'sorted_appointments' => $sortedAppointments,
            'timestamps' => $timestamps,
        ]);
    }
}