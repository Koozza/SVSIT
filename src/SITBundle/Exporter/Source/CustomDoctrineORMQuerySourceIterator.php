<?php
namespace SITBundle\Exporter\Source;

use Exporter\Source\DoctrineORMQuerySourceIterator;
use Symfony\Component\Validator\Constraints\DateTime;

class CustomDoctrineORMQuerySourceIterator extends DoctrineORMQuerySourceIterator
{

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function getValue($value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            $result = [];
            foreach ($value as $item) {
                $result[] = $this->getValue($item);
            }
            $value = implode(', ', $result);
        } elseif ($value instanceof \DateTime) {
            $duur = new \DateTime("1/1/1970");
            $duur->add(new \DateInterval("P3D"));

            if($value < $duur)
                $value = $value->format("H:i:s");
            else
                $value = $value->format($this->dateTimeFormat);

        } elseif (is_object($value)) {
            $value = (string) $value;
        } elseif (is_bool($value)) {
            if ($value)
                $value = "Ja";
            else
                $value = "Nee";
        }elseif (preg_match('/^€[0-9]+$/', $value)) {
            if((strpos($value, ',') === false)) {
                $value = $value.",00";
            }
        }

        return $value;
    }
}