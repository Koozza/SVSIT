<?php

namespace SITBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\Gebruiker;
use SITBundle\Entity\MollieMandate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserController extends Controller
{
    /**
     * @Route("/user/dashboard", name="user_dashboard")
     */
    public function userDashboardAction(Request $request)
    {
        $gebruiker = $this->get('security.context')->getToken()->getUser();

        $form = $this->createFormBuilder($gebruiker)
            ->add('voornaam', TextType::class, array('attr' => array('placeholder' => 'John')))
            ->add('tussenvoegsel', TextType::class, array('required' => false))
            ->add('achternaam', TextType::class, array('attr' => array('placeholder' => 'Doe')))
            ->add('geslacht', ChoiceType::class, array('choices' => array('Man' => 'Man', 'Vrouw' => 'Vrouw')))
            ->add('geboortedatum', BirthdayType::class, array('years' => range(1980, date('Y')), 'format' => 'd MMMM yyyy'))
            ->add('adres', TextType::class, array('attr' => array('placeholder' => 'Lange Laan 15')))
            ->add('postcode', TextType::class, array('attr' => array('placeholder' => '1234 AB')))
            ->add('woonplaats', TextType::class, array('attr' => array('placeholder' => 'Amsterdam')))
            ->add('telefoonnummer', TextType::class, array('attr' => array('placeholder' => '0612345678')))
            ->add('emailadres', EmailType::class, array('mapped' => false, 'data' => $gebruiker->getEmailadres(), 'attr' => array('placeholder' => 'john@doe.nl')))
            ->add('wijzigen', SubmitType::class, array(
                'attr' => array('value' => 'wijzigen'),
            ))
            ->add('password', PasswordType::class, array('mapped' => false, 'required' => false))
            ->add('herhaalPassword', PasswordType::class, array('mapped' => false, 'required' => false))
            ->getForm();


        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($gebruiker->getEmailadres() != $form->get('emailadres')->getData() && trim($form->get('emailadres')->getData()) != "") {
                if ($em->getRepository('SITBundle\Entity\Gebruiker')->findOneBy(array('emailadres' => $form->get('emailadres')->getData())) != null)
                    $form->get('emailadres')->addError(new FormError('Dit e-mailadres is al bij ons bekend.'));
            }

            if(trim($form->get('password')->getData()) != "") {
                //Check of wachtwoorden matchen
                if ($form->get('password')->getData() != $form->get('herhaalPassword')->getData())
                    $form->get('herhaalPassword')->addError(new FormError('Wachtwoorden komen niet overeen.'));

                if (strlen($form->get('password')->getData()) < 8)
                    $form->get('password')->addError(new FormError('Wachtwoord moet 8 tekens of langer zijn.'));
            }

            if($form->isValid()) {
                if(trim($form->get('password')->getData()) != "") {
                    $encoder_service = $this->get('security.encoder_factory');
                    $encoder = $encoder_service->getEncoder($gebruiker);
                    $encoded_pass = $encoder->encodePassword($form->get('password')->getData(), $gebruiker->getSalt());

                    $gebruiker->setPassword($encoded_pass);
                }

                if($gebruiker->getEmailadres() != $form->get('emailadres')->getData() && trim($form->get('emailadres')->getData()) != "") {
                    $gebruiker->setNewEmailadres($form->get('emailadres')->getData());


                    $message = \Swift_Message::newInstance()
                        ->setSubject('Wijzigen SIT E-mail')
                        ->setFrom('no-reply@svsit.nl')
                        ->setTo($gebruiker->getNewEmailadres())
                        ->setBody(
                            $this->renderView(
                                '_CONTENT_/mail/change_email_email.html.twig',
                                array('gebruiker' => $gebruiker)
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                }

                $em->persist($gebruiker);
                $em->flush();

                if(trim($form->get('password')->getData()) != "")
                    return $this->redirectToRoute('user_logout');
            }
        }

        return $this->render('user/dashboard_gegevens.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'form' => $form->createView(),
        ]));
    }

    /**
     * @Route("/user/dashboard/betalingen", name="user_dashboard_betalingen")
     */
    public function userDashboardBetalingenAction(Request $request)
    {
        $gebruiker = $this->get('security.context')->getToken()->getUser();

        return $this->render('user/dashboard_betalingen.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'gebruiker' => $gebruiker,
        ]));
    }

    /**
     * @Route("/user/dashboard/change_email", name="user_change_email")
     */
    public function userDashboardChangeEmailAction(Request $request)
    {
        $gebruiker = $this->get('security.context')->getToken()->getUser();

        if($gebruiker->getNewEmailadres() == "") {
            return $this->redirectToRoute('user_dashboard');
        }else{
            $em = $this->getDoctrine()->getManager();

            $gebruiker->setEmailadres($gebruiker->getNewEmailadres());
            $gebruiker->setNewEmailadres(null);

            $em->persist($gebruiker);
            $em->flush();

            return $this->redirectToRoute('user_logout');
        }
    }

    /**
     * @Route("/user/dashboard/lidmaatschap", name="user_dashboard_lidmaatschap")
     */
    public function userDashboardLidmaatschapAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $gebruiker= $this->get('security.context')->getToken()->getUser();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));

        $hasPayments = true;
        $lifetime = false;
        $lidsinds = new \Datetime("1-1-2050");
        $lidtot = new \DateTime("1-1-2014");

        foreach($gebruiker->getBetalingen() as $betaling) {
            if($betaling->getIsLifetime()) {
                $lifetime = true;
                $lidsinds = $gebruiker->getInschrijfdatum();
            }

            if($betaling->getMolliePayment() != null) {
                if($betaling->getMolliePayment()->getStatus() != "paid" && !($betaling->getMolliePayment()->getStatus() == "pending" && $betaling->getMolliePayment()->getMethod() == "directdebit" ))
                    continue;
            }

            foreach($betaling->getPeriodes() as $periode) {
                if($periode->getBeginDatum() <= $lidsinds)
                    $lidsinds = $periode->getBeginDatum();

                if($periode->getEindDatum() > $lidtot)
                    $lidtot = $periode->getEindDatum();
            }
        }

        if(count($gebruiker->getBetalingen()) == 0)
            $hasPayments = false;

        //Check mandaat status
        $bestMandate = null;
        if($gebruiker->getMollieCustomer() != null) {
            foreach($gebruiker->getMollieCustomer()->getMandates() as $mandate) {
                if($mandate->getStatus() != 'invalid') {
                    try {
                        $liveMandate = $mollie->customers_mandates->withParentId($gebruiker->getMollieCustomer()->getCustomerid())->get($mandate->getMandateid());

                        $mandate->setStatus($liveMandate->status);
                        if ($liveMandate->details != null) {
                            $mandate->setConsumerName($liveMandate->details->consumerName);
                            $mandate->setConsumerAccount($liveMandate->details->consumerAccount);
                            $mandate->setConsumerBic($liveMandate->details->consumerBic);
                        }

                        if ($liveMandate->status == "valid") {
                            $bestMandate = $liveMandate;
                        }

                        if ($liveMandate->status == "pending") {
                            if ($bestMandate != null) {
                                if ($bestMandate->status != "valid")
                                    $bestMandate = $liveMandate;
                            } else {
                                $bestMandate = $liveMandate;
                            }
                        }

                        if ($liveMandate->status == "invalid") {
                            if ($bestMandate != null) {
                                if ($bestMandate->status != "valid" && $bestMandate->status != "invalid")
                                    $bestMandate = $liveMandate;
                            } else {
                                $bestMandate = $liveMandate;
                            }
                        }
                    }catch (\Mollie_API_Exception $ex) {
                        $mandate->setStatus('invalid');
                    }

                    $em->persist($mandate);
                    $em->flush();
                }
            }
        }

        return $this->render('user/dashboard_lidmaatschap.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'gebruiker' => $gebruiker,
            'lidsinds' => $lidsinds,
            'lidtot' => $lidtot,
            'lifetime' => $lifetime,
            'hasPayments' => $hasPayments,
            'mandate' => $bestMandate,
            'magBetalen' => PaymentController::magPaymentDoen($gebruiker, $mollie)
        ]));
    }

    /**
     * @Route("/user/login", name="user_login")
     */
    public function userLoginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()),array(
            'last_username' => $lastUsername,
            'error'         => $error,
        )));
    }

    /**
     * @Route("/user/logout", name="user_logout")
     */
    public function userLogoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();


        return $this->redirectToRoute('frontpage');
    }
}