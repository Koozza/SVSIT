<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="maatproduct", uniqueConstraints={@ORM\UniqueConstraint(name="product_unique", columns={"product_id", "maat_id"})})
 */
class MaatProduct
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
    private $voorraad;

    /**
     * @ORM\ManyToOne(targetEntity="Product", fetch="LAZY")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Maat", fetch="LAZY")
     * @ORM\JoinColumn(name="maat_id", referencedColumnName="id")
     */
    private $maat;

    /**
     * @ORM\OneToMany(targetEntity="ProductVerzoek", mappedBy="maatproduct")
     */
    private $productverzoeken;



    function __toString()
    {
        return $this->getProduct()->getNaam() . ' ' . $this->getProduct()->getType() . ' maat ' . $this->getMaat();
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
     * Set voorraad
     *
     * @param integer $voorraad
     *
     * @return MaatProduct
     */
    public function setVoorraad($voorraad)
    {
        $this->voorraad = $voorraad;

        return $this;
    }

    /**
     * Get voorraad
     *
     * @return integer
     */
    public function getVoorraad()
    {
        return $this->voorraad;
    }

    /**
     * Set product
     *
     * @param \SITBundle\Entity\Product $product
     *
     * @return MaatProduct
     */
    public function setProduct(\SITBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \SITBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set maat
     *
     * @param \SITBundle\Entity\Maat $maat
     *
     * @return MaatProduct
     */
    public function setMaat(\SITBundle\Entity\Maat $maat = null)
    {
        $this->maat = $maat;

        return $this;
    }

    /**
     * Get maat
     *
     * @return \SITBundle\Entity\Maat
     */
    public function getMaat()
    {
        return $this->maat;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productverzoeken = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add productverzoeken
     *
     * @param \SITBundle\Entity\ProductVerzoek $productverzoeken
     *
     * @return MaatProduct
     */
    public function addProductverzoeken(\SITBundle\Entity\ProductVerzoek $productverzoeken)
    {
        $this->productverzoeken[] = $productverzoeken;
    
        return $this;
    }

    /**
     * Remove productverzoeken
     *
     * @param \SITBundle\Entity\ProductVerzoek $productverzoeken
     */
    public function removeProductverzoeken(\SITBundle\Entity\ProductVerzoek $productverzoeken)
    {
        $this->productverzoeken->removeElement($productverzoeken);
    }

    /**
     * Get productverzoeken
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductverzoeken()
    {
        return $this->productverzoeken;
    }
}
