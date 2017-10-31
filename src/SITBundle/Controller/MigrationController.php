<?php

namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MigrationController extends BaseController
{
    /**
     * @Route("/migrate/generatePassword/{id}/{password}")
     */
    public function setPasswordAction(Request $request, $id, $password)
    {
        $em = $this->getDoctrine()->getManager();
        $gebruiker= $em->getRepository('SITBundle\Entity\Gebruiker')->find($id);
        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($gebruiker);
        $encoded_pass = $encoder->encodePassword($password, $salt);

        return new Response($encoded_pass.'<br>'.$salt);
    }

    /**
     * @Route("/migrate/betalingdatum")
     */
    public function userDashboardLidmaatschapAction(Request $request)
    {
        if($request->query->get('c') != "zGarTPw9Le3oue")
            die();

        $em = $this->getDoctrine()->getEntityManager();

        $users = $em->getRepository('SITBundle\Entity\Gebruiker')->findAll();
        foreach($users as $user) {
            foreach($user->getBetalingen() as $betaling) {
                $betaling->setDatum($user->getInschrijfdatum());

                $em->persist($betaling);
            }
        }

        $em->flush();

        return new Response('Done!');
    }

    /**
     * @Route("/migrate/sendPasswords")
     */
    public function migrateSendPasswords(Request $request)
    {
        if($request->query->get('c') != "zGarTPw9Le3oue")
            die();

        $em = $this->getDoctrine()->getManager();
        $gebruikers= $em->getRepository('SITBundle\Entity\Gebruiker')->findAll();

        foreach($gebruikers as $gebruiker) {
                $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
                $plainPassword = DefaultController::generateRandomString(10);
                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($gebruiker);
                $encoded_pass = $encoder->encodePassword($plainPassword, $salt);

                $gebruiker->setSalt($salt);
                $gebruiker->setPassword($encoded_pass);
                $em->persist($gebruiker);
                $em->flush();


                $message = \Swift_Message::newInstance()
                    ->setSubject('Nieuwe SIT website')
                    ->setFrom('no-reply@svsit.nl')
                    ->setTo($gebruiker->getEmailadres())
                    ->setBody(
                        $this->renderView(
                            '_CONTENT_/mail/new_website.html.twig',
                            array('email' => $gebruiker->getEmailadres(), 'password' => $plainPassword)
                        ),
                        'text/html'
                    )
                ;
                $this->get('mailer')->send($message);
            }

        return new Response('Done');
    }
}