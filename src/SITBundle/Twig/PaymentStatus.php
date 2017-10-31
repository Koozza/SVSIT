<?php
namespace SITBundle\Twig;

class PaymentStatus extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('paymentStatus', array($this, 'PaymentStatus')),
        );
    }

    public function PaymentStatus($status)
    {
        return $this->translatePaymentStatus($status);
    }

    public function getName()
    {
        return 'sit_paymentstatus_ext';
    }

    private function translatePaymentStatus($status) {
        $org = array("open", "cancelled", "expired", "failed", "pending", "paid", "paidout", "charged_back", "valid", "pending", "invalid", "ideal", "directdebit");
        $new   = array("Wordt Verwerkt", "Geannuleerd", "Verlopen", "Mislukt", "Wordt Verwerkt", "Betaald", "Terugbetaald", "Terugbetaald", "Geldig", "In Afwachting", "Ongeldig", "IDeal", "SEPA Incasso");

        return str_replace($org, $new, $status);
    }
}