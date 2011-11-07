<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Zpi\PaperBundle\Entity\Document
 *
 * @ORM\Table(name="documents")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Document
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="filename", type="string")
     * @Assert\NotBlank
     */
    private $fileName;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;
    
    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;
    
    /**
     * @ORM\Column(name="pagescount", type="smallint")
     */
    private $pagesCount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Paper", inversedBy="documents")
     * @ORM\JoinColumn(name="paper_id", referencedColumnName="id", nullable=false)
     */
    private $paper;
    
    /**
     * @ORM\OneToMany(targetEntity="Review", mappedBy="document")
     */
    private $reviews;
    
    
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
     * Set paper
     *
     * @param Zpi\PaperBundle\Entity\Paper $paper
     */
    public function setPaper(\Zpi\PaperBundle\Entity\Paper $paper)
    {
        $this->paper = $paper;
    }

    /**
     * Get paper
     *
     * @return Zpi\PaperBundle\Entity\Paper 
     */
    public function getPaper()
    {
        return $this->paper;
    }
    public function __construct()
    {
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add reviews
     *
     * @param Zpi\PaperBundle\Entity\Review $reviews
     */
    public function addReview(\Zpi\PaperBundle\Entity\Review $reviews)
    {
        $this->reviews[] = $reviews;
    }

    /**
     * Get reviews
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReviews()
    {
        return $this->reviews;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/documents';
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set pagesCount
     *
     * @param smallint $pagesCount
     */
    public function setPagesCount($pagesCount)
    {
        $this->pagesCount = $pagesCount;
    }

    /**
     * Get pagesCount
     *
     * @return smallint 
     */
    public function getPagesCount()
    {
        return $this->pagesCount;
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file)
        {
            $this->setPath(uniqid().'.'.$this->file->guessExtension());
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file)
        {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) 
        {
            unlink($file);
        }
    }


}