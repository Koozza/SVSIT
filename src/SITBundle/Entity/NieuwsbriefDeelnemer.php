<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="nieuwsbrief_deelnemer")
 */
class NieuwsbriefDeelnemer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "Dit is geen geldig E-Mail adres."
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $inschrijfdatum;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $UID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;



    public function __construct()
    {
        $this->inschrijfdatum = new \DateTime();
        $this->active = false;
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
     * Set email
     *
     * @param string $email
     *
     * @return NieuwsbriefDeelnemer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set inschrijfdatum
     *
     * @param \DateTime $inschrijfdatum
     *
     * @return NieuwsbriefDeelnemer
     */
    public function setInschrijfdatum($inschrijfdatum)
    {
        $this->inschrijfdatum = $inschrijfdatum;

        return $this;
    }

    /**
     * Get inschrijfdatum
     *
     * @return \DateTime
     */
    public function getInschrijfdatum()
    {
        return $this->inschrijfdatum;
    }

    /**
     * Set uID
     *
     * @param string $uID
     *
     * @return NieuwsbriefDeelnemer
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
     * Set active
     *
     * @param boolean $active
     *
     * @return NieuwsbriefDeelnemer
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return NieuwsbriefDeelnemer
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
}