<?php

namespace SITBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mollie_API_Exception;
use Mollie_API_Object_Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\Betaling;
use SITBundle\Entity\Gebruiker;
use SITBundle\Entity\Gebruiker_MolliePayment;
use SITBundle\Entity\MollieCustomer;
use SITBundle\Entity\MollieMandate;
use SITBundle\Entity\MolliePayment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;
use Symfony\Component\Validator\Constraints\DateTime;

class PaymentController extends Controller
{
    /**
     * @Route("/payment/link/{id}", name="payment_link_accept")
     */
    public function paymentLinkAcceptAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $BetaalLink = $em->getRepository('SITBundle\Entity\BetaalLink')->findOneBy(array('UID' => $id));
        $securityContext = $this->container->get('security.authorization_checker');

        if($BetaalLink != null) {
            //Check of deze link nog geldig is (verloopdatum)
            if($BetaalLink->getVerloopDatum() != null) {
                $verloopdatum = $BetaalLink->getVerloopDatum();
                $verloopdatum->add(new \DateInterval('P1D'));
                if (new \DateTime("NOW") >= $verloopdatum)
                    return new Response("Deze link is verlopen.");
            }

            //Check of deze link niet al is gebruikt (niet multiuser)
            if(!$BetaalLink->getMultiuser()) {
                if ($BetaalLink->isUsed())
                    return new Response("Deze link is al gebruikt.");
            }

            //Check Ingelogged
            if($BetaalLink->getAlleenLeden() || $BetaalLink->getAlleenIngelogged()) {
                if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    return new RedirectResponse($this->generateUrl('user_login'));
                }
            }

            //Check of de gebruiker lid is
            if($BetaalLink->getAlleenLeden()) {
                if (!$this->getUser()->getBetaald()) {
                    return $this->render('_CONTENT_/paylink/geen_lid.html.twig', DefaultController::generateBaseVars($this->getDoctrine()->getManager()));
                }

            }

            $mollie = new \Mollie_API_Client();
            $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
            $issuers = $mollie->issuers->all();
            $issuersArray = array('' => '');

            //Mollie to array
            foreach($issuers as $issuer) {
                if ($issuer->method == Mollie_API_Object_Method::IDEAL)
                    $issuersArray[$issuer->id] = $issuer->name;
            }

            //Create form
            $data = array();
            $form = $this->createFormBuilder($data)
                ->add('issuers', 'choice',
                    array('choices' => $issuersArray, 'required' => true, 'label' => 'Kies je bank'));

            if(!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
                $form->add('volledigeNaam', 'text', array('required' => true, 'attr' => array('placeholder' => 'John Doe')));

            $form = $form->getForm();


            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                return $this->paymentLinkStartAction($request, $form, $id);
            }

