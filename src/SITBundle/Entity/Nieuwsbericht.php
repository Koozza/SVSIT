<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="nieuwsbericht")
 */
class Nieuwsbericht
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
    private $titel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $auteur;

    /**
     * @ORM\Column(type="text")
     */
    private $bericht;

    /**
     * @ORM\Column(type="datetime")
     */
    private $gepubliceerd;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $edited;


    public function __construct()
    {
        $this->gepubliceerd = new \DateTime();
    }

    function __toString()
    {
        return $this->titel;
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
     * Set titel
     *
     * @param string $titel
     *
     * @return Nieuwsbericht
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * Get titel
     *
     * @return string
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * Set auteur
     *
     * @param string $auteur
     *
     * @return Nieuwsbericht
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get auteur
     *
     * @return string
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set bericht
     *
     * @param string $bericht
     *
     * @return Nieuwsbericht
     */
    public function setBericht($bericht)
    {
        $this->bericht = $bericht;

        return $this;
    }

    /**
     * Get bericht
     *
     * @return string
     */
    public function getBericht()
    {
        return $this->bericht;
    }

    /**
     * Set gepubliceerd
     *
     * @param \DateTime $gepubliceerd
     *
     * @return Nieuwsbericht
     */
    public function setGepubliceerd($gepubliceerd)
    {
        $this->gepubliceerd = $gepubliceerd;

        return $this;
    }

    /**
     * Get gepubliceerd
     *
     * @return \DateTime
     */
    public function getGepubliceerd()
    {
        return $this->gepubliceerd;
    }

    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return Nieuwsbericht
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function getEdited()
    {
        return $this->edited;
    }
}
