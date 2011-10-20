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
     * @ORM\ManyToOne(targetEntity="Zpi\PaperBundle\Entity\Paper")
     * @ORM\JoinColumn(name="paper_id", referencedColumnName="id")
     */
    private $paper;
    
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
}