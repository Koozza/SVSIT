<?php

namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\Gebruiker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class EvenementenController extends Controller
{
    /**
     * @Route("/evenementen/", name="evenementen")
     */
    public function evenementenAction(Request $request)
    {
        return $this->render('evenementen/evenementen.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'evenementen_beer' => $this->getEvenementen("beer"),
            'evenementen_book' => $this->getEvenementen("book"),
            'evenementen_controller' => $this->getEvenementen("controller")
        ]));
    }

    /**
     * @Route("/evenementen/{id}/ical", name="evenementen_ical")
     */
    public function evenementDownloadIcalAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository('SITBundle\Entity\Evenement')->find($id);

        $ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//SVSIT/Thijs//NONSGML v1.0//EN
BEGIN:VEVENT
DTSTAMP:'.$this->dateToCal(time()).'
ORGANIZER;CN=SVSIT:MAILTO:bestuur@svsit.nl
DTSTART:'.date_format($evenement->getDatum(), 'Ymd\THi00\Z').'
DTEND:'.date_format($evenement->getEinddatum(), 'Ymd\THi00\Z').'
SUMMARY:'.$evenement->getNaam().'
DESCRIPTION:'.json_encode(strip_tags($evenement->getOmschrijving())).'
LOCATION:'.$evenement->getLocatieAdres().'
END:VEVENT
END:VCALENDAR';
        $response = new Response($ical);

        $d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'afspraak.ics');
        $response->headers->set('Content-Disposition', $d);

        return $response;
    }

    private function dateToCal($timestamp) {
        return date('Ymd\Tgis\Z', $timestamp);
    }

    /**
     * @Route("/evenementen/{id}", name="evenementen_show")
     */
    public function evenementenShowAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository('SITBundle\Entity\Evenement')->find($id);

        if($evenement != null) {
            return $this->render('evenementen/evenement.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
                'evenement' => $evenement
            ]));
        }else{
            return new Response("Er is iets fout gegaan");
        }
    }

    private function getEvenementen($type) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $qb->select('u, lp')
            ->from('SITBundle\Entity\Evenement', 'u')
            ->leftJoin('u.locatiePositie', 'lp')
            ->where('u.datum > :now')
            ->andWhere('u.type = :beer')
            ->orderBy('u.datum', 'ASC')
            ->setParameters(array('now' => new \DateTime("-6 hours"), 'beer' => $type));

        return $qb->getQuery()->getArrayResult();
    }
}