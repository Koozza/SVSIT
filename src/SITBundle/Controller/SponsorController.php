<?php
namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SponsorController extends Controller
{
    /**
     * @Route("/partners/", name="sponsors")
     */
    public function nieuwsAction(Request $request)
    {
        return $this->render('sponsor/sponsors.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
    }

    /**
     * @Route("/partners/{id}/", name="sponsor_pagina")
     */
    public function sponsorPaginaAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('sponsor/sponsorpagina.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'sponsor' => $em->getRepository('SITBundle\Entity\Sponsor')->find($id)
        ]));
    }

    public static function getSponsors($em) {
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('SITBundle\Entity\Sponsor', 'u')
            ->orderBy('u.naam', 'ASC');

        return $qb->getQuery()->getArrayResult();

    }
}