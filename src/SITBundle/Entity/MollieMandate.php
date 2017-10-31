<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mollie_mandate")
 */
class MollieMandate
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
    private $mandateid;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $method;

    /**
     * @ORM\ManyToOne(targetEntity="MollieCustomer", inversedBy="mandates",cascade={"persist"})
     */
    private $customerId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDatetime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $consumerName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $consumerAccount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $consumerBic;

    /**
     * @ORM\OneToMany(targetEntity="MolliePayment", mappedBy="mandateId")
     */
    private $payments;


    public function __toString() {
        return $this->mandateid . ' (' . $this->status . ')';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set mandateid
     *
     * @param string $mandateid
     *
     * @return MollieMAndate
     */
    public function setMandateid($mandateid)
    {
        $this->mandateid = $mandateid;

        return $this;
    }

    /**
     * Get mandateid
     *
     * @return string
     */
    public function getMandateid()
    {
        return $this->mandateid;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return MollieMAndate
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set method
     *
     * @param string $method
     *
     * @return MollieMAndate
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set createdDatetime
     *
     * @param \DateTime $createdDatetime
     *
     * @return MollieMAndate
     */
    public function setCreatedDatetime($createdDatetime)
    {
        $this->createdDatetime = $createdDatetime;

        return $this;
    }

    /**
     * Get createdDatetime
     *
     * @return \DateTime
     */
    public function getCreatedDatetime()
    {
        return $this->createdDatetime;
    }

    /**
     * Set consumerName
     *
     * @param string $consumerName
     *
     * @return MollieMAndate
     */
    public function setConsumerName($consumerName)
    {
        $this->consumerName = $consumerName;

        return $this;
    }

    /**
     * Get consumerName
     *
     * @return string
     */
    public function getConsumerName()
    {
        return $this->consumerName;
    }

    /**
     * Set consumerAccount
     *
     * @param string $consumerAccount
     *
     * @return MollieMAndate
     */
    public function setConsumerAccount($consumerAccount)
    {
        $this->consumerAccount = $consumerAccount;

        return $this;
    }

    /**
     * Get consumerAccount
     *
     * @return string
     */
    public function getConsumerAccount()
    {
        return $this->consumerAccount;
    }

    /**
     * Set consumerBic
     *
     * @param string $consumerBic
     *
     * @return MollieMAndate
     */
    public function setConsumerBic($consumerBic)
    {
        $this->consumerBic = $consumerBic;

        return $this;
    }

    /**
     * Get consumerBic
     *
     * @return string
     */
    public function getConsumerBic()
    {
        return $this->consumerBic;
    }

    /**
     * Set customerId
     *
     * @param \SITBundle\Entity\MollieCustomer $customerId
     *
     * @return MollieMAndate
     */
    public function setCustomerId(\SITBundle\Entity\MollieCustomer $customerId = null)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return \SITBundle\Entity\MollieCustomer
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Add payment
     *
     * @param \SITBundle\Entity\MolliePayment $payment
     *
     * @return MollieMAndate
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
