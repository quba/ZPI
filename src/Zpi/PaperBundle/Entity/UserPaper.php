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
    const TYPE_AUTHOR = 0;
    const TYPE_EDITOR = 0;
    const TYPE_TECH_EDITOR = 0;
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", cascade={"all"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\PaperBundle\Entity\Paper")
     */
    private $paper;

    /**
     * @var integer $author
     *
     * @ORM\Column(name="author", type="smallint")
     */
    private $author;
    
     /**
     * @var integer $editor
     *
     * @ORM\Column(name="editor", type="smallint")
     */
    private $editor;
    
     /**
     * @var integer $techEditor
     *
     * @ORM\Column(name="tech_editor", type="smallint")
     */
    private $techEditor;

    public function __construct(User $user, Paper $paper, $author, $editor, $techEditor)
    {
        $this->user = $user;
        $this->paper = $paper;
        $this->author = $author;
        $this->editor = $editor;
        $this->techEditor = $techEditor;
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

    /**
     * Get paper title
     *
     * @return Zpi\PaperBundle\Entity\Paper
     */
    public function getTitle()
    {
        return $this->getPaper()->getTitle();
    }

    /**
     * Get paper abstract
     *
     * @return Zpi\PaperBundle\Entity\Paper
     */
    public function getAbstract()
    {
        return $this->getPaper()->getAbstract();
    }
}