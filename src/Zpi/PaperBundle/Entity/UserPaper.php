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
    const TYPE_EDITOR = 3;
    const TYPE_TECH_EDITOR = 4;
    const TYPE_AUTHOR = 1;
    const TYPE_AUTHOR_EXISTING = 2;
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", cascade={"persist"})
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

    public function __construct(User $user, Paper $paper, $author = 0, $editor = 0, $techEditor = 0)
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

    /**
     * Set author
     *
     * @param smallint $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return smallint 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set editor
     *
     * @param smallint $editor
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
    }

    /**
     * Get editor
     *
     * @return smallint 
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * Set techEditor
     *
     * @param smallint $techEditor
     */
    public function setTechEditor($techEditor)
    {
        $this->techEditor = $techEditor;
    }

    /**
     * Get techEditor
     *
     * @return smallint 
     */
    public function getTechEditor()
    {
        return $this->techEditor;
    }
    
    public function isType($type)
    {
        switch ($type)
        {
            case UserPaper::TYPE_AUTHOR:
                return $this->author;
            case UserPaper::TYPE_EDITOR:
                return $this->editor;
            case UserPaper::TYPE_TECH_EDITOR;
                return $this->techEditor;
        }
    }
}