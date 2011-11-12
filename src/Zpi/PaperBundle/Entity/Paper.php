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
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\UserPaper", mappedBy="paper", cascade={"all"}, orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="ownedPapers")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="Zpi\ConferenceBundle\Entity\Registration", mappedBy="papers")
     */
    private $registrations;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="paper")
     */
    private $documents;

    private $authorsFromEmail;


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
        $this->authorsFromEmail = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add author
     *
     * @param Zpi\UserBundle\Entity\User $author
     */
    public function addAuthor(\Zpi\UserBundle\Entity\User $author)
    {
        $this->users[] = new UserPaper($author, $this, 1);
    }
    
    /**
     * Add authorExisting
     *
     * @param Zpi\UserBundle\Entity\User $authorExisting
     */
    public function addAuthorExisting(\Zpi\UserBundle\Entity\User $author)
    {
        $this->users[] = new UserPaper($author, $this, 2);
    }

    /**
     * Add editors
     *
     * @param Zpi\UserBundle\Entity\User $editor
     */
    public function addEditor(\Zpi\UserBundle\Entity\User $editor)
    {
        foreach ($this->users as $user)
        {
            if ($user->getUser()->getId() === $editor->getId())
            {
                $user->setEditor(1);
                return;
            }
        }
        $this->users[] = new UserPaper($editor, $this, 0, 1);
    }
    
    public function delEditor(\Zpi\UserBundle\Entity\User $editor)
    {
        foreach ($this->users as $user)
        {
            if ($user->getUser()->getId() === $editor->getId())
            {
                if ($user->isType(UserPaper::TYPE_AUTHOR) || $user->isType(UserPaper::TYPE_TECH_EDITOR))
                {
                    $user->setEditor(0);
                }
                else
                {
                    $this->users->removeElement($user);
                }
                return;
            }
        }
    }
    
    public function setEditors(\Doctrine\Common\Collections\ArrayCollection $editors)
    {
        $currEditors = $this->getEditors()->toArray();
        $editors = $editors->toArray();
        $diff = array_diff($editors, $currEditors);
        foreach ($diff as $e)
        {
            $this->addEditor($e);
        }
        $diff = array_diff($currEditors, $editors);
        foreach ($diff as $e)
        {
            $this->delEditor($e);
        }
    }

    /**
     * Add technical editors
     *
     * @param Zpi\UserBundle\Entity\User $editor
     */
    public function addTechEditor(\Zpi\UserBundle\Entity\User $editor)
    {
        foreach ($this->users as $user)
        {
            if ($user->getUser()->getId() === $editor->getId())
            {
                $user->setTechEditor(1);
                return;
            }
        }
        $this->users[] = new UserPaper($editor, $this, 0, 0, 1);
    }
    
    public function delTechEditor(\Zpi\UserBundle\Entity\User $editor)
    {
        foreach ($this->users as $user)
        {
            if ($user->getUser()->getId() === $editor->getId())
            {
                if ($user->isType(UserPaper::TYPE_AUTHOR) || $user->isType(UserPaper::TYPE_EDITOR))
                {
                    $user->setTechEditor(0);
                }
                else
                {
                    $this->users->removeElement($user);
                }
                return;
            }
        }
    }
    
    public function setTechEditors(\Doctrine\Common\Collections\ArrayCollection $editors)
    {
        $currEditors = $this->getTechEditors()->toArray();
        $editors = $editors->toArray();
        $diff = array_diff($editors, $currEditors);
        foreach ($diff as $e)
        {
            $this->addTechEditor($e);
        }
        $diff = array_diff($currEditors, $editors);
        foreach ($diff as $e)
        {
            $this->delTechEditor($e);
        }
    }

    /**
     * Get authors
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        $authors = new \Doctrine\Common\Collections\ArrayCollection();
        $authors_up = $this->users->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_AUTHOR);
        });
        
        foreach ($authors_up as $up)
        {
            $authors->add($up->getUser());
        }
        return $authors;
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
        $editors = new \Doctrine\Common\Collections\ArrayCollection();
        $editors_up = $this->users->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_EDITOR);
            });
        
        foreach ($editors_up as $up)
        {
            $editors->add($up->getUser());
        }
        return $editors;
    }

    /**
     * Get editors
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTechEditors()
    {
        $techEditors = new \Doctrine\Common\Collections\ArrayCollection();
        $techEditors_up = $this->users->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_TECH_EDITOR);
        });
        
        foreach ($techEditors_up as $up)
        {
            $techEditors->add($up->getUser());
        }
        return $techEditors;
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
    public function addUserPaper(\Zpi\PaperBundle\Entity\UserPaper $userPaper)
    {
        $this->users[] = $userPaper;
    }

    public function delAuthors()
    {
        //TODO Usuwanie tylko autorów
        $this->users = null;
    }

    public function setAuthorsFromEmail(\Zpi\PaperBundle\Entity\UserPaper $authors)
    {
        $this->authorsFromEmail[] = $authors;
    }

    public function getAuthorsFromEmail()
    {
        return $this->authorsFromEmail;
    }

    public function delAuthorsFromEmail()
    {
        $this->authorsFromEmail = null;
    }
}