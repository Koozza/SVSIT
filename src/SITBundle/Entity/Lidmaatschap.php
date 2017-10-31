<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="lidmaatschap")
 */
class Lidmaatschap
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
    private $beschrijving;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $afkorting;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $prijs;

    /**
     * @ORM\Column(type="integer")
     */
    private $aantalPeriodes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibleOnWebsite;

    /**
     * @ORM\Column(type="boolean")
     */
    private $incasseerbaar;

    /**
     * @ORM\OneToMany(targetEntity="Gebruiker", mappedBy="lidmaatschap")
     */
    private $gebruikers;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Studierichting", mappedBy="lidmaatschappen")
     *
     */
    private $studierichtingen;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->incasseerbaar = true;
        $this->gebruikers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->studierichtingen = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->beschrijving;
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
     * Set beschrijving
     *
     * @param string $beschrijving
     *
     * @return Product
     */
    public function setBeschrijving($beschrijving)
    {
        $this->beschrijving = $beschrijving;

        return $this;
    }

    /**
     * Get beschrijving
     *
     * @return string
     */
    public function getBeschrijving()
    {
        return $this->beschrijving;
    }

    /**
     * Add gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return Product
     */
    public function addGebruiker(\SITBundle\Entity\Gebruiker $gebruiker)
    {
        $this->gebruikers[] = $gebruiker;

        return $this;
    }

    /**
     * Remove gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     */
    public function removeGebruiker(\SITBundle\Entity\Gebruiker $gebruiker)
    {
        $this->gebruikers->removeElement($gebruiker);
    }

    /**
     * Get gebruikers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGebruikers()
    {
        return $this->gebruikers;
    }

    /**
     * Set visibleOnWebsite
     *
     * @param boolean $visibleOnWebsite
     *
     * @return Lidmaatschap
     */
    public function setVisibleOnWebsite($visibleOnWebsite)
    {
        $this->visibleOnWebsite = $visibleOnWebsite;

        return $this;
    }

    /**
     * Get visibleOnWebsite
     *
     * @return boolean
     */
    public function getVisibleOnWebsite()
    {
        return $this->visibleOnWebsite;
    }

    /**
     * Set afkorting
     *
     * @param string $afkorting
     *
     * @return Lidmaatschap
     */
    public function setAfkorting($afkorting)
    {
        $this->afkorting = $afkorting;

        return $this;
    }

    /**
     * Get afkorting
     *
     * @return string
     */
    public function getAfkorting()
    {
        return $this->afkorting;
    }

    /**
     * Set prijs
     *
     * @param string $prijs
     *
     * @return Lidmaatschap
     */
    public function setPrijs($prijs)
    {
        $this->prijs = $prijs;

        return $this;
    }

    /**
     * Get prijs
     *
     * @return string
     */
    public function getPrijs()
    {
        return $this->prijs;
    }

    /**
     * Set incasseerbaar
     *
     * @param boolean $incasseerbaar
     *
     * @return Lidmaatschap
     */
    public function setIncasseerbaar($incasseerbaar)
    {
        $this->incasseerbaar = $incasseerbaar;

        return $this;
    }

    /**
     * Get incasseerbaar
     *
     * @return boolean
     */
    public function getIncasseerbaar()
    {
        return $this->incasseerbaar;
    }

    /**
     * Set aantalPeriodes
     *
     * @param integer $aantalPeriodes
     *
     * @return Lidmaatschap
     */
    public function setAantalPeriodes($aantalPeriodes)
    {
        $this->aantalPeriodes = $aantalPeriodes;

        return $this;
    }

    /**
     * Get aantalPeriodes
     *
     * @return integer
     */
    public function getAantalPeriodes()
    {
        return $this->aantalPeriodes;
    }

    /**
     * Add studierichtingen
     *
     * @param \SITBundle\Entity\Studierichting $studierichtingen
     *
     * @return Lidmaatschap
     */
    public function addStudierichtingen(\SITBundle\Entity\Studierichting $studierichtingen)
    {
        $this->studierichtingen[] = $studierichtingen;

        return $this;
    }

    /**
     * Remove studierichtingen
     *
     * @param \SITBundle\Entity\Studierichting $studierichtingen
     */
    public function removeStudierichtingen(\SITBundle\Entity\Studierichting $studierichtingen)
    {
        $this->studierichtingen->removeElement($studierichtingen);
    }

    /**
     * Get studierichtingen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStudierichtingen()
    {
        return $this->studierichtingen;
    }
}
