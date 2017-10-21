<?php

namespace AppBundle\Controller;

use AppBundle\Form\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{

    const RECAPTCHA_SECRET_KEY = '6Lcp7TEUAAAAAAoiYX3NpTB5R__vdp3z58ky70AX';
    /**
     * @Route("/", name="accueil")
     */
    public function indexAction()
    {
        $doctrine = $this->get('doctrine');

        $categories = $doctrine->getRepository('AppBundle:Category')->findBy([], null, 3);

        return $this->render('index/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/qui-suis-je", name="qui-suis-je")
     */
    public function quiSuisJeAction()
    {
        return $this->render('index/qui-suis-je.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction()
    {
        $captchaError = null;
        $captchaReturn['success'] = false;
        $formSuccess = false;

        $form = $this->createForm(ContactType::class, null, [
            'attr' => [
                'class' => 'nobottommargin',
            ],
        ]);

        $request = Request::createFromGlobals();
        $captchaValue = $request->request->get('g-recaptcha-response');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $curl = curl_init('https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
                    'secret' => self::RECAPTCHA_SECRET_KEY,
                    'response' => $captchaValue,
                ]));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $captchaCurlResponse = curl_exec($curl);

            if (!empty($captchaCurlResponse)) {
                $captchaReturn = json_decode($captchaCurlResponse, true);
            }
        }

        if ($form->isSubmitted() && empty($captchaValue)) {
            $captchaError = 'Veuillez renseigner le vérificateur de robot.';
        }

        if ($form->isSubmitted() && $form->isValid() && $captchaReturn['success']) {

            $data = $form->getData();
            $formSuccess = true;
        }

        return $this->render('index/contact.html.twig', [
            'form' => $form->createView(),
            'captcha_error' => $captchaError,
            'form_success' => $formSuccess,
        ]);
    }

    function footerAction()
    {
        $doctrine = $this->get('doctrine');

        $themes = $doctrine->getRepository('AppBundle:Theme')->findAll();
        $posts = $doctrine->getRepository('AppBundle:Post')->findBy([], ['date' => 'DESC'], 3);

        $nbServices = $doctrine->getRepository('AppBundle:Service')->countAll();
        $nbBlogPosts = $doctrine->getRepository('AppBundle:Post')->countAll();

        return $this->render('index/footer.html.twig', [
            'themes' => $themes,
            'posts' => $posts,
            'nb_services' => $nbServices,
            'nb_blog_posts' => $nbBlogPosts,
        ]);
    }
}
