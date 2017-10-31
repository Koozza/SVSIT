<?php
namespace SITBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SITBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity
 * @ORM\Table(name="foto")
 */
class Foto
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
    private $album;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bestandLocatie;

    protected $file;


    function __toString()
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
        return 'uploads/'.$this->album;
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
     * Set album
     *
     * @param string $album
     *
     * @return Foto
     */
    public function setAlbum($album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get album
     *
     * @return string
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Set bestandLocatie
     *
     * @param string $bestandLocatie
     *
     * @return Foto
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
}
