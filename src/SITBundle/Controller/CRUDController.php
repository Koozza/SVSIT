<?php

namespace SITBundle\Controller;

use Dompdf\Dompdf;
use SITBundle\Entity\Betaling;
use SITBundle\Entity\MolliePayment;
use Swift_Message;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\DateTime;

class CRUDController extends Controller
{
    /**
     * Admin: Send PDF to user. Used for facturen
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function mailAction(Request $request)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }

        //Create PDF
        $pdf = new Dompdf();
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->loadHtml($this->get('templating')->render('factuur/factuur.html.twig', [
            'factuur' => $object,
            'incassoKosten' => $this->getParameter('IncassoKosten')
        ]));
        $pdf->setPaper('A4', 'portrait');
        $pdf->set_option('defaultFont', 'Open Sans');
        $pdf->render();

        //Get user info for usage in the mail
        $firstName = ($object->getGebruiker() == null ? $object->getNaam() : $object->getGebruiker()->getVoornaam());
        $email = ($object->getGebruiker() == null ? $object->getEmail() : $object->getGebruiker()->getEmailadres());

        //Add attachment and send mail to user
        $attachment = \Swift_Attachment::newInstance()
            ->setFilename('factuur-'.$object->getDisplayFactuurnummer().'.pdf')
            ->setContentType('application/pdf')
            ->setBody($pdf->output());
        $message = \Swift_Message::newInstance()
            ->setSubject('Uw factuur: '.$object->getDisplayFactuurnummer())
            ->setFrom('no-reply@svsit.nl')
            ->setTo($email)
            ->setBody(
                $this->get('templating')->render(
                    '_CONTENT_/mail/send_factuur.html.twig',
                    array('voornaam' => $firstName,
                        'factuur' => $object)
                ),
                'text/html'
            )
            ->attach($attachment)
        ;
        $this->get('mailer')->send($message);

        $this->addFlash('sonata_flash_success', 'Mail met factuur is opnieuw verstuurd!');

        return new RedirectResponse($this->admin->generateUrl('show', array('id' => $object->getId())));
    }


    /**
     * Download PDF for a factuur
     *
     * @param Request $request
     *
     * @return Response
     */
    public function pdfAction(Request $request)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }


        $pdf = new Dompdf();
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->loadHtml($this->renderView('factuur/factuur.html.twig', [
            'factuur' => $object,
            'incassoKosten' => $this->container->getParameter('IncassoKosten')
        ]));
        $pdf->setPaper('A4', 'portrait');
        $pdf->set_option('defaultFont', 'Open Sans');
        $pdf->render();

        return new Response($pdf->stream());
    }


    /**
     * Admin page to set a factuur to paid.
     * Also handles POSt.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function betaaldAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }

        if($request == null)
            $request = new Request();

        //Check if POST has been send
        if (($request->isMethod('POST'))) {
            $object->setStatus("Betaald");
            $em->persist($object);
            $em->flush();

            $this->addFlash('sonata_flash_success', 'Factuur '.$object->getDisplayFactuurnummer().' heeft nu als status: Betaald!');

            //Redirect back to list
            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        //Render form
        return $this->render('SITBundle::CRUD\factuur_betalen_confirm.html.twig', [
            'obj' => $object
        ]);

    }

    //Action voor annuleren PDF

    /**
     *
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function annulerenAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }

        if($request == null)
            $request = new Request();

        if (($request->isMethod('POST'))) {
            //Voorraad terug zetten
            foreach($object->getProducten() as $product) {
                $MaatProduct = $em->getRepository('SITBundle\Entity\MaatProduct')->findOneBy(array('product' => $product->getProductOriginal(), 'maat' =>$product->getMaatOriginal()));
                if($MaatProduct != null) {
                    $MaatProduct->setVoorraad($MaatProduct->getVoorraad() + $product->getAantal());
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
                }else{
                    $this->addFlash('sonata_flash_error', 'Geen voorraad gevonden voor: '.$product->getProductOriginal()->getNaam().' maat: '.$product->getMaatOriginal()->getNaam().'. Voorraad is niet terug gezet.');
                }
            }

            //Betaallink verwijderen als die er een is
            if($object->getBetaalLink() != null) {
                $em->remove($object->getBetaalLink());
                $object->setBetaalLink(null);
            }

            $object->setStatus("Geannuleerd");
            $em->persist($object);
            $em->flush();

            $this->addFlash('sonata_flash_success', 'Factuur '.$object->getDisplayFactuurnummer().' is nu geannuleerd');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        return $this->render('SITBundle::CRUD\factuur_annuleren_confirm.html.twig', [
            'obj' => $object
        ]);

    }

    public function batchActionIncasseren(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        $modelManager = $this->admin->getModelManager();
        $selectedModels = $selectedModelQuery->execute();


        if(!$this->admin->isGranted('MOLLIE'))
            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));

        foreach($selectedModels as $object) {
            if($object->checkAllowedIncasso())
                $this->betalingUitvoeren($object, $request, false, true);
        }

        $this->addFlash('sonata_flash_success', 'Incasso\'s met succes uitgevoerd!');

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    public function incasserenAction(Request $request)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }

        if(!$this->admin->isGranted('MOLLIE') || !$object->checkAllowedIncasso())
            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));

        return $this->betalingUitvoeren($object, $request, true);
    }

    public function intrekkenAction(Request $request)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException('unable to find the object');
        }

        if(!$this->admin->isGranted('MOLLIE'))
            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));

        return $this->betalingIntrekken($object, $request, true);
    }

    private function betalingIntrekken($object, Request $request = null, $flashMessage) {
        if($request == null)
            $request = new Request();

        $em = $this->getDoctrine()->getManager();
        if (($request->isMethod('POST'))) {
            $object->setStatus('failed');
            $object->setManuallyCancelledDatetime(new \DateTime());

            $em->persist($object);
            $em->flush();

            if($flashMessage)
                $this->addFlash('sonata_flash_success', 'Betaling is ingetrokken!');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }


        return $this->render('SITBundle::CRUD\betaling_intrekken_confirm.html.twig', [
            'obj' => $object,
            'incassoKosten' => $this->container->getParameter('IncassoKosten')
        ]);
    }

    private function betalingUitvoeren($object, Request $request = null, $flashMessage, $batchAction = false) {
        if($request == null)
            $request = new Request();

        //Checken of de incasso wel uitgevoert kan worden (of het mandaat nog bestaat).
        //Volgens de database is er sowieso een mandaat; anders faalt de CheckAllowedIncasso functie.
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));
        $em = $this->getDoctrine()->getManager();

        $validMandate = null;
        $mandates = $object->getMollieCustomer()->getMandates();
        foreach ($mandates as $mandate) {
            if ($mandate->getStatus() == "valid") {
                $realmandate = $mollie->customers_mandates->withParentId($object->getMollieCustomer()->getCustomerid())->get($mandate->getMandateid());
                if($realmandate->status == "valid") {
                    $validMandate = $realmandate;
                    break;
                }
            }
        }


        if (($request->isMethod('POST') || $batchAction) && $validMandate != null) {
            $orderID = time().DefaultController::generateRandomString(10);

            $customer = $mollie->customers->get($object->getMollieCustomer()->getCustomerid());
            $payment = $mollie->customers_payments->with($customer)->create(array(
                "amount"        => $object->getLidmaatschap()->getPrijs() + $this->container->getParameter('IncassoKosten'),
                "description"   => "SIT Lidmaatschap: ".$object->getLidmaatschap()->getBeschrijving(),
                "recurringType" => \Mollie_API_Object_Payment::RECURRINGTYPE_RECURRING,
            ));

            //Onze eigen betaling ook aanmaken
            $betaling = new Betaling();
            $betaling->setDatum(new \DateTime());
            $betaling->setGebruiker($object);
            $betaling->setIsLifetime(false);
            foreach(PaymentController::getNextPeriod($object, $em, $object->getLidmaatschap()->getAantalPeriodes()) as $periode)
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
            $betaling->setMolliePayment($dbPayment);


            $em->persist($betaling);
            $em->persist($dbPayment);

            $em->flush();

            if($flashMessage)
                $this->addFlash('sonata_flash_success', 'Incasso met succes uitgevoerd!');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }



        return $this->render('SITBundle::CRUD\incasseren_confirm.html.twig', [
            'obj' => $object,
            'validMandate' => $validMandate,
            'incassoKosten' => $this->container->getParameter('IncassoKosten'),
            'periodes' => PaymentController::getNextPeriod($object, $em, $object->getLidmaatschap()->getAantalPeriodes())
        ]);
    }
}