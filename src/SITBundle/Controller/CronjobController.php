<?php

namespace SITBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CronjobController extends Controller
{
    /**
     * Force update of all payments that are not failed or completed.
     * Remove DIE(); first.
     *
     * @param Request $request
     * @param         $id
     *
     * @return Response
     * @Route("/cronjob/{id}")
     */
    public function setPasswordAction(Request $request, $id)
    {
        die();
        $this->checkPayments();

        return new Response("Done");
    }


    /**
     * Force update of all payments that are not failed or completed.
     *
     * @return Response
     */
    private function checkPayments()
    {
        $em = $this->getDoctrine()->getManager();
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->container->getParameter('MollieAPIKey'));

        $pendingPayments = $em->getRepository('SITBundle\Entity\MolliePayment')->findBy(array(
            'status' => 'pending',
            'method' => 'directdebit'
        ));

        foreach ($pendingPayments as $dbPayment) {
            try {
                $payment = $mollie->payments->get($dbPayment->getPaymentid());

                $dbPayment->setAmountRefunded($payment->amountRefunded);
                $dbPayment->setAmountRemaining($payment->amountRemaining);
                $dbPayment->setStatus($payment->status);
                $dbPayment->setExpiryPeriod($payment->expiryPeriod);
                if ($payment->expiredDatetime != null) {
                    $dbPayment->setExpiredDatetime(new \DateTime($payment->expiredDatetime));
                }
                if ($payment->paidDatetime != null) {
                    $dbPayment->setPaidDatetime(new \DateTime($payment->paidDatetime));
                }
                if ($payment->cancelledDatetime != null) {
                    $dbPayment->setCancelledDatetime(new \DateTime($payment->cancelledDatetime));
                }
                $dbPayment->setProfileId($payment->profileId);
                $dbPayment->setRecurringType($payment->recurringType);
                $dbPayment->setSubscriptionId($payment->subscriptionId);
                $dbPayment->setLocale($payment->locale);
                if ($payment->details != null) {
                    $dbPayment->setConsumerName($payment->details->consumerName);
                    $dbPayment->setConsumerAccount($payment->details->consumerAccount);
                    $dbPayment->setConsumerBic($payment->details->consumerBic);
                }

                if ($payment->mandateId != null) {
                    //Get or create matching Mandate
                    $dbMandate = $em->getRepository('SITBundle\Entity\MollieMandate')->findOneBy(array('mandateid' => $payment->mandateId));
                    if ($dbMandate == null) {
                        try {
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
                        } catch (Mollie_API_Exception $e) {
                            return new Response("Er is iets mis gegaan");
                        }
                    }

                    $dbPayment->setMandateId($dbMandate);
                    $em->persist($dbMandate);
                }
                $em->persist($dbPayment);

                $em->flush();
            } catch (Mollie_API_Exception $e) {
                return new Response("Er is iets mis gegaan");
            }
        }
    }
}