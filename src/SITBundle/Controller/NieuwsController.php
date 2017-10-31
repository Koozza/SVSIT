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

class NieuwsController extends Controller
{
    /**
     * @Route("/nieuws/", name="nieuws")
     */
    public function nieuwsAction(Request $request)
    {
        return $this->render('nieuws/nieuws.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'nieuwsberichten' => $this->getNieuws()
        ]));
    }

    /**
     * @Route("/nieuws/{id}/", name="nieuws_artikel")
     */
    public function nieuwsArtikelAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $artikel = $em->getRepository('SITBundle\Entity\Nieuwsbericht')->find($id);
        if($artikel != null) {
            return $this->render('nieuws/nieuwsartikel.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
                'artikel' => $artikel
            ]));
        }else{
            return $this->render('nieuws/artikel_niet_gevonden.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
        }
    }

    private function getNieuws() {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('SITBundle\Entity\Nieuwsbericht', 'u')
            ->orderBy('u.gepubliceerd', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }
}