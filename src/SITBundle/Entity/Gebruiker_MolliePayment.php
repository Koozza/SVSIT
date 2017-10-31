<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="gebruiker_molliepayment")
 */
class Gebruiker_MolliePayment
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Gebruiker")
     * @ORM\JoinColumn(name="gebruiker_id", referencedColumnName="id")
     */
    private $gebruiker;

    /**
     * @ORM\OneToOne(targetEntity="MolliePayment", inversedBy="gebruikerMolliePayment")
     * @ORM\JoinColumn(name="molliePayment_id", referencedColumnName="id")
     */
    private $molliePayment;

    /**
     * @ORM\ManyToOne(targetEntity="BetaalLink", inversedBy="gebruikerMolliePayments")
     */
    private $betaalLink;

    /**
     * @ORM\ManyToOne(targetEntity="Factuur", inversedBy="gebruikerMolliePayments", fetch="EAGER")
     */
    private $factuur;


    function __toString()
    {
        $user = '';
        if($this->gebruiker != null) {
            $user = $this->gebruiker->getFullName();
        }else{
            if(property_exists($this->molliePayment->getMetaData(), 'fullname')) {
                $user = $this->molliePayment->getMetaData()->fullname;
            }
        }

        return $user.' ('.$this->molliePayment->getStatus().', '.$this->molliePayment->getPaymentid().')';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return Gebruiker_MolliePayment
     */
    public function setGebruiker(\SITBundle\Entity\Gebruiker $gebruiker = null)
    {
        $this->gebruiker = $gebruiker;

        return $this;
    }

    /**
     * Get gebruiker
     *
     * @return \SITBundle\Entity\Gebruiker
     */
    public function getGebruiker()
    {
        return $this->gebruiker;
    }

    /**
     * Set molliePayment
     *
     * @param \SITBundle\Entity\MolliePayment $molliePayment
     *
     * @return Gebruiker_MolliePayment
     */
    public function setMolliePayment(\SITBundle\Entity\MolliePayment $molliePayment = null)
    {
        $this->molliePayment = $molliePayment;

        return $this;
    }

    /**
     * Get molliePayment
     *
     * @return \SITBundle\Entity\MolliePayment
     */
    public function getMolliePayment()
    {
        return $this->molliePayment;
    }

    /**
     * Set betaalLink
     *
     * @param \SITBundle\Entity\BetaalLink $betaalLink
     *
     * @return Gebruiker_MolliePayment
     */
    public function setBetaalLink(\SITBundle\Entity\BetaalLink $betaalLink = null)
    {
        $this->betaalLink = $betaalLink;

        return $this;
    }

    /**
     * Get betaalLink
     *
     * @return \SITBundle\Entity\BetaalLink
     */
    public function getBetaalLink()
    {
        return $this->betaalLink;
    }

    /**
     * Set factuur
     *
     * @param \SITBundle\Entity\Factuur $factuur
     *
     * @return Gebruiker_MolliePayment
     */
    public function setFactuur(\SITBundle\Entity\Factuur $factuur = null)
    {
        $this->factuur = $factuur;
    
        return $this;
    }

    /**
     * Get factuur
     *
     * @return \SITBundle\Entity\Factuur
     */
    public function getFactuur()
    {
        return $this->factuur;
    }
}
