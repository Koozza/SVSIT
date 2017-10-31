<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="betaling")
 */
class Betaling
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Gebruiker", inversedBy="betalingen")
     */
    private $gebruiker;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLifetime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datum;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Periode", inversedBy="betalingen")
     * @ORM\JoinTable(name="betaling_periode",
     *   joinColumns={
     *     @ORM\JoinColumn(name="betaling_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="periode_id", referencedColumnName="id")
     *   }
     * )
     */
    private $periodes;

    /**
     * @ORM\OneToOne(targetEntity="MolliePayment", inversedBy="betaling")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="MolliePaymentId", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $molliePayment;

    public function __toString()
    {
        return ''.$this->getGebruiker();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->datum = new \DateTime();
        $this->periodes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set isLifetime
     *
     * @param boolean $isLifetime
     *
     * @return Betaling
     */
    public function setIsLifetime($isLifetime)
    {
        $this->isLifetime = $isLifetime;

        return $this;
    }

    /**
     * Get isLifetime
     *
     * @return boolean
     */
    public function getIsLifetime()
    {
        return $this->isLifetime;
    }

    /**
     * Set gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return Betaling
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
     * Add periode
     *
     * @param \SITBundle\Entity\Periode $periode
     *
     * @return Betaling
     */
    public function addPeriode(\SITBundle\Entity\Periode $periode)
    {
        $this->periodes[] = $periode;

        return $this;
    }

    /**
     * Remove periode
     *
     * @param \SITBundle\Entity\Periode $periode
     */
    public function removePeriode(\SITBundle\Entity\Periode $periode)
    {
        $this->periodes->removeElement($periode);
    }

    /**
     * Get periodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPeriodes()
    {
        return $this->periodes;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     *
     * @return Betaling
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;

        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * Set molliePayment
     *
     * @param \SITBundle\Entity\MolliePayment $molliePayment
     *
     * @return Betaling
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
}
