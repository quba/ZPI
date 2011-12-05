<?php

namespace Zpi\PaperBundle\Entity;

use Zpi\ConferenceBundle\Entity\Registration;

use Doctrine\ORM\Mapping as ORM;
use Zpi\PaperBundle\Entity\Review;
use Zpi\PaperBundle\Entity\Document;
use Zpi\UserBundle\Entity\User as User;
use \Doctrine\Common\Collections\ArrayCollection;

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
    const PAYMENT_TYPE_CEDED = 2;
    
    private static $paymentType_names = array(Paper::PAYMENT_TYPE_FULL => 'paper.payment_full',
									Paper::PAYMENT_TYPE_EXTRAPAGES => 'paper.paper_extrapages',
                                    Paper::PAYMENT_TYPE_CEDED => 'paper.paper_ceded');
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
     * @ORM\Column(name="status", type="smallint")
     */
    private $statusNormal;

    /**
     * @ORM\Column(name="status_tech", type="smallint")
     */
    private $statusTech;

    /**
     * @ORM\Column(name="approved", type="smallint")
     */
    private $approved;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\UserPaper", mappedBy="paper", cascade={"persist", "remove"})
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="ownedPapers")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\ConferenceBundle\Entity\Registration", inversedBy="papers")
     * @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=false)
     */
    private $registration;
    
    /**
     * Rejestracja uczestnika który ceduje pracę.
     * @var unknown_type
     * @ORM\ManyToOne(targetEntity="Zpi\ConferenceBundle\Entity\Registration", inversedBy="cededPapers")
     * @ORM\JoinColumn(name="ceded_id", referencedColumnName="id", nullable=true)
     */
    private $ceded;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Document", mappedBy="paper", cascade="delete", cascade={"remove"})     
     *  
     */
    private $documents;
    
    /**
     * @var smallint $paymentType
     *
     * @ORM\Column(name="payment_type", type="smallint")
     */
    private $paymentType;
    
    
    /* Pole pokazujące czy dany paper jest potwierdzony przez kogoś
     * jeśli przykładowo ktoś się wyrejestruje, kto potwierdził, że zapłaci za dany paper
     * to paper ten nie jest już potwierdzony
     */
    
    /**
	 * Czy rejestracja jest potwierdzona.
	 * @ORM\Column(name="confirmed", type="boolean", nullable=true)
	 */
    private $confirmed;

    private $authors;
    
    private $authorsExisting;


    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->authorsExisting = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statusNormal = Review::MARK_NO_MARK;
        $this->statusTech = Review::MARK_NO_MARK;
        $this->approved = Review::NOT_APPROVED;
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
    
//     /**
//      * Add registrations
//      *
//      * @param Zpi\ConferenceBundle\Entity\Registration $registrations
//      */
//     public function addRegistration(\Zpi\ConferenceBundle\Entity\Registration $registrations)
//     {
//         $this->registrations[] = $registrations;
//     }
    
