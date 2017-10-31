<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="content_entry")
 */
class ContentEntry
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
    private $UID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $configType;    //Voor Ckeditor 

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $naam;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $string;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bestandLocatie;
    protected $file;


    function __toString()
    {
        return $this->categorie . ' - ' . $this->naam;
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
     * Set categorie
     *
     * @param string $categorie
     *
     * @return ContentEntry
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
    
        return $this;
    }

    /**
     * Get categorie
     *
     * @return string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set naam
     *
     * @param string $naam
     *
     * @return ContentEntry
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
     * Set string
     *
     * @param string $string
     *
     * @return ContentEntry
     */
    public function setString($string)
    {
        $this->string = $string;
    
        return $this;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return ContentEntry
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ContentEntry
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

    /**
     * Set configType
     *
     * @param string $configType
     *
     * @return ContentEntry
     */
    public function setConfigType($configType)
    {
        $this->configType = $configType;
    
        return $this;
    }

    /**
     * Get configType
     *
     * @return string
     */
    public function getConfigType()
    {
        return $this->configType;
    }

    /**
     * Set uID
     *
     * @param string $uID
     *
     * @return ContentEntry
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
}
