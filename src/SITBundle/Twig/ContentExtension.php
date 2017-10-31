<?php
namespace SITBundle\Twig;

use Doctrine\ORM\EntityManager;
use SITBundle\Entity\ContentEntry;

class ContentExtension extends \Twig_Extension
{
    private $em;
    private $sessionHelper;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getFunctions() {
        return array(
            'getContent' => new \Twig_Function_Method($this, 'getContent')
        );
    }

    public function getContent ($UID) {
        $contentEntry = $this->em->getRepository('SITBundle\Entity\ContentEntry')->findOneBy(array('UID' => $UID));
        if($contentEntry->getType() == "ckeditor")
            return $contentEntry->getText();
    }

    public function getName() {
        return 'SITBundle';
    }
}