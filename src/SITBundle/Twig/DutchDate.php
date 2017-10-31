<?php
namespace SITBundle\Twig;

class DutchDate extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dutchDate', array($this, 'DutchDate')),
        );
    }

    public function DutchDate($date, $format = 'Y-m-d H:i:s')
    {
        return $this->translateMonthnames($date->format($format));
    }

    public function getName()
    {
        return 'sit_dutchdate_ext';
    }

    private function translateMonthnames($dateString) {
        $eng = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        $nl   = array("Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December");

        return str_replace($eng, $nl, $dateString);
    }
}