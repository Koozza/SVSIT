<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="periode")
 */
class Periode
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $naam;

    /**
     * @ORM\Column(type="date")
     */
    private $begindatum;

    /**
     * @ORM\Column(type="date")
     */
    private $einddatum;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Betaling", mappedBy="periodes")
     *
     */
    private $betalingen;

    /**
     * @ORM\OneToOne(targetEntity="Periode")
     */
    private $volgendePeriode;


    public function __toString()
    {
        return $this->naam;
    }



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->betalingen = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Periode
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
     * Set begindatum
     *
     * @param \DateTime $begindatum
     *
     * @return Periode
     */
    public function setBegindatum($begindatum)
    {
        $this->begindatum = $begindatum;

        return $this;
    }

    /**
     * Get begindatum
     *
     * @return \DateTime
     */
    public function getBegindatum()
    {
        return $this->begindatum;
    }

    /**
     * Set einddatum
     *
     * @param \DateTime $einddatum
     *
     * @return Periode
     */
    public function setEinddatum($einddatum)
    {
        $this->einddatum = $einddatum;

        return $this;
    }

    /**
     * Get einddatum
     *
     * @return \DateTime
     */
    public function getEinddatum()
    {
        return $this->einddatum;
    }

    /**
     * Add betalingen
     *
     * @param \SITBundle\Entity\Betaling $betalingen
     *
     * @return Periode
     */
    public function addBetalingen(\SITBundle\Entity\Betaling $betalingen)
    {
        $this->betalingen[] = $betalingen;

        return $this;
    }

    /**
     * Remove betalingen
     *
     * @param \SITBundle\Entity\Betaling $betalingen
     */
    public function removeBetalingen(\SITBundle\Entity\Betaling $betalingen)
    {
        $this->betalingen->removeElement($betalingen);
    }

    /**
     * Get betalingen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBetalingen()
    {
        return $this->betalingen;
    }

    /**
     * Set volgendePeriode
     *
     * @param \SITBundle\Entity\Periode $volgendePeriode
     *
     * @return Periode
     */
    public function setVolgendePeriode(\SITBundle\Entity\Periode $volgendePeriode = null)
    {
        $this->volgendePeriode = $volgendePeriode;

        return $this;
    }

    /**
     * Get volgendePeriode
     *
     * @return \SITBundle\Entity\Periode
     */
    public function getVolgendePeriode()
    {
        return $this->volgendePeriode;
    }
}