            return $this->render('paylink/accept.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
                'form' => $form->createView(),
                'betaalLink' => $BetaalLink,
                'ingelogged' => $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'),
                'UID' => $id,
                'incassoKosten' => $this->container->getParameter('IncassoKosten')
            ]));
        }else{
            return new Response("Deze betaallink is niet geldig");
        }
    }

    private function paymentLinkStartAction(Request $request, $form, $id)
    {
        $gebruiker = null;
        $em = $this->getDoctrine()->getManager();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
        $BetaalLink = $em->getRepository('SITBundle\Entity\BetaalLink')->findOneBy(array('UID' => $id));
        $securityContext = $this->container->get('security.authorization_checker');


        if($BetaalLink != null) {
            //Checken of alle info van het formulier beschikbaar is indien nodig
            if(!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                if(count($this->get('validator')->validateValue($form->get('volledigeNaam')->getData(), new NotBlankConstraint())) != 0)
                    $form->get('volledigeNaam')->addError(new FormError('Naam is een verplicht veld.'));

            }else{
                $gebruiker = $this->getUser();
            }

            if($form->isValid()) {

                if ($gebruiker != null) {
                    //Create MollieCustomer if needed.
                    if ($gebruiker->getMollieCustomer() == null) {
                        try {
                            $customer = $mollie->customers->create(array(
                                "name" => $gebruiker->getFullName(),
                                "email" => $gebruiker->getEmailadres(),
                                "metadata" => array(
                                    "lidmaatschap" => $gebruiker->getLidmaatschap()->getBeschrijving(),
                                ),
                            ));

                            $mollieCustomer = new MollieCustomer();
                            $mollieCustomer->setCustomerid($customer->id);
                            $mollieCustomer->setMode($customer->mode);
                            $mollieCustomer->setName($customer->name);
                            $mollieCustomer->setEmail($customer->email);
                            $mollieCustomer->setMetadata(json_encode($customer->metadata, true));
                            $em->persist($mollieCustomer);

                            $gebruiker->setMollieCustomer($mollieCustomer);
                            $em->persist($gebruiker);

                            $em->flush();
                        } catch (Mollie_API_Exception $e) {
                            return new Response("Er is iets mis gegaan");
                        }
                    }
                }

                //Betaling Starten
                try {
                    $orderID = time() . DefaultController::generateRandomString(10);

                    if ($gebruiker != null)
                        $customer = $mollie->customers->get($gebruiker->getMollieCustomer()->getCustomerid());

                    $paymentSettings = array(
                        "description" => "Betaling: " . $BetaalLink->getNaam(),
                        "redirectUrl" => $this->generateUrl('payment_link_complete', array('id' => $orderID), UrlGeneratorInterface::ABSOLUTE_URL),
                        "webhookUrl" => $this->generateUrl('payment_webhook', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                        "method" => \Mollie_API_Object_Method::IDEAL,
                        "issuer" => $form->get('issuers')->getData()
                    );
                    if ($BetaalLink->getChargePaymentCost())
                        $paymentSettings['amount'] = $BetaalLink->getamount() + $this->container->getParameter('IncassoKosten');
                    else
                        $paymentSettings['amount'] = $BetaalLink->getamount();

                    if ($gebruiker != null) {
                        $payment = $mollie->customers_payments->with($customer)->create($paymentSettings);
                    }else {
                        $paymentSettings['metadata'] = array(
                            'fullname' => $form->get('volledigeNaam')->getData()
                        );
                        $payment = $mollie->payments->create($paymentSettings);
                    }


                    //Onze eigen betaling ook aanmaken
                    $dbPayment = new MolliePayment();
                    $dbPayment->setUID($orderID);
                    $dbPayment->setPaymentid($payment->id);
                    $dbPayment->setMode($payment->mode);
                    $dbPayment->setAmount($payment->amount);
                    $dbPayment->setDescription($payment->description);
                    $dbPayment->setMethod($payment->method);
                    $dbPayment->setStatus($payment->status);
                    $dbPayment->setExpiryPeriod($payment->expiryPeriod);
                    $dbPayment->setCreatedDatetime(new \DateTime($payment->createdDatetime));
                    $dbPayment->setProfileId($payment->profileId);
                    if ($gebruiker != null)
                        $dbPayment->setCustomerId($em->getRepository('SITBundle\Entity\MollieCustomer')->findOneBy(array('customerid' => $payment->customerId)));
                    else
                        $dbPayment->setMetadata(json_encode($paymentSettings['metadata']));
                    $dbPayment->setProfileId($payment->profileId);
                    $em->persist($dbPayment);

                    //Gebruiker Molliepayment aanmaken
                    $gmp = new Gebruiker_MolliePayment();
                    $gmp->setMolliePayment($dbPayment);
                    $gmp->setBetaalLink($BetaalLink);
                    if ($gebruiker != null)
                        $gmp->setGebruiker($this->getUser());
                    $em->persist($gmp);

                    $BetaalLink->addGebruikerMolliePayment($gmp);
                    $em->persist($BetaalLink);

                    $em->flush();

                    return $this->redirect($payment->getPaymentUrl());
                } catch (Mollie_API_Exception $e) {
                    return new Response("Er is iets mis gegaan");
                }

                return new Response("Er is iets mis gegaan");
            }
        }
    }

    /**
     * @Route("/payment/link/complete/{id}", name="payment_link_complete")
     */
    public function paymentLinkCompleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $dbPayment = $em->getRepository('SITBundle\Entity\MolliePayment')->findOneBy(array('UID' => $id));

        $page = null;
        if($dbPayment->getStatus() == 'paid')
            $page = '_CONTENT_/paylink/payment_complete.html.twig';
        elseif($dbPayment->getStatus() == 'cancelled')
            $page = '_CONTENT_/paylink/payment_cancelled.html.twig';
        else
            $page = '_CONTENT_/paylink/payment_open.html.twig';

        return $this->render($page, array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager())));
    }


    /**
     * @Route("/payment/lidmaatschap/accept", name="payment_accept")
     */
    public function paymentAcceptAction(Request $request)
    {
        $gebruiker = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
        $issuers = $mollie->issuers->all();

        if(!$this::magPaymentDoen($gebruiker, $mollie))
            return $this->redirectToRoute('user_dashboard_lidmaatschap');

        //Uitzoeken wat de voglende periode is.
        $volgendePeriodes = $this::getNextPeriod($gebruiker, $em, $gebruiker->getLidmaatschap()->getAantalPeriodes());

        return $this->render('payment/accept.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'lidmaatschap' => $gebruiker->getLidmaatschap(),
            'issuers' => $issuers,
            'volgendePeriodes' => $volgendePeriodes,
            'incassoKosten' => $this->container->getParameter('IncassoKosten')
        ]));
    }

    /**
     * @Route("/payment/lidmaatschap/cancel", name="payment_cancel")
     */
    public function paymentCancelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $gebruiker = $this->get('security.context')->getToken()->getUser();
        $lidtot = new \DateTime("1-1-2014");

        foreach($gebruiker->getBetalingen() as $betaling) {
            if($betaling->getIsLifetime()) {
                $lifetime = true;
                $lidsinds = $gebruiker->getInschrijfdatum();
            }

            if($betaling->getMolliePayment() != null) {
                if($betaling->getMolliePayment()->getStatus() != "paid")
                    continue;
            }

            foreach($betaling->getPeriodes() as $periode) {
                if($periode->getEindDatum() > $lidtot)
                    $lidtot = $periode->getEindDatum();
            }
        }

        if($request->isMethod('POST')) {
            $mollie = new \Mollie_API_Client();
            $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));

            if($gebruiker->getMollieCustomer() != null) {
                foreach($gebruiker->getMollieCustomer()->getMandates() as $mandate) {
                    if($mandate->getStatus() == 'valid') {
                        $mollie->customers_mandates->withParentId($gebruiker->getMollieCustomer()->getCustomerid())->delete($mandate->getMandateid());

                        $mandate->setStatus('invalid');
                        $em->persist($mandate);
                    }
                }
                $em->flush();


                return $this->render('payment/lidmaatschap_geannuleerd.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
                    'lidtot' => $lidtot
                ]));
            }else{
                return new Response("Er is iets mis gegaan");
            }
        }

        return $this->render('payment/cancel.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'lidtot' => $lidtot
        ]));
    }

    /**
     * @Route("/payment/lidmaatschap/start", name="payment_start")
     */
    public function paymentStartAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
        $gebruiker = $this->get('security.context')->getToken()->getUser();

        if(!$this::magPaymentDoen($gebruiker, $mollie))
            return $this->redirectToRoute('user_dashboard_lidmaatschap');


        //Create MollieCustomer if needed.
        if($gebruiker->getMollieCustomer() == null) {
            try {
                $customer = $mollie->customers->create(array(
                    "name" => $gebruiker->getFullName(),
                    "email" => $gebruiker->getEmailadres(),
                    "metadata" => array(
                        "lidmaatschap" => $gebruiker->getLidmaatschap()->getBeschrijving(),
                    ),
                ));

                $mollieCustomer = new MollieCustomer();
                $mollieCustomer->setCustomerid($customer->id);
                $mollieCustomer->setMode($customer->mode);
                $mollieCustomer->setName($customer->name);
                $mollieCustomer->setEmail($customer->email);
                $mollieCustomer->setMetadata(json_encode($customer->metadata, true));
                $em->persist($mollieCustomer);

                $gebruiker->setMollieCustomer($mollieCustomer);
                $em->persist($gebruiker);

                $em->flush();
            } catch (Mollie_API_Exception $e) {
                return new Response("Er is iets mis gegaan");
            }
        }

        //Betaling Starten
        try {
            $orderID = time().DefaultController::generateRandomString(10);

            $customer = $mollie->customers->get($gebruiker->getMollieCustomer()->getCustomerid());
            $payment = $mollie->customers_payments->with($customer)->create(array(
                "amount"        => $gebruiker->getLidmaatschap()->getPrijs() + $this->container->getParameter('IncassoKosten'),
                "description"   => "SIT Lidmaatschap: ".$gebruiker->getLidmaatschap()->getBeschrijving(),
                "redirectUrl"   => $this->generateUrl('payment_complete', array('id'=>$orderID), UrlGeneratorInterface::ABSOLUTE_URL),
                "webhookUrl"    => $this->generateUrl('payment_webhook', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                "recurringType" => \Mollie_API_Object_Payment::RECURRINGTYPE_FIRST,
                "method" => \Mollie_API_Object_Method::IDEAL,
                "issuer" => $request->request->get('ideal_issuer'),
                "metadata" => array(
                    'desc' => "SIT Lidmaatschap: ".$gebruiker->getLidmaatschap()->getBeschrijving()
                )
            ));

            //Onze eigen betaling ook aanmaken
            $betaling = new Betaling();
            $betaling->setDatum(new \DateTime());
            $betaling->setGebruiker($gebruiker);
            $betaling->setIsLifetime(false);
            foreach($this::getNextPeriod($gebruiker, $em, $gebruiker->getLidmaatschap()->getAantalPeriodes()) as $periode)
                $betaling->addPeriode($periode);

            $em->persist($betaling);

            $dbPayment = new MolliePayment();
            $dbPayment->setUID($orderID);
            $dbPayment->setPaymentid($payment->id);
            $dbPayment->setMode($payment->mode);
            $dbPayment->setAmount($payment->amount);
            $dbPayment->setDescription($payment->description);
            $dbPayment->setMethod($payment->method);
            $dbPayment->setStatus($payment->status);
            $dbPayment->setExpiryPeriod($payment->expiryPeriod);
            $dbPayment->setCreatedDatetime(new \DateTime($payment->createdDatetime));
            $dbPayment->setProfileId($payment->profileId);
            $dbPayment->setCustomerId($em->getRepository('SITBundle\Entity\MollieCustomer')->findOneBy(array('customerid' => $payment->customerId)));
            $dbPayment->setRecurringType($payment->recurringType);
            $dbPayment->setBetaling($betaling);
            $dbPayment->setMetadata(json_encode(array(
                'desc' => $payment->description
            )));
            $betaling->setMolliePayment($dbPayment);


            $em->persist($betaling);
            $em->persist($dbPayment);

            $em->flush();

            return $this->redirect($payment->getPaymentUrl());
        } catch (Mollie_API_Exception $e) {
            return new Response("Er is iets mis gegaan");
        }

        return new Response("Er is iets mis gegaan");
    }

    /**
     * @Route("/payment/webhook", name="payment_webhook")
     */
    public function paymentWebhookAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));

        try {
            $dbPayment = $em->getRepository('SITBundle\Entity\MolliePayment')->findOneBy(array('paymentid' => $request->request->get('id')));
            $payment  = $mollie->payments->get($request->request->get('id'));

            $dbPayment->setAmountRefunded($payment->amountRefunded);
            $dbPayment->setAmountRemaining($payment->amountRemaining);
            $dbPayment->setStatus($payment->status);
            $dbPayment->setExpiryPeriod($payment->expiryPeriod);
            if($payment->expiredDatetime != null)
                $dbPayment->setExpiredDatetime(new \DateTime($payment->expiredDatetime));
            if($payment->paidDatetime != null)
                $dbPayment->setPaidDatetime(new \DateTime($payment->paidDatetime));
            if($payment->cancelledDatetime != null)
                $dbPayment->setCancelledDatetime(new \DateTime($payment->cancelledDatetime));
            $dbPayment->setProfileId($payment->profileId);
            $dbPayment->setRecurringType($payment->recurringType);
            $dbPayment->setSubscriptionId($payment->subscriptionId);
            $dbPayment->setLocale($payment->locale);
            $dbPayment->setMetadata(json_encode($payment->metadata));
            if($payment->details != null) {
                $dbPayment->setConsumerName($payment->details->consumerName);
                $dbPayment->setConsumerAccount($payment->details->consumerAccount);
                $dbPayment->setConsumerBic($payment->details->consumerBic);
            }

            if($payment->mandateId != null) {
                //Get or create matching Mandate
                $dbMandate = $em->getRepository('SITBundle\Entity\MollieMandate')->findOneBy(array('mandateid' => $payment->mandateId));
                if($dbMandate == null) {
                    try {
                        $mandate = $mollie->customers_mandates->withParentId($payment->customerId)->get($payment->mandateId);
                        if($mandate != null) {
                            $dbMandate = new MollieMandate();
                            $mandate = $mollie->customers_mandates->withParentId($payment->customerId)->get($payment->mandateId);

                            $dbMandate->setMandateid($mandate->id);
                            $dbMandate->setStatus($mandate->status);
                            $dbMandate->setMethod($mandate->method);
                            $dbMandate->setCustomerId($dbPayment->getCustomerId());
                            $dbMandate->setCreatedDatetime(new \DateTime($mandate->createdDatetime));
                            if ($payment->details != null) {
                                $dbMandate->setConsumerName($payment->details->consumerName);
                                $dbMandate->setConsumerAccount($payment->details->consumerAccount);
                                $dbMandate->setConsumerBic($payment->details->consumerBic);
                            }
                        }
                    } catch (Mollie_API_Exception $e) {
                        return new Response("Er is iets mis gegaan");
                    }
                }

                if($dbMandate != null) {
                    $dbPayment->setMandateId($dbMandate);
                    $em->persist($dbMandate);
                }
            }
            $em->persist($dbPayment);

            $em->flush();
        } catch (Mollie_API_Exception $e) {
            return new Response("Er is iets mis gegaan");
        }

        return new Response("");
    }

    /**
     * @Route("/payment/lidmaatschap/complete/{id}", name="payment_complete")
     */
    public function paymentCompleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $dbPayment = $em->getRepository('SITBundle\Entity\MolliePayment')->findOneBy(array('UID' => $id));

        $page = null;
        if($dbPayment->getStatus() == 'paid')
            $page = '_CONTENT_/payment/payment_complete.html.twig';
        elseif($dbPayment->getStatus() == 'cancelled')
            $page = '_CONTENT_/payment/payment_cancelled.html.twig';
        else
            $page = '_CONTENT_/payment/payment_open.html.twig';

        return $this->render($page, array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager())));
    }

    public static function getNextPeriod(Gebruiker $gebruiker, EntityManager $em, $aantalPeriodes) {
        $volgendePeriodes = array();

        $verloopdatum = null;
        foreach($gebruiker->getBetalingen() as $betaling) {
            $heeftBetaald = false;
            if($betaling->getMolliePayment() == null)
                $heeftBetaald = true;
            else
                if($betaling->getMolliePayment()->getStatus() == "paid")
                    $heeftBetaald = true;

            if($heeftBetaald) {
                foreach ($betaling->getPeriodes() as $periode) {
                    if($verloopdatum == null)
                        $verloopdatum = $periode;
                    else
                        if ($periode->getEinddatum() > $verloopdatum->getEinddatum())
                            $verloopdatum = $periode;
                }
            }
        }

        //Er is uberhaupt nog nooit een betaling gedaan; zoek dan de huidige periode
        $dontGetNextPeriod = true;
        if($verloopdatum == null) {
            $now = new \DateTime();
            foreach($em->getRepository('SITBundle\Entity\Periode')->findAll() as $periode) {
                if($now >= $periode->getBegindatum() && $now <= $periode->getEinddatum()) {
                    $verloopdatum = $periode;
                    $dontGetNextPeriod = false;
                }
            }
        }

        for($i = 0;$i < $aantalPeriodes; $i++) {
            if(count($volgendePeriodes) == 0)
                if($dontGetNextPeriod)
                    $volgendePeriodes[] = $verloopdatum->getVolgendePeriode();
                else
                    $volgendePeriodes[] = $verloopdatum;
            else
                $volgendePeriodes[] = $volgendePeriodes[count($volgendePeriodes) - 1]->getVolgendePeriode();
        }

        return $volgendePeriodes;
    }

    public static function magPaymentDoen(Gebruiker $user, \Mollie_API_Client $mollie) {
        foreach($user->getBetalingen() as $betaling) {
            if($betaling->getIsLifetime())
                return false;
        }

        if($user->getLidmaatschap() != null) {
            if ($user->getLidmaatschap()->getIncasseerbaar()) {  //Checken of het incaseerbaar is
                if ($user->getMollieCustomer() == null) {  //Checken of er geen customer is
                    return true;
                } else {
                    $mandates = $user->getMollieCustomer()->getMandates();
                    foreach ($mandates as $mandate) {
                        if ($mandate->getStatus() == "valid") {
                            $realmandate = $mollie->customers_mandates->withParentId($user->getMollieCustomer()->getCustomerid())->get($mandate->getMandateid());
                            if ($realmandate->isValid()) {
                                return false;
                            }
                        }
                    }

                    //Geen mandaat; eerst kijken of er misschien nog een betaling open staat. Dan mag nml ook geen nieuwe betaling gedaan worden
                    foreach($user->getMollieCustomer()->getPayments() as $payment) {
                        if($payment->getStatus() == "open" || $payment->getStatus() == "pending") {
                            return false;
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }
}