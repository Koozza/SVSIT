<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="factuurproductkoppeling")
 * @ORM\HasLifecycleCallbacks
 */
class FactuurProductKoppeling
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $aantal;

    /**
     * @ORM\ManyToOne(targetEntity="Maat")
     * @ORM\JoinColumn(name="maat_id", referencedColumnName="id")
     */
    private $maat_original;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $maat;

    /**
     * @ORM\OneToOne(targetEntity="FactuurProduct")
     * @ORM\JoinColumn(name="factuurproduct_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product_original;

    /**
     * @ORM\ManyToOne(targetEntity="Factuur", inversedBy="producten")
     * @ORM\JoinColumn(name="factuur_id", referencedColumnName="id")
     */
    private $factuur;


    public function __toString()
    {
        if($this->getProduct() != null)
            if($this->getMaat() != null)
                return $this->getAantal().'x: '.$this->getProduct()->getNaam() . ' ' . $this->getProduct()->getType().' maat: '.$this->getMaat();
            else
                return $this->getAantal().'x: '.$this->getProduct()->getNaam() . ' ' . $this->getProduct()->getType();
        else
            return 'Empty '.$this->id;
    }

    public function __construct() {
        $this->aantal = 1;
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
     * Set aantal
     *
     * @param integer $aantal
     *
     * @return FactuurProductKoppeling
     */
    public function setAantal($aantal)
    {
        $this->aantal = $aantal;

        return $this;
    }

    /**
     * Get aantal
     *
     * @return integer
     */
    public function getAantal()
    {
        return $this->aantal;
    }

    /**
     * Set product
     *
     * @param \SITBundle\Entity\FactuurProduct $product
     *
     * @return FactuurProductKoppeling
     */
    public function setProduct(\SITBundle\Entity\FactuurProduct $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \SITBundle\Entity\FactuurProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set factuur
     *
     * @param \SITBundle\Entity\Factuur $factuur
     *
     * @return FactuurProductKoppeling
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
     * Set productOriginal
     *
     * @param \SITBundle\Entity\Product $productOriginal
     *
     * @return FactuurProductKoppeling
     */
    public function setProductOriginal(\SITBundle\Entity\Product $productOriginal = null)
    {
        $this->product_original = $productOriginal;

        return $this;
    }

    /**
     * Get productOriginal
     *
     * @return \SITBundle\Entity\Product
     */
    public function getProductOriginal()
    {
        return $this->product_original;
    }

    /**
     * Set maat
     *
     * @param string $maat
     *
     * @return FactuurProductKoppeling
     */
    public function setMaat($maat)
    {
        $this->maat = $maat;

        return $this;
    }

    /**
     * Get maat
     *
     * @return string
     */
    public function getMaat()
    {
        return $this->maat;
    }

    /**
     * Set maatOriginal
     *
     * @param \SITBundle\Entity\Maat $maatOriginal
     *
     * @return FactuurProductKoppeling
     */
    public function setMaatOriginal(\SITBundle\Entity\Maat $maatOriginal = null)
    {
        $this->maat_original = $maatOriginal;

        return $this;
    }

    /**
     * Get maatOriginal
     *
     * @return \SITBundle\Entity\Maat
     */
    public function getMaatOriginal()
    {
        return $this->maat_original;
    }
}
