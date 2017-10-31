<?php

namespace SITBundle\Controller;

use DateTime;
use Dompdf\Dompdf;
use Mollie_API_Client;
use Mollie_API_Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SITBundle\Entity\Factuur;
use SITBundle\Entity\FactuurProduct;
use SITBundle\Entity\FactuurProductKoppeling;
use SITBundle\Entity\Gebruiker;
use SITBundle\Entity\Gebruiker_MolliePayment;
use SITBundle\Entity\MollieCustomer;
use SITBundle\Entity\MollieMandate;
use SITBundle\Entity\MolliePayment;
use SITBundle\Entity\ProductVerzoek;
use Swift_Attachment;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Mollie_API_Object_Method;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebshopController extends Controller
{

    /**
     * @Route("/webshop/webhook/", name="webshop_webhook")
     */
    public function paymentWebhookAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mollie = new Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));

        try {
            $dbPayment = $em->getRepository('SITBundle\Entity\MolliePayment')->findOneBy(array('paymentid' => $request->request->get('id')));
            $factuur = $dbPayment->getGebruikerMolliePayment()->getFactuur();
            $payment  = $mollie->payments->get($request->request->get('id'));

            $dbPayment->setAmountRefunded($payment->amountRefunded);
            $dbPayment->setAmountRemaining($payment->amountRemaining);
            $dbPayment->setStatus($payment->status);
            $dbPayment->setExpiryPeriod($payment->expiryPeriod);
            if($payment->expiredDatetime != null)
                $dbPayment->setExpiredDatetime(new DateTime($payment->expiredDatetime));
            if($payment->paidDatetime != null)
                $dbPayment->setPaidDatetime(new DateTime($payment->paidDatetime));
            if($payment->cancelledDatetime != null)
                $dbPayment->setCancelledDatetime(new DateTime($payment->cancelledDatetime));
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

            $em->persist($dbPayment);

            //kijken of de betaling gefaalt is. Zo ja dan de voorraad terug zetten.
            if(strtolower($payment->status) == "expired" || strtolower($payment->status) == "cancelled" || strtolower($payment->status) == "failed") {
                foreach($factuur->getProducten() as $fpk) {
                    $MaatProduct = $em->getRepository('SITBundle\Entity\MaatProduct')->findOneBy(array('product' => $fpk->getProductOriginal(), 'maat' =>$fpk->getMaatOriginal()));
                    $MaatProduct->setVoorraad($MaatProduct->getVoorraad() + $fpk->getAantal());
                    $em->persist($MaatProduct);


                    //Mails versturen naar product verzoeken
                    foreach($MaatProduct->getProductverzoeken() as $productVerzoek) {
                        //Mails versturen naar mensen die opzoek zijn naar dit product.
                        $message = Swift_Message::newInstance()
                            ->setSubject('SIT Merchandise weer op voorraad!')
                            ->setFrom('no-reply@svsit.nl')
                            ->setTo($productVerzoek->getGebruiker()->getEmailadres())
                            ->setBody(
                                $this->get('templating')->render(
                                    '_CONTENT_/mail/productverzoek.html.twig',
                                    array('voornaam' => $productVerzoek->getGebruiker()->getVoornaam(),
                                        'mp' => $MaatProduct)
                                ),
                                'text/html'
                            );
                        $this->get('mailer')->send($message);

                        $em->remove($productVerzoek);
                    }

                    $factuur->setStatus("Geannuleerd");
                    $em->persist($factuur);
                }
            }
            $em->flush();

            //Kijken of de betaling gelukt is. Zo ja dan de factuur verzenden
            if(strtolower($payment->status) == "paid") {
                $this->mailAction($factuur);
            }

        } catch (Mollie_API_Exception $e) {
            return new Response("Er is iets mis gegaan");
        }

        return new Response("");
    }

    /**
     * @Route("/winkelwagentje/remove/{id}/", name="winkelwagentje_remove_prod")
     */
    public function winkelwagentjeRemoveAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $session = new Session();
        if($session->get('products') == null) {
            return $this->redirectToRoute('winkelwagentje');
        }

        $SessionObject = $session->get('products');
        $product = $em->getRepository('SITBundle\Entity\MaatProduct')->find($id);


        $found = -1;
        foreach($SessionObject as $key=>$item) {
            if ($item['product']->getId() == $product->getId())
                $found = $key;

        }

        if($found != -1)
            unset($SessionObject[$found]);

        $session->set('products', $SessionObject);

        return $this->redirectToRoute('winkelwagentje');
    }

    /**
     * @Route("/winkelwagentje/", name="winkelwagentje")
     */
    public function winkelwagentjeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = new Session();
        $SessionObject = $session->get('products');

        if($session->get('products') != null) {
            foreach ($SessionObject as $key => $obj) {
                $SessionObject[$key]['product'] = $em->getRepository('SITBundle\Entity\MaatProduct')->find($obj['product']->getId());

                if ($this->getRequest()->isMethod('POST')) {
                    foreach ($this->get('request')->request->all() as $postKey => $param) {
                        $expl = explode('_', $postKey);
                        if ($expl[1] == $SessionObject[$key]['product']->getId()) {
                            if ($param > $SessionObject[$key]['product']->getVoorraad()) {
                                $SessionObject[$key]['aantal'] = $SessionObject[$key]['product']->getVoorraad();
                            } else {
                                $SessionObject[$key]['aantal'] = $param;
                            }
                        }
                    }
                }
            }
        }

        $session->set('products', $SessionObject);


        return $this->render('webshop/winkelwagentje.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'session' => $SessionObject
        ]));
    }

    /**
     * @Route("/webshop/", name="webshop")
     */
    public function webshopAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('webshop/webshop.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'producten' => $em->getRepository('SITBundle\Entity\Product')->findAll()
        ]));

    }

    /**
     * @Route("/webshop/checkout/", name="webshop_afrekenen")
     */
    public function webshopAfrekenenAction(Request $request)
    {
        $session = new Session();
        if($session->get('products') == null) {
            return $this->redirectToRoute('winkelwagentje');
        }

        $securityContext = $this->container->get('security.authorization_checker');

        $mollie = new Mollie_API_Client();
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

        $form = $form->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->paymentLinkStartAction($request, $form);
        }
        return $this->render('webshop/checkout.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'form' => $form->createView(),
        ]));

    }

    /**
     * @Route("/webshop/request/{id}/", name="productrequest")
     */
    public function productRequestAction(Request $request, $id = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $mp = $em->getRepository('SITBundle\Entity\MaatProduct')->find($id);
        $gebruiker = $this->get('security.context')->getToken()->getUser();
        $page = null;


        if($mp->getVoorraad() > 0) {
            return $this->redirectToRoute('productpage', array('id' => $id));
        }


        if ($this->getRequest()->isMethod('POST') && $page == null) {
            $ProductVerzoek = new ProductVerzoek();
            $ProductVerzoek->setMaatproduct($mp);
            $ProductVerzoek->setGebruiker($gebruiker);

            $em->persist($ProductVerzoek);
            $em->flush();

            $page = 'aangemeld';
        }else{
            $page = 'aanmelden';
        }


        foreach($gebruiker->getProductverzoeken() as $pv)
            if($pv->getMaatproduct()->getId() == $id)
                $page = 'alAangemeld';

        return $this->render('webshop/reserveren.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'mp' => $mp,
            'page' => $page
        ]));
    }

    /**
     * @Route("/webshop/{id}/", name="productpage")
     */
    public function productPageAction(Request $request, $id = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $session = new Session();

        if ($this->getRequest()->isMethod('POST')) {
            if($request->request->get('mp') != null && $request->request->get('mp') != '') {
                $product = $em->getRepository('SITBundle\Entity\MaatProduct')->find($request->request->get('mp'));
                if($product != null) {

                    $sessionA = $session->get('products');
                    if ($sessionA != null) {
                        $found = -1;
                        foreach($sessionA as $key=>$item) {
                            if ($item['product']->getId() == $product->getId())
                                $found = $key;

                        }

                        if($found != -1) {
                            if ($product->getVoorraad() >= $sessionA[$found]['aantal'] + 1)
                                $sessionA[$found]['aantal'] = $sessionA[$found]['aantal'] + 1;
                        }else {
                            $sessionA[] = array('product' => $product, 'aantal' => 1);
                        }

                        $session->set('products', $sessionA);
                    } else {
                        $session->set('products', array(array('product' => $product, 'aantal' => 1)));
                    }

                    return $this->redirectToRoute('winkelwagentje');
                }
            }
        }

        $winkelwagentjeAantal = 0;
        if($session->get('products') != null)
            foreach($session->get('products') as $prod)
                $winkelwagentjeAantal = $winkelwagentjeAantal + $prod['aantal'];

        return $this->render('webshop/productpage.html.twig', array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager()), [
            'product' => $em->getRepository('SITBundle\Entity\Product')->find($id),
            'maatProduct' => self::getMatenForProduct($em, $id),
            'winkelwagentjeAantal' => $winkelwagentjeAantal
        ]));
    }

    private function mailAction(Factuur $object)
    {
        //PDF aanmaken
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($this->get('templating')->render('factuur/factuur.html.twig', [
            'factuur' => $object,
            'incassoKosten' => $this->getParameter('IncassoKosten')
        ]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->set_option('defaultFont', 'Open Sans');
        $dompdf->render();

        //Get mail info
        $voornaam = ($object->getGebruiker() == null ? $object->getNaam() : $object->getGebruiker()->getVoornaam());
        $email = ($object->getGebruiker() == null ? $object->getEmail() : $object->getGebruiker()->getEmailadres());

        //Mail versturen
        $attachment = Swift_Attachment::newInstance()
            ->setFilename('factuur-'.$object->getDisplayFactuurnummer().'.pdf')
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
        $message = Swift_Message::newInstance()
            ->setSubject('Uw factuur: '.$object->getDisplayFactuurnummer())
            ->setFrom('no-reply@svsit.nl')
            ->setTo($email)
            ->setBody(
                $this->get('templating')->render(
                    '_CONTENT_/mail/send_webshop_factuur.html.twig',
                    array('voornaam' => $voornaam,
                        'factuur' => $object)
                ),
                'text/html'
            )
            ->attach($attachment)
        ;
        $this->get('mailer')->send($message);


        $message = \Swift_Message::newInstance()
            ->setSubject('Nieuwe webshop verkoop!')
            ->setFrom('no-reply@svsit.nl')
            ->setTo('bestuur@svsit.nl')
            ->setBody('Er is een nieuwe webshop verkoop gedaan. Factuurnummer: '.$object->getDisplayFactuurnummer(),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);

    }

    private function paymentLinkStartAction(Request $request, $form)
    {
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $mollie = new Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
        $gebruiker = $this->getUser();

        if ($gebruiker != null) {
            if($form->isValid()) {
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

                //Factuur Aanmaken
                $factuurnummer = 1;
                $laatsteFactuur = $em->getRepository('SITBundle\Entity\Factuur')->findOneBy(array(),
                    array('id' => 'DESC')
                );

                if($laatsteFactuur != null)
                    $factuurnummer = $laatsteFactuur->getFactuurnummer() + 1;

                $factuur = new Factuur();
                $factuur->setFactuurnummer($factuurnummer);
                $factuur->setGebruiker($gebruiker);
                $factuur->setStatus("Open");
                $factuur->setChargePaymentCost(false);

                //Producten in factuur zetten & Voorraad updaten
                foreach($session->get('products') as $key=>$prod) {
                    $maatproduct = $em->getRepository('SITBundle\Entity\MaatProduct')->find($prod['product']->getId());

                    if($maatproduct->getVoorraad() < $prod['aantal']) {
                        $prodSes = $session->get('products');
                        $prodSes[$key]['aantal'] = $maatproduct->getVoorraad();
                        $session->set('products', $prodSes);

                        return $this->redirectToRoute('winkelwagentje');
                    }

                    $maatproduct->setVoorraad($maatproduct->getVoorraad() - $prod['aantal']);
                    $em->persist($maatproduct);

                    $fp = new FactuurProduct();
                    $fp->setNaam($maatproduct->getProduct()->getNaam());
                    $fp->setType($maatproduct->getProduct()->getType());
                    $fp->setPrijs($maatproduct->getProduct()->getPrijs());
                    $fp->setBtw($maatproduct->getProduct()->getBtw());
                    $fp->setFactuur($factuur);

                    $em->persist($fp);


                    $fpk = new FactuurProductKoppeling();
                    $fpk->setAantal($prod['aantal']);
                    $fpk->setMaatOriginal($maatproduct->getMaat());
                    $fpk->setMaat($maatproduct->getMaat()->getNaam());
                    $fpk->setProduct($fp);
                    $fpk->setProductOriginal($maatproduct->getProduct());
                    $fpk->setFactuur($factuur);

                    $em->persist($fpk);

                    $factuur->addProducten($fpk);
                }

                $em->persist($factuur);

                //Betaling Starten
                try {
                    $orderID = time() . DefaultController::generateRandomString(10);
                    $customer = $mollie->customers->get($gebruiker->getMollieCustomer()->getCustomerid());

                    $paymentSettings = array(
                        "description" => "Factuur: " . $factuur->getDisplayFactuurnummer(),
                        "redirectUrl" => $this->generateUrl('webshop_complete', array('id' => $orderID), UrlGeneratorInterface::ABSOLUTE_URL),
                        //"webhookUrl" => "http://svsit.nl/",
                        "webhookUrl" => $this->generateUrl('webshop_webhook', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                        "method" => \Mollie_API_Object_Method::IDEAL,
                        "issuer" => $form->get('issuers')->getData(),
                        "amount" => $factuur->getBetalenBedrag()
                    );

                    $payment = $mollie->customers_payments->with($customer)->create($paymentSettings);


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
                    $dbPayment->setCreatedDatetime(new DateTime($payment->createdDatetime));
                    $dbPayment->setProfileId($payment->profileId);
                    $dbPayment->setCustomerId($em->getRepository('SITBundle\Entity\MollieCustomer')->findOneBy(array('customerid' => $payment->customerId)));
                    $dbPayment->setProfileId($payment->profileId);
                    $em->persist($dbPayment);

                    //Gebruiker Molliepayment aanmaken
                    $gmp = new Gebruiker_MolliePayment();
                    $gmp->setMolliePayment($dbPayment);
                    $gmp->setGebruiker($this->getUser());
                    $gmp->setFactuur($factuur);
                    $em->persist($gmp);

                    $dbPayment->setGebruikerMolliePayment($gmp);
                    $em->persist($dbPayment);

                    $em->flush();

                    return $this->redirect($payment->getPaymentUrl());
                } catch (\Mollie_API_Exception $e) {
                    return new Response("Er is iets mis gegaan");
                }

                return new Response("Er is iets mis gegaan. Probeer het later opnieuw.");
            }
        }
    }

    /**
     * @Route("/webshop/complete/{id}", name="webshop_complete")
     */
    public function paymentCompleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $dbPayment = $em->getRepository('SITBundle\Entity\MolliePayment')->findOneBy(array('UID' => $id));

        $session = new Session();
        $session->clear();

        $page = null;
        if($dbPayment->getStatus() == 'paid')
            $page = '_CONTENT_/webshop/payment_complete.html.twig';
        elseif($dbPayment->getStatus() == 'cancelled')
            $page = '_CONTENT_/webshop/payment_cancelled.html.twig';
        else
            $page = '_CONTENT_/webshop/payment_open.html.twig';

        return $this->render($page, array_merge(DefaultController::generateBaseVars($this->getDoctrine()->getManager())));
    }



    public static function getMatenForProduct($em, $id) {
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('SITBundle\Entity\MaatProduct', 'u')
            ->where('u.product = :product')
            ->orderBy('u.id', 'ASC')
            ->setParameters(array('product' => $id));

        return $qb->getQuery()->getResult();


    }
}