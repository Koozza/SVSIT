<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="factuurproduct")
 */
class FactuurProduct
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
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $prijs;

    /**
     * @ORM\Column(type="integer")
     */
    private $btw;

    /**
     * @ORM\ManyToOne(targetEntity="Factuur", inversedBy="producten")
     * @ORM\JoinColumn(name="factuur_id", referencedColumnName="id")
     */
    private $factuur;


    public function __toString()
    {
        return $this->naam . ' ' . $this->type;
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
     * @return FactuurProduct
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
     * Set prijs
     *
     * @param string $prijs
     *
     * @return FactuurProduct
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
     * Set btw
     *
     * @param integer $btw
     *
     * @return FactuurProduct
     */
    public function setBtw($btw)
    {
        $this->btw = $btw;

        return $this;
    }

    /**
     * Get btw
     *
     * @return integer
     */
    public function getBtw()
    {
        return $this->btw;
    }

    /**
     * Set factuur
     *
     * @param \SITBundle\Entity\Factuur $factuur
     *
     * @return FactuurProduct
     */
    public function setFactuur(\SITBundle\Entity\Factuur $factuur = null)
    {
        $this->factuur = $factuur;

        return $this;
    }

    /**
     * Get factuur
     *
     * @return \SITBundle\Entity\Factuur
     */
    public function getFactuur()
    {
        return $this->factuur;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FactuurProduct
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
}
