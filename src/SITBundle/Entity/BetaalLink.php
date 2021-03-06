<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SITBundle\Controller\DefaultController;

/**
 * @ORM\Entity
 * @ORM\Table(name="betaallink")
 */
class BetaalLink
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $naam;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $UID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $multiuser;

    /**
     * @ORM\Column(type="boolean")
     */
    private $alleenIngelogged;

    /**
     * @ORM\Column(type="boolean")
     */
    private $alleenLeden;

    /**
     * @ORM\Column(type="boolean")
     */
    private $chargePaymentCost;

    /**
     * @ORM\OneToMany(targetEntity="Gebruiker_MolliePayment", mappedBy="betaalLink")
     */
    private $gebruikerMolliePayments;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $amount;

    /**
     * @Assert\Choice({"Admin", "Factuur"})
     * @ORM\Column(type="string", length=12)
     */
    private $type;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $verloopDatum;

    private $URL;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->gebruikerMolliePayments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->UID = DefaultController::generateRandomString(15);

        $this->multiuser = false;
        $this->chargePaymentCost = false;
        $this->alleenIngelogged = false;
        $this->alleenLeden = false;
    }

    public function __toString()
    {
        return $this->naam;
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
     * Set naam
     *
     * @param string $naam
     *
     * @return BetaalLink
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;

        return $this;
    }

    /**
     * Get naam
     *
     * @return string
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set uID
     *
     * @param string $uID
     *
     * @return BetaalLink
     */
    public function setUID($uID)
    {
        $this->UID = $uID;

        return $this;
    }

    /**
     * Get uID
     *
     * @return string
     */
    public function getUID()
    {
        return $this->UID;
    }

    /**
     * Set multiuser
     *
     * @param boolean $multiuser
     *
     * @return BetaalLink
     */
    public function setMultiuser($multiuser)
    {
        $this->multiuser = $multiuser;

        return $this;
    }

    /**
     * Get multiuser
     *
     * @return boolean
     */
    public function getMultiuser()
    {
        return $this->multiuser;
    }

    /**
     * Set alleenLeden
     *
     * @param boolean $alleenLeden
     *
     * @return BetaalLink
     */
    public function setAlleenLeden($alleenLeden)
    {
        $this->alleenLeden = $alleenLeden;

        return $this;
    }

    /**
     * Get alleenLeden
     *
     * @return boolean
     */
    public function getAlleenLeden()
    {
        return $this->alleenLeden;
    }

    /**
     * Set chargePaymentCost
     *
     * @param boolean $chargePaymentCost
     *
     * @return BetaalLink
     */
    public function setChargePaymentCost($chargePaymentCost)
    {
        $this->chargePaymentCost = $chargePaymentCost;

        return $this;
    }

    /**
     * Get chargePaymentCost
     *
     * @return boolean
     */
    public function getChargePaymentCost()
    {
        return $this->chargePaymentCost;
    }

    /**
     * Add gebruikerMolliePayment
     *
     * @param \SITBundle\Entity\Gebruiker_MolliePayment $gebruikerMolliePayment
     *
     * @return BetaalLink
     */
    public function addGebruikerMolliePayment(\SITBundle\Entity\Gebruiker_MolliePayment $gebruikerMolliePayment)
    {
        $this->gebruikerMolliePayments[] = $gebruikerMolliePayment;

        return $this;
    }

    /**
     * Remove gebruikerMolliePayment
     *
     * @param \SITBundle\Entity\Gebruiker_MolliePayment $gebruikerMolliePayment
     */
    public function removeGebruikerMolliePayment(\SITBundle\Entity\Gebruiker_MolliePayment $gebruikerMolliePayment)
    {
        $this->gebruikerMolliePayments->removeElement($gebruikerMolliePayment);
    }

    /**
     * Get gebruikerMolliePayments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGebruikerMolliePayments()
    {
        return $this->gebruikerMolliePayments;
    }

    /**
     * @return mixed
     */
    public function getURL()
    {
        return $this->URL;
    }

    /**
     * @param mixed $URL
     */
    public function setURL($URL)
    {
        $this->URL = $URL;
    }

    /**
     * @return mixed
     */
    public function getTotaalAantalBetalingen()
    {
        return count($this->getGebruikerMolliePayments());
    }

    /**
     * @return mixed
     */
    public function getBetalendeGebruikers()
    {
        $gebruikers = new \Doctrine\Common\Collections\ArrayCollection();

        foreach($this->getGebruikerMolliePayments() as $gmpayment) {
            if($gmpayment->getMolliePayment()->getStatus() == "paid") {
                if($gmpayment->getGebruiker() != null)
                    $gebruikers->add($gmpayment->getGebruiker());
                else {
                    if ($gmpayment->getMolliePayment()->getMetadata() != null) {
                        if ($gmpayment->getMolliePayment()->getMetadata()->fullname != null) {
                            $gebruikers->add($gmpayment->getMolliePayment()->getMetadata()->fullname . ' (' . $gmpayment->getMolliePayment()->getConsumerName() . ')');
                        }
                    }else{
                        $gebruikers->add($gmpayment->getMolliePayment()->getConsumerName());
                    }
                }
            }
        }

        return $gebruikers;
    }

    /**
     * @return mixed
     */
    public function isUsed()
    {
        $used = false;

        foreach($this->getGebruikerMolliePayments() as $gmpayment) {
            if($gmpayment->getMolliePayment()->getStatus() == "paid") {
                $used = true;
            }
        }

        return $used;
    }



    /**
     * Set alleenIngelogged
     *
     * @param boolean $alleenIngelogged
     *
     * @return BetaalLink
     */
    public function setAlleenIngelogged($alleenIngelogged)
    {
        $this->alleenIngelogged = $alleenIngelogged;

        return $this;
    }

    /**
     * Get alleenIngelogged
     *
     * @return boolean
     */
    public function getAlleenIngelogged()
    {
        return $this->alleenIngelogged;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return BetaalLink
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return BetaalLink
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set verloopDatum
     *
     * @param \DateTime $verloopDatum
     *
     * @return BetaalLink
     */
    public function setVerloopDatum($verloopDatum)
    {
        $this->verloopDatum = $verloopDatum;

        return $this;
    }

    /**
     * Get verloopDatum
     *
     * @return \DateTime
     */
    public function getVerloopDatum()
    {
        return $this->verloopDatum;
    }
}
