<?php

namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NieuwsbriefController extends Controller
{
    /**
     * @Route("/mail/", name="mail")
     */
    public function indexAction(Request $request)
    {
        return $this->render('mail/nieuwsbrief.html.twig');
    }
    /**
     * @Route("/mail/sent/", name="nieuwsbriefsent")
     */
    public function sentAction(Request $request)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('SIT Nieuwsbrief')
            ->setFrom('no-reply@svsit.nl')
            ->setTo('thijsdalmaijer@gmail.com')
            ->setBody(
                $this->renderView(
                    'mail/nieuwsbrief.html.twig'
                ),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);

        return $this->render('mail/nieuwsbrief.html.twig');
    }

    /**
     * @Route("/mail/inschrijven/{uid}", name="nieuwsbrief_inschrijven")
     */
    public function inschrijvenAction(Request $request, $uid)
    {
        $em = $this->getDoctrine()->getManager();
        $deelnemer = $em->getRepository('SITBundle\Entity\NieuwsbriefDeelnemer')->findOneBy(array('UID' => $uid));
        $deelnemer->setActive(true);

        $em->persist($deelnemer);
        $em->flush();

        return $this->render('_CONTENT_/nieuwsbrief/ingeschreven.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
    }

    /**
     * @Route("/mail/uitschrijven/{uid}", name="nieuwsbrief_uitschrijven")
     */
    public function uitschrijvenAction(Request $request, $uid)
    {

        return $this->render('mail/nieuwsbrief.html.twig');
    }
}
