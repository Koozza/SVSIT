<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="evenement")
 */
class Evenement
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
     * @ORM\Column(type="text")
     */
    private $omschrijving;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datum;

    /**
     * @ORM\Column(type="datetime")
     */
    private $einddatum;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locatieNaam;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locatieAdres;

    /**
     * @ORM\OneToOne(targetEntity="Position", cascade={"persist", "remove"})
     */
    private $locatiePositie;


    public function __construct()
    {
        $this->datum = new \DateTime();
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

    public function hasPositie() {
        if($this->locatiePositie != null)
            return true;
        else
            return false;
    }

    /**
     * Set naam
     *
     * @param string $naam
     *
     * @return Evenement
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
     * Set omschrijving
     *
     * @param string $omschrijving
     *
     * @return Evenement
     */
    public function setOmschrijving($omschrijving)
    {
        $this->omschrijving = $omschrijving;

        return $this;
    }

    /**
     * Get omschrijving
     *
     * @return string
     */
    public function getOmschrijving()
    {
        return $this->omschrijving;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     *
     * @return Evenement
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
     * Set locatieNaam
     *
     * @param string $locatieNaam
     *
     * @return Evenement
     */
    public function setLocatieNaam($locatieNaam)
    {
        $this->locatieNaam = $locatieNaam;

        return $this;
    }

    /**
     * Get locatieNaam
     *
     * @return string
     */
    public function getLocatieNaam()
    {
        return $this->locatieNaam;
    }

    /**
     * Set locatieAdres
     *
     * @param string $locatieAdres
     *
     * @return Evenement
     */
    public function setLocatieAdres($locatieAdres)
    {
        $this->locatieAdres = $locatieAdres;

        return $this;
    }

    /**
     * Get locatieAdres
     *
     * @return string
     */
    public function getLocatieAdres()
    {
        return $this->locatieAdres;
    }

    /**
     * Set locatiePositie
     *
     * @param \SITBundle\Entity\Position $locatiePositie
     *
     * @return Evenement
     */
    public function setLocatiePositie(\SITBundle\Entity\Position $locatiePositie = null)
    {
        $this->locatiePositie = $locatiePositie;

        return $this;
    }

    /**
     * Get locatiePositie
     *
     * @return \SITBundle\Entity\Position
     */
    public function getLocatiePositie()
    {
        return $this->locatiePositie;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Evenement
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
     * Set einddatum
     *
     * @param \DateTime $einddatum
     *
     * @return Evenement
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
}
