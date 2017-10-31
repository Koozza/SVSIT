<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="bestuur")
 */
class Bestuur
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
     * @ORM\Column(type="boolean")
     */
    private $isHuidigeBestuur;

    /**
     * @ORM\OneToMany(targetEntity="Bestuurslid", mappedBy="bestuur")
     */
    private $bestuursleden;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bestandLocatie;

    protected $file;


    public function __toString()
    {
        return $this->naam;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bestuursleden = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * Set bestandLocatie
     *
     * @param string $bestandLocatie
     *
     * @return Sponsor
     */
    public function setBestandLocatie($bestandLocatie)
    {
        $this->bestandLocatie = $bestandLocatie;

        return $this;
    }

    /**
     * Get bestandLocatie
     *
     * @return string
     */
    public function getBestandLocatie()
    {
        return $this->bestandLocatie;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }


    public function getAbsolutePath()
    {
        return null === $this->bestandLocatie ? null : $this->getUploadRootDir().'/'.$this->bestandLocatie;
    }

    public function getWebPath()
    {
        return null === $this->bestandLocatie ? null : "/".$this->getUploadDir().'/'.$this->bestandLocatie;
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/bestuursleden';
    }

    public function delete()
    {
        if(file_exists($this->getAbsolutePath()))
            unlink($this->getAbsolutePath());
    }

    public function upload($basepath)
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        if (null === $basepath) {
            return;
        }


        if ($this->getBestandLocatie() != null){
            $this->delete();
        }

        $filename = substr($this->file->getClientOriginalName(), 0, (strlen($this->file->getClientOriginalExtension()) + 1) * -1).'_'.time().'.'.$this->file->getClientOriginalExtension();

        $this->file->move($this->getUploadRootDir($basepath), $filename);
        $this->setBestandLocatie($filename);

        $this->file = null;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
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
     * @return Bestuur
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
     * Add bestuursleden
     *
     * @param \SITBundle\Entity\Bestuurslid $bestuursleden
     *
     * @return Bestuur
     */
    public function addBestuursleden(\SITBundle\Entity\Bestuurslid $bestuursleden)
    {
        $bestuursleden->setBestuur($this);
        $this->bestuursleden[] = $bestuursleden;
    
        return $this;
    }

    /**
     * Remove bestuursleden
     *
     * @param \SITBundle\Entity\Bestuurslid $bestuursleden
     */
    public function removeBestuursleden(\SITBundle\Entity\Bestuurslid $bestuursleden)
    {
        $this->bestuursleden->removeElement($bestuursleden);
    }

    /**
     * Get bestuursleden
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBestuursleden()
    {
        return $this->bestuursleden;
    }

    public function setBestuursleden($bestuursleden)
    {
        if (count($bestuursleden) > 0) {
            foreach ($bestuursleden as $i) {
                $this->addBestuursleden($i);
            }
        }

        return $this;
    }


    /**
     * Set isHuidigeBestuur
     *
     * @param boolean $isHuidigeBestuur
     *
     * @return Bestuur
     */
    public function setIsHuidigeBestuur($isHuidigeBestuur)
    {
        $this->isHuidigeBestuur = $isHuidigeBestuur;
    
        return $this;
    }

    /**
     * Get isHuidigeBestuur
     *
     * @return boolean
     */
    public function getIsHuidigeBestuur()
    {
        return $this->isHuidigeBestuur;
    }
}