//     public function delRegistration(\Zpi\ConferenceBundle\Entity\Registration $registration)
//     {
//         foreach ($this->registrations as $key => $reg)
//         {
//             if ($reg == $registration)
//             {
//                 unset($this->registrations[$key]);
//                 return;
//             }
//         }
//     }

    /**
     * Get registrations
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getRegistrations()
    {
        $registrations = new ArrayCollection();
        $registrations->add($this->registration);
        return $registrations;
    }
    
    /**
     * Set registrations
     * @param unknown_type $registrations
     */
    public function setRegistrations($registrations)
    {
        if (empty($registrations))
            $this->registration = null;
        else
            $this->registration = $registrations[0];
    }
    
    /**
     * Get registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }
    
    /**
     * Set registration
     * @param Registration $registration
     */
    public function setRegistration(Registration $registration)
    {
        if (is_null($registration) || !is_null($this->registration))
            return;
        $this->registration = $registration;
    }
    
    /**
     * Get registration for ceded
     */
    public function getRegistrationCeded()
    {
        return $this->registration;
    }
    
    /**
     * Set registration for ceded
     * @param Registration $registration
     */
    public function setRegistrationCeded($registration)
    {
        if (is_null($registration) || !is_null($this->registration))
            return;
        $this->registration = $registration;
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
        if ($paymentType == self::PAYMENT_TYPE_CEDED)
        {
            $this->setCeded($this->getRegistration());
            $this->registration = null;
        }
        else
            $this->paymentType = $paymentType;
    }
    
    /**
     * Ustawia typ płatności dla cedowanej pracy
     * 
     * @param unknown_type $paymentType
     */
    public function setPaymentTypeCeded($paymentType)
    {
        if ($paymentType != self::PAYMENT_TYPE_CEDED)
        {
            $this->setRegistration($this->getCeded());
            $this->ceded = null;
            $this->setPaymentType($paymentType);
        }
    }

    /**
     * Pobiera typ płatności - przy podaniu parametru $registration sprawdza
     * czy praca nie jest przez aktualnego użytkownika cedowana na kogoś innego.
     *
     * @param Registration $registration | null
     * @return smallint 
     */
    public function getPaymentType(Registration $registration = null)
    {
        $ceded = $this->getCeded();
        if (is_null($ceded))
            return $this->paymentType;
        if (isset($registration) && $registration == $ceded)
            return self::PAYMENT_TYPE_CEDED;
        return $this->paymentType;
    }
    
    /**
     * Zwraca typ płatności dla cedowanej pracy
     * @return \Zpi\PaperBundle\Entity\smallint|string
     */
    public function getPaymentTypeCeded()
    {
        return self::PAYMENT_TYPE_CEDED;
    }
    
    public function getPaymentTypeText(Registration $registration = null)
    {
        switch($this->getPaymentType($registration))
        {
            case(Paper::PAYMENT_TYPE_FULL):
                return 'paper.payment.full';
            case(Paper::PAYMENT_TYPE_EXTRAPAGES):
                return 'paper.payment.extra';
            case(self::PAYMENT_TYPE_CEDED):
                return 'paper.payment.ceded';
        }
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
                
        }
        return $lastDocument;
    }
    
    //pobranie najnowszego dokumentu
    public function getLastDocument()
    {
        $documents = $this->documents;
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
        
//         if(sizeof($this->documents) != 0)
//         {
//             return $this->getLastDocumentReview()->getMark() == Review::MARK_ACCEPTED &&
//                     $this->getLastDocumentTechReview()->getMark() == Review::MARK_ACCEPTED;
//         }
        return $this->getStatus() == Review::MARK_ACCEPTED;
       
    }
    
    // pobranie liczby stron ostatniej wersji zaakceptowanego dokumentu
    public function getAcceptedDocumentPagesCount()
    {       
        if($this->isAccepted())
            return $this->getAcceptedDocument()->getPagesCount();
        return 0;
    }
    
    // pobranie liczby stron najnowszego dokumentu
    public function getLastDocumentPagesCount()
    {              
        return $this->getLastDocument()->getPagesCount();        
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
    
    // pobranie liczby extra stron ostatniej wersji dokumentu
    public function getLastDocumentExtraPagesCount()
    {        
        // konferencja danego papera jest jednoczenie konferencja dowolnej rejestracji tego papera
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        
        if($this->getLastDocumentPagesCount() > $conference->getMinPageSize())
            return $this->getLastDocumentPagesCount() - $conference->getMinPageSize();
        return 0;
    }
    
    // obliczenie ceny za extra pages - tylko za zaakceptowane papery
    public function getExtraPagesPrice()
    {
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        if(!($this->isAccepted()))
                return 0;
        return $this->getAcceptedDocumentExtraPagesCount()*$conference->getExtrapagePrice();
    }
    
    // obliczenie całĸowitej ceny za paper - tylko za zaakceptowane papery
    public function getPaperPrice(Registration $registration = null)
    {
//         if(!($this->isAccepted()))
//                 return 0;
        // konferencja danego papera jest jednoczenie konferencja dowolnej rejestracji tego papera
        $registrations = $this->getRegistrations();
        $conference = $registrations[0]->getConference();
        
        
        // liczenie ceny w zależności od typu
        switch($this->getPaymentType($registration))
        {
            case Paper::PAYMENT_TYPE_FULL:
                $basePages = $conference->getMinPageSize();
                $extraPages = $this->getAcceptedDocumentPagesCount() - $conference->getMinPageSize();
                $totalPrice = $extraPages*$conference->getExtrapagePrice();
                if(!($this->isFirstFull()))
                    $totalPrice += $conference->getFullParticipationPrice();
                
                return $totalPrice;
            case Paper::PAYMENT_TYPE_EXTRAPAGES:
                
                return $this->getAcceptedDocumentPagesCount()*$conference->getExtrapagePrice();
        }
        return 0;
        
    }

    /**
     * Get status
     *
     * @return smallint 
     */
    public function getStatus()
    {
        $normal = $this->statusNormal;
        $tech = $this->statusTech;
        if ($normal == Review::MARK_NO_MARK || $tech == Review::MARK_NO_MARK)
            return Review::MARK_NO_MARK;
        return min($normal, $tech);
    }
    
    // Sprawdzenie czy dany paper ma jakiś dokument
    public function isSubmitted()
    {
        if(count($this->documents) == 0)
        {
            return false;
        }
        return true;
    }
    
    // pobranie zwykłej review dla ostatniego dokumentu
    public function getLastDocumentReview()
    {
        return $this->getLastDocument()->getWorstNormalReview();
    }
    
    // pobranie technicznej review dla ostatniego dokumentu
    public function getLastDocumentTechReview()
    {
        return $this->getLastDocument()->getWorstTechReview();
    }
    
    // sprawdzenie, czy jest to pierwsza praca opłacana w ramach full
    public function isFirstFull()
    {
        $registrations = $this->getRegistrations();
        foreach($registrations[0]->getPapers() as $paper)
        {
            if($paper->isAccepted() && ($paper->getPaymentType() == Paper::PAYMENT_TYPE_FULL))
            {
                if($paper->getId() == $this->id)
                    return true;
                return false;
            }
        }
        return false;
    }
    

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * Get confirmed
     *
     * @return boolean 
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set ceded
     *
     * @param Zpi\ConferenceBundle\Entity\Registration $ceded
     */
    public function setCeded(\Zpi\ConferenceBundle\Entity\Registration $ceded)
    {
        $this->ceded = $ceded;
    }

    /**
     * Get ceded
     *
     * @return Zpi\ConferenceBundle\Entity\Registration 
     */
    public function getCeded()
    {
        return $this->ceded;
    }
    
    // funkcja pobierająca rejestrację płatnika - na potrzeby listy
    public function getPayer()
    {
        if(isset($this->ceded))
        return $this->ceded != null ? $this->ceded : $this->registration;
    }

    /**
     * Set statusNormal
     *
     * @param smallint $statusNormal
     */
    public function setStatusNormal($statusNormal)
    {
        $this->statusNormal = $statusNormal;
    }

    /**
     * Get statusNormal
     *
     * @return smallint 
     */
    public function getStatusNormal()
    {
        return $this->statusNormal;
    }

    /**
     * Set statusTech
     *
     * @param smallint $statusTech
     */
    public function setStatusTech($statusTech)
    {
        $this->statusTech = $statusTech;
    }

    /**
     * Get statusTech
     *
     * @return smallint 
     */
    public function getStatusTech()
    {
        return $this->statusTech;
    }

    /**
     * Set approved
     *
     * @param smallint $approved
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * Get approved
     *
     * @return smallint 
     */
    public function getApproved()
    {
        return $this->approved;
    }
}