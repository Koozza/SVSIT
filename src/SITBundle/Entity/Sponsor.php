<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="sponsor")
 */
class Sponsor
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
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $website;

    /**
     * @ORM\Column(type="text")
     */
    private $beschrijving;

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
     * @return Sponsor
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
     * Set url
     *
     * @param string $url
     *
     * @return Sponsor
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set beschrijving
     *
     * @param string $beschrijving
     *
     * @return Sponsor
     */
    public function setBeschrijving($beschrijving)
    {
        $this->beschrijving = $beschrijving;

        return $this;
    }

    /**
     * Get beschrijving
     *
     * @return string
     */
    public function getBeschrijving()
    {
        return $this->beschrijving;
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
        return 'uploads/sponsoren';
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
}