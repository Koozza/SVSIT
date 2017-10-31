<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="productverzoek", uniqueConstraints={@ORM\UniqueConstraint(name="verzoek_unique", columns={"maatproduct_id", "gebruiker_id"})})
 */
class ProductVerzoek
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="MaatProduct", inversedBy="productverzoeken")
     * @ORM\JoinColumn(name="maatproduct_id", referencedColumnName="id")
     */
    private $maatproduct;

    /**
     * @ORM\ManyToOne(targetEntity="Gebruiker", inversedBy="productverzoeken")
     * @ORM\JoinColumn(name="gebruiker_id", referencedColumnName="id")
     */
    private $gebruiker;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datum;



    public function __construct()
    {
        $this->datum = new \DateTime();
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
     * Set maatproduct
     *
     * @param \SITBundle\Entity\MaatProduct $maatproduct
     *
     * @return ProductVerzoek
     */
    public function setMaatproduct(\SITBundle\Entity\MaatProduct $maatproduct = null)
    {
        $this->maatproduct = $maatproduct;
    
        return $this;
    }

    /**
     * Get maatproduct
     *
     * @return \SITBundle\Entity\MaatProduct
     */
    public function getMaatproduct()
    {
        return $this->maatproduct;
    }

    /**
     * Set gebruiker
     *
     * @param \SITBundle\Entity\Gebruiker $gebruiker
     *
     * @return ProductVerzoek
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
     * Set datum
     *
     * @param \DateTime $datum
     *
     * @return ProductVerzoek
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
}
