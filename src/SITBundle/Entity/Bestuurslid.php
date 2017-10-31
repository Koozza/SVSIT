<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="bestuurslid")
 */
class Bestuurslid
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
    private $functie;

    /**
     * @ORM\Column(type="integer",)
     */
    private $positie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fblink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inlink;

    /**
     * @ORM\Column(type="integer")
     */
    private $l;

    /**
     * @ORM\Column(type="integer")
     */
    private $t;

    /**
     * @ORM\Column(type="integer")
     */
    private $w;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bestandLocatie;

    /**
     * @ORM\ManyToOne(targetEntity="Bestuur", inversedBy="bestuursleden")
     */
    private $bestuur;


    protected $file;


    public function __construct()
    {
        $this->l = 0;
        $this->t = 0;
        $this->w = 0;
    }


    public function __toString()
    {
        return $this->naam;
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
     * Set website
     *
     * @param string $website
     *
     * @return Sponsor
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
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
     * @return Bestuurslid
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
     * Set l
     *
     * @param integer $l
     *
     * @return Bestuurslid
     */
    public function setL($l)
    {
        $this->l = $l;
    
        return $this;
    }

    /**
     * Get l
     *
     * @return integer
     */
    public function getL()
    {
        return $this->l;
    }

    /**
     * Set t
     *
     * @param integer $t
     *
     * @return Bestuurslid
     */
    public function setT($t)
    {
        $this->t = $t;
    
        return $this;
    }

    /**
     * Get t
     *
     * @return integer
     */
    public function getT()
    {
        return $this->t;
    }

    /**
     * Set w
     *
     * @param integer $w
     *
     * @return Bestuurslid
     */
    public function setW($w)
    {
        $this->w = $w;
    
        return $this;
    }

    /**
     * Get w
     *
     * @return integer
     */
    public function getW()
    {
        return $this->w;
    }

    /**
     * Set functie
     *
     * @param string $functie
     *
     * @return Bestuurslid
     */
    public function setFunctie($functie)
    {
        $this->functie = $functie;
    
        return $this;
    }

    /**
     * Get functie
     *
     * @return string
     */
    public function getFunctie()
    {
        return $this->functie;
    }

    /**
     * Set bestuur
     *
     * @param \SITBundle\Entity\Bestuur $bestuur
     *
     * @return Bestuurslid
     */
    public function setBestuur(\SITBundle\Entity\Bestuur $bestuur = null)
    {
        $this->bestuur = $bestuur;
    
        return $this;
    }

    /**
     * Get bestuur
     *
     * @return \SITBundle\Entity\Bestuur
     */
    public function getBestuur()
    {
        return $this->bestuur;
    }

    /**
     * Set fblink
     *
     * @param string $fblink
     *
     * @return Bestuurslid
     */
    public function setFblink($fblink)
    {
        $this->fblink = $fblink;
    
        return $this;
    }

    /**
     * Get fblink
     *
     * @return string
     */
    public function getFblink()
    {
        return $this->fblink;
    }

    /**
     * Set inlink
     *
     * @param string $inlink
     *
     * @return Bestuurslid
     */
    public function setInlink($inlink)
    {
        $this->inlink = $inlink;
    
        return $this;
    }

    /**
     * Get inlink
     *
     * @return string
     */
    public function getInlink()
    {
        return $this->inlink;
    }

    /**
     * Set positie
     *
     * @param integer $positie
     *
     * @return Bestuurslid
     */
    public function setPositie($positie)
    {
        $this->positie = $positie;
    
        return $this;
    }

    /**
     * Get positie
     *
     * @return integer
     */
    public function getPositie()
    {
        return $this->positie;
    }
}
