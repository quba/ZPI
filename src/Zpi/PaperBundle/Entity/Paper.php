<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PaperBundle\Entity\Paper
 *
 * @ORM\Table(name="papers")
 * @ORM\Entity
 */
class Paper
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $abstract
     *
     * @ORM\Column(name="abstract", type="text")
     */
    private $abstract;
    
    /**
     * @ORM\OneToMany(targetEntity="UserPaper", mappedBy="paper")
     */
    private $authors;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="ownedPapers")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id", nullable=false)
     */
    private $owner;
    
    /**
     * @ORM\OneToMany(targetEntity="UserPaper", mappedBy="paper")
     */
    private $editors;
    
    /**
     * @ORM\ManyToMany(targetEntity="Zpi\ConferenceBundle\Entity\Registration", mappedBy="papers")
     */
    private $registrations;
    
    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="paper")
     */
    private $documents;


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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set abstract
     *
     * @param text $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Get abstract
     *
     * @return text 
     */
    public function getAbstract()
    {
        return $this->abstract;
    }
    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
    $this->editors = new \Doctrine\Common\Collections\ArrayCollection();
    $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
    $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add authors
     *
     * @param Zpi\UserBundle\Entity\User $authors
     */
    public function addUser(\Zpi\UserBundle\Entity\User $authors)
    {
        $this->authors[] = $authors;
    }

    /**
     * Get authors
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set owner
     *
     * @param Zpi\UserBundle\Entity\User $owner
     */
    public function setOwner(\Zpi\UserBundle\Entity\User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return Zpi\UserBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get editors
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * Add registrations
     *
     * @param Zpi\ConferenceBundle\Entity\Registration $registrations
     */
    public function addRegistration(\Zpi\ConferenceBundle\Entity\Registration $registrations)
    {
        $this->registrations[] = $registrations;
    }

    /**
     * Get registrations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }

    /**
     * Add documents
     *
     * @param Zpi\PaperBundle\Entity\Document $documents
     */
    public function addDocument(\Zpi\PaperBundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;
    }

    /**
     * Get documents
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }
    
    public function __toString()
    {
    	return $this->getTitle();
    }

    /**
     * Add authors
     *
     * @param Zpi\PaperBundle\Entity\UserPaper $authors
     */
    public function addUserPaper(\Zpi\PaperBundle\Entity\UserPaper $authors)
    {
        $this->authors[] = $authors;
    }
}