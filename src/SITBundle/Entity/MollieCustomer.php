<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="mollie_customer")
 */
class MollieCustomer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $customerid;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $mode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $metadata;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\OneToOne(targetEntity="Gebruiker", mappedBy="MollieCustomer")
     */
    private $gebruiker;

    /**
     * @ORM\OneToMany(targetEntity="MollieMandate", mappedBy="customerId")
     */
    private $mandates;

    /**
     * @ORM\OneToMany(targetEntity="MolliePayment", mappedBy="customerId")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $payments;


    public function __toString() {
           return $this->customerid . '('.$this->name.')';
    }


    /**
     * MollieCustomer constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime();
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
     * Set customerid
     *
     * @param string $customerid
     *
     * @return MollieCustomer
     */
    public function setCustomerid($customerid)
    {
        $this->customerid = $customerid;

        return $this;
    }

    /**
     * Get customerid
     *
     * @return string
     */
    public function getCustomerid()
    {
        return $this->customerid;
    }

    /**
     * Set mode
     *
     * @param string $mode
     *
     * @return MollieCustomer
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MollieCustomer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return MollieCustomer
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
     * Set metadata
     *
     * @param string $metadata
     *
     * @return MollieCustomer
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return string
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return MollieCustomer
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return MollieCustomer
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
     * Add mandate
     *
     * @param \SITBundle\Entity\MollieMandate $mandate
     *
     * @return MollieCustomer
     */
    public function addMandate(\SITBundle\Entity\MollieMandate $mandate)
    {
        $this->mandates[] = $mandate;

        return $this;
    }

    /**
     * Remove mandate
     *
     * @param \SITBundle\Entity\MollieMandate $mandate
     */
    public function removeMandate(\SITBundle\Entity\MollieMandate $mandate)
    {
        $this->mandates->removeElement($mandate);
    }

    /**
     * Get mandates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMandates()
    {
        return $this->mandates;
    }

    /**
     * Add payment
     *
     * @param \SITBundle\Entity\MolliePayment $payment
     *
     * @return MollieCustomer
     */
    public function addPayment(\SITBundle\Entity\MolliePayment $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \SITBundle\Entity\MolliePayment $payment
     */
    public function removePayment(\SITBundle\Entity\MolliePayment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}