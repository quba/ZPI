<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zpi\UserBundle\Entity\User;

/**
 * Zpi\PaperBundle\Entity\UserPaper
 *
 * @ORM\Table(name="users_papers")
 * @ORM\Entity
 */
class UserPaper
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
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\PaperBundle\Entity\Paper")
     */
    private $paper;
    
    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;

    public function __construct(User $user, Paper $paper, $type = 0)
    {
        $this->user = $user;
        $this->paper = $paper;
        $this->type = $type;
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
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param Zpi\PaperBundle\Entity\UserPaper $user
     */
    public function setUser(\Zpi\PaperBundle\Entity\UserPaper $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Zpi\PaperBundle\Entity\UserPaper 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set paper
     *
     * @param Zpi\PaperBundle\Entity\UserPaper $paper
     */
    public function setPaper(\Zpi\PaperBundle\Entity\UserPaper $paper)
    {
        $this->paper = $paper;
    }

    /**
     * Get paper
     *
     * @return Zpi\PaperBundle\Entity\UserPaper 
     */
    public function getPaper()
    {
        return $this->paper;
    }
}