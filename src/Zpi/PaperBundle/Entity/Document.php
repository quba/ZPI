<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zpi\PaperBundle\Entity\Review as Review;

/**
 * Zpi\PaperBundle\Entity\Document
 * TODO Zrobić porządek ze statusem
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;

    /**
     * @var text $comment
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="pagescount", type="smallint")
     */
    private $pagesCount;

    /**
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(name="upload_date", type="datetime")
     */
    private $uploadDate;

    /**
     * @ORM\Column(name="version", type="integer")
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\PaperBundle\Entity\Paper", inversedBy="documents")
     * @ORM\JoinColumn(name="paper_id", referencedColumnName="id", nullable=false)
     */
    private $paper;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="documents")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

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

    /**
     * Set status
     *
     * @param smallint $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return smallint
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set uploadDate
     *
     * @param date $uploadDate
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;
    }

    /**
     * Get uploadDate
     *
     * @return date
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set version
     *
     * @param integer $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set user
     *
     * @param Zpi\UserBundle\Entity\User $user
     */
    public function setUser(\Zpi\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Zpi\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /* Pobranie najgorszej normalnej review (całego obiektu, bo oprócz oceny może być 
     * potrzebne także imię i nazwisko oceniającego)
     */    
    public function getWorstNormalReview()
    {
        
        $worstReview = $this->reviews[0];        
        foreach($this->reviews as $review)
        {
            // jeżeli jest normalnego typu i jest gorsza od najgorszej dotychczas
            if($review->getType() == Review::TYPE_NORMAL && $review->getMark() < $worstReview->getMark())
            {
                $worstReview = $review;
            }
        }
        return $worstReview;
    }
    
    /* Pobranie najgorszej technicznej review (całego obiektu, bo oprócz oceny może być 
     * potrzebne także imię i nazwisko oceniającego)
     */    
    public function getWorstTechReview()
    {
        $worstReview = $this->reviews[0];        
        foreach($this->reviews as $review)
        {
            // jeżeli jest normalnego typu i jest gorsza od najgorszej dotychczas
            if($review->getType() == Review::TYPE_TECHNICAL && $review->getMark() < $worstReview->getMark())
            {
                $worstReview = $review;
            }
        }
        return $worstReview;
    }
    
}