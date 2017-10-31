<?php

namespace SITBundle\Controller;

use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\NieuwsbriefDeelnemer;
use SITBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="frontpage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $nieuwsbriefDeelnemer = new NieuwsbriefDeelnemer();
        $nieuwsbriefSucces = false;

        $nieuwsbriefForm = $this->createFormBuilder($nieuwsbriefDeelnemer)
            ->setAction("#mail")
            ->add('email', TextType::class, array('attr' => array('placeholder'=>'VOER JE E-MAILADRES IN')))
            ->add('aanmelden', SubmitType::class)
            ->getForm();

        $nieuwsbriefForm->handleRequest($request);
        if ($nieuwsbriefForm->isSubmitted() && $nieuwsbriefForm->isValid()) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            if($em->getRepository('SITBundle\Entity\NieuwsbriefDeelnemer')->findOneBy(array('email' => $nieuwsbriefForm->get('email')->getData())) != null)
                $nieuwsbriefForm->get('email')->addError(new FormError('Dit e-mailadres is al bij ons bekend.'));

            if(count($em->getRepository('SITBundle\Entity\NieuwsbriefDeelnemer')->findBy(array('ip' => $ip))) >= 2)
                $nieuwsbriefForm->get('email')->addError(new FormError('Er zijn teveel aanvragen gedaan vanaf dit IP adres.'));

            if($nieuwsbriefForm->isValid()) {
                $session = $request->getSession();
                $session->start();
                if($session->get('name') == null) {
                    $nieuwsbriefSucces = true;

                    $nieuwsbriefDeelnemer->setUID($this->generateRandomString(25));
                    $nieuwsbriefDeelnemer->setIp($ip);


                    $em->persist($nieuwsbriefDeelnemer);
                    $em->flush();

                    $session->set('nieuwsbrief', true);

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Inschrijving SIT Nieuwsbrief')
                        ->setFrom('no-reply@svsit.nl')
                        ->setTo($nieuwsbriefDeelnemer->getEmail())
                        ->setBody(
                            $this->renderView(
                                '_CONTENT_/mail/activate_nieuwsbrief.html.twig',
                                array('nieuwsbriefDeelnemer' => $nieuwsbriefDeelnemer)
                            ),
                            'text/html'
                        );
                    $this->get('mailer')->send($message);
                }
            }
        }

        return $this->render('default/index.html.twig', array_merge($this->generateBaseVars($this->getDoctrine()->getManager()), [
            'nieuwsbriefForm' => $nieuwsbriefForm->createView(),
            'nieuwsbriefSucces' => $nieuwsbriefSucces,
            'sponsoren' => $em->getRepository("SITBundle\Entity\Sponsor")->findAll(),
            'bestuur' => $em->getRepository("SITBundle\Entity\Bestuur")->findOneBy(array('isHuidigeBestuur' => true))
        ]));
    }

    /**
     * @Route("/bestuur/{id}/", name="bestuur_id")
     */
    public function bestuurIdAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('default/bestuur.html.twig', array_merge($this->generateBaseVars($this->getDoctrine()->getManager()), [
            'bestuur' => $em->getRepository("SITBundle\Entity\Bestuur")->find($id)
        ]));
    }

    /**
     * @Route("/bestuur/", name="bestuur")
     */
    public function bestuurAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('default/bestuur.html.twig', array_merge($this->generateBaseVars($this->getDoctrine()->getManager()), [
            'bestuur' => $em->getRepository("SITBundle\Entity\Bestuur")->findOneBy(array('isHuidigeBestuur' => true))
        ]));
    }

    /**
     * @Route("/cookies/accept/", name="accept_cookies")
     */
    public function acceptCookiesAction(Request $request)
    {
        $obj = (object) array('status' => 'success');

        $response = new Response(json_encode($obj));;
        $response->headers->setCookie(new Cookie('cookies', 'accepted',time() + (3600 * 24 * 365 * 10), '/', null, false, false));
        return $response;
    }

    public static function generateBaseVars($em) {
        $qb = $em->createQueryBuilder();
        $qb->select('u, lp')
            ->from('SITBundle\Entity\Evenement', 'u')
            ->leftJoin('u.locatiePositie', 'lp')
            ->where('u.datum > :now')
            ->orderBy('u.datum', 'ASC')
            ->setMaxResults(5)
            ->setParameter('now', new \DateTime("-6 hours"));

        $qb2 = $em->createQueryBuilder();
        $qb2->select('u')
            ->from('SITBundle\Entity\Nieuwsbericht', 'u')
            ->orderBy('u.gepubliceerd', 'DESC')
            ->setMaxResults(5);

        return [
            'evenementen' => $qb->getQuery()->getArrayResult(),
            'nieuws' => $qb2->getQuery()->getArrayResult(),
            'sponsoren' => SponsorController::getSponsors($em),
            'besturen' => Self::getBesturen($em)
        ];
    }

    public static function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getBesturen($em) {
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('SITBundle\Entity\Bestuur', 'u')
            ->orderBy('u.naam', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }
}
