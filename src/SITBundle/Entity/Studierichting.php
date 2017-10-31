<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="studierichting")
 */
class Studierichting
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
    private $studierichting;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $afkorting;

    /**
     * @ORM\Column(type="boolean")
     */
    private $startjaarRelevant;

    /**
     * @ORM\OneToMany(targetEntity="Gebruiker", mappedBy="studierichting")
     */
    private $gebruikers;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Lidmaatschap", inversedBy="studierichtingen")
     * @ORM\JoinTable(name="studierichting_lidmaatschap",
     *   joinColumns={
     *     @ORM\JoinColumn(name="studierichting_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="lidmaatschap_id", referencedColumnName="id")
     *   }
     * )
     */
    private $lidmaatschappen;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->gebruikers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lidmaatschappen = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->studierichting;
    }

    public function getAantalGebruikers() {
        return count($this->gebruikers);
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
     * Set studierichting
     *
     * @param string $studierichting
     *
     * @return Studierichting
     */
    public function setStudierichting($studierichting)
    {
        $this->studierichting = $studierichting;

        return $this;
    }

    /**
     * Get studierichting
     *
     * @return string
     */
    public function getStudierichting()
    {
        return $this->studierichting;
    }

    /**
     * Add gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return Studierichting
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
     * Set afkorting
     *
     * @param string $afkorting
     *
     * @return Studierichting
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
     * Set startjaarRelevant
     *
     * @param boolean $startjaarRelevant
     *
     * @return Studierichting
     */
    public function setStartjaarRelevant($startjaarRelevant)
    {
        $this->startjaarRelevant = $startjaarRelevant;

        return $this;
    }

    /**
     * Get startjaarRelevant
     *
     * @return boolean
     */
    public function getStartjaarRelevant()
    {
        return $this->startjaarRelevant;
    }

    /**
     * Add lidmaatschappen
     *
     * @param \SITBundle\Entity\Lidmaatschap $lidmaatschappen
     *
     * @return Studierichting
     */
    public function addLidmaatschappen(\SITBundle\Entity\Lidmaatschap $lidmaatschappen)
    {
        $this->lidmaatschappen[] = $lidmaatschappen;

        return $this;
    }

    /**
     * Remove lidmaatschappen
     *
     * @param \SITBundle\Entity\Lidmaatschap $lidmaatschappen
     */
    public function removeLidmaatschappen(\SITBundle\Entity\Lidmaatschap $lidmaatschappen)
    {
        $this->lidmaatschappen->removeElement($lidmaatschappen);
    }

    /**
     * Get lidmaatschappen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLidmaatschappen()
    {
        return $this->lidmaatschappen;
    }
}
