<?php
namespace SITBundle\Twig;

use Doctrine\ORM\EntityManager;
use SITBundle\Entity\ContentEntry;

class DecodeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('unescape', array($this, 'unescape')),
        );
    }

    public function unescape($value)
    {
        return html_entity_decode($value);
    }
}