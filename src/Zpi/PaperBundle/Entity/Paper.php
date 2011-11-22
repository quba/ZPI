<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zpi\PaperBundle\Entity\Review;
use Zpi\PaperBundle\Entity\Document;

/**
 * Zpi\PaperBundle\Entity\Paper
 *
 * @ORM\Table(name="papers")
 * @ORM\Entity
 */
class Paper
{
    // Typ opłaty za paper, bo może być full, a może być Extra Pages -> inaczej liczona cena
    const PAYMENT_TYPE_FULL = 0;
    const PAYMENT_TYPE_EXTRAPAGES = 1;
    
    private static $paymentType_names = array(Paper::PAYMENT_TYPE_FULL => 'paper.payment_full',
									Paper::PAYMENT_TYPE_EXTRAPAGES => 'paper.paper_extrapages');
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
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\UserPaper", mappedBy="paper", cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Document", mappedBy="paper")
     */
    private $documents;
    
    /**
     * @var smallint $paymentType
     *
     * @ORM\Column(name="payment_type", type="smallint")
     */
    private $paymentType;

    private $authors;
    
    private $authorsExisting;


    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->authorsExisting = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getAuthors()
    {
        return $this->authors;
    }
    public function getAuthorsExisting()
    {
        return $this->authorsExisting;
    }
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }
    public function setAuthorsExisting($authors)
    {
        $this->authorsExisting = $authors;
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
    public function getAuthors2()
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
     * Add users
     *
     * @param Zpi\PaperBundle\Entity\UserPaper $users
     */
    public function addUserPaper(\Zpi\PaperBundle\Entity\UserPaper $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set paymentType
     *
     * @param smallint $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * Get paymentType
     *
     * @return smallint 
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }
    
    // pobranie ostatniego zaakceptowanego dokumentu - najnowszej wersji
    public function getAcceptedDocument()
    {
        $documents = $this->getDocuments();
        $acceptedDocuments = array(); // lista zaakceptowanych dokumentów - po to aby funkcja zwróciła najnowszy :)
        $lastDocument = null; // najnowszy z zaakceptowanych dokumentów
        foreach($documents as $document)
        {
                // najgorsza ocena jest wiazaca
                $worst_technical_mark = Review::MARK_ACCEPTED;
                $worst_normal_mark = Review::MARK_ACCEPTED;
                
                // czy istnieje przynajmniej jedna ocena kazdego typu
                $exist_technical = false;
                $exist_normal = false;
                
                foreach($document->getReviews() as $review)
                {
                    
                    if(!$exist_normal && $review->getType() == Review::TYPE_NORMAL)
                            $exist_normal = true;
                    else if(!$exist_technical && $review->getType() == Review::TYPE_TECHNICAL)
                            $exist_technical = true;
                    
                    if($review->getType() == REVIEW::TYPE_NORMAL && $review->getMark() < $worst_normal_mark)
                    {
                        
                        $worst_normal_mark = $review->getMark();
                    }
                    else if($review->getType() == Review::TYPE_TECHNICAL && $review->getMark() < $worst_technical_mark)
                    {
                        
                        $worst_technical_mark = $review->getMark();
                    }
                }
                // jezeli obydwie najnizsze oceny sa 'accepted' paper jest accepted
                if(($exist_normal && $exist_technical) && $worst_normal_mark == Review::MARK_ACCEPTED 
                        && $worst_technical_mark == Review::MARK_ACCEPTED)
                {
                    $acceptedDocuments[] =  $document;
                }
                
                
                if(sizeof($acceptedDocuments) != 0)                    
                    $lastDocument = $acceptedDocuments[0];
                
                foreach($acceptedDocuments as $acceptedDocument)
                {
                    if($acceptedDocument->getVersion() > $lastDocument->getVersion())
                        $lastDocument = $acceptedDocument;
                }
                return $lastDocument;
        }
    }
    
    //pobranie najnowszego dokumentu
    public function getLastDocument()
    {
        $documents = $this->getDocuments();
        $lastDocument = null;
        if(sizeof($documents) != 0)                    
                    $lastDocument = $documents[0];
        foreach($documents as $document)
        {
            if($document->getVersion() > $lastDocument->getVersion())
                        $lastDocument = $document;
        }
        return $lastDocument;
    }
    
    // sprawdzenie czy dany paper jest zaakceptowany
    public function isAccepted()
    {
       return $this->getAcceptedDocument() != null;
    }
    
    // pobranie liczby stron ostatniej wersji zaakceptowanego dokumentu
    public function getAcceptedDocumentPagesCount()
    {       
        if($this->isAccepted())
            return $this->getAcceptedDocument()->getPagesCount();
        return 0;
    }
    
    // pobranie liczby extra stron ostatniej wersji zaakceptowanego dokumentu
    public function getAcceptedDocumentExtraPagesCount()
    {       
        if(!($this->isAccepted()))
                return 0;
        // konferencja danego papera jest jednoczenie konferencja dowolnej rejestracji tego papera
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        
        return $this->getAcceptedDocumentPagesCount() - $conference->getMinPageSize();
    }
    
    // pobranie liczby stron ostatniej wersji dokumentu
    public function getLastDocumentPagesCount()
    {
        if($this->getLastDocument() != null)
            return $this->getLastDocument()->getPagesCount();
        return 0;
    }
    
    // pobranie liczby stron ostatniej wersji dokumentu
    public function getLastDocumentExtraPagesCount()
    {        
        // konferencja danego papera jest jednoczenie konferencja dowolnej rejestracji tego papera
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        
        if($this->getLastDocumentPagesCount() > $conference->getMinPageSize())
            return $this->getLastDocumentPagesCount() - $conference->getMinPageSize();
        return 0;
    }
    
    // obliczenie ceny za paper - tylko za zaakceptowane papery
    public function getPaperPrice()
    {
        if(!($this->isAccepted()))
                return 0;
        // konferencja danego papera jest jednoczenie konferencja dowolnej rejestracji tego papera
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        
        
        // liczenie ceny w zależności od typu
        switch($this->getPaymentType())
        {
            case Paper::PAYMENT_TYPE_FULL:
                $basePages = $conference->getMinPageSize();
                $extraPages = $this->getAcceptedDocumentPagesCount() - $conference->getMinPageSize();
                $totalPrice = $conference->getPaperPrice() + $extraPages*$conference->getExtrapagePrice();
                
                return $totalPrice;                
                break;
            case Paper::PAYMENT_TYPE_EXTRAPAGES:
                
                return $this->getAcceptedDocumentPagesCount()*$conference->getExtrapagePrice();
                break;                
            
        }
        
        
    }
}