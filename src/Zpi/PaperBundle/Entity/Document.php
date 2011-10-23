<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PaperBundle\Entity\Document
 *
 * @ORM\Table(name="documents")
 * @ORM\Entity
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
     */
    private $fileName;
    
    /**
     * @ORM\Column(name="pagesize", type="smallint")
     */
    private $pageSize;
    
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
}