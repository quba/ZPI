<?php

namespace Zpi\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Zpi\PaperBundle\Entity\UserPaper;

/**
 * Zpi\UserBundle\Entity\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Zpi\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    const ROLE_EDITOR = 'ROLE_TECHNICAL_REVIEWER';
    const ROLE_TECH_EDITOR = 'ROLE_NORMAL_REVIEWER';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=25, nullable=true)
     */
    private $title;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var string $surname
     *
     * @ORM\Column(name="surname", type="string", length=50, nullable=true)
     */
    private $surname;

    /**
     * @var string $institution
     *
     * @ORM\Column(name="institution", type="string", length=50, nullable=true)
     */
    private $institution;

    /**
     * @var string $address
     *
     * @ORM\Column(name="address", type="string", length=50, length=50, nullable=true)
     */
    private $address;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=50, nullable=true)
     */
    private $city;

    /**
     * @var string $postalcode
     *
     * @ORM\Column(name="postalcode", type="string", length=50, nullable=true)
     */
    private $postalcode;

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=50, nullable=true)
     */
    private $country;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=50, length=50, nullable=true)
     */
    private $phone;

    /**
     * type = private participation (0) || invoice for the institution (1)
     *
     * @var int $type
     *
     * @ORM\Column(name="type", type="smallint", nullable=true)
     */
    private $type;

    /**
     * @var string $nipvat
     *
     * @ORM\Column(name="nipvat", type="string", length=50, length=50, nullable=true)
     */
    private $nipvat;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Paper", mappedBy="owner")
     */
    private $ownedPapers;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\UserPaper", mappedBy="user")
     */
    private $papers;
    
    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Document", mappedBy="user")
     */
    private $documents;

    /**
     * @ORM\ManyToMany(targetEntity="Zpi\ConferenceBundle\Entity\Conference")
     * @ORM\JoinTable(name="users_conferences",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="conference_id", referencedColumnName="id")}
     * )
     */
    private $conferences;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\ConferenceBundle\Entity\Registration", mappedBy="participant")
     */
    private $registrations;

    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Review", mappedBy="editor")
     */
    private $reviews;


    public function __construct()
    {
        parent::__construct();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set institution
     *
     * @param string $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * Get institution
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postalcode
     *
     * @param string $postalcode
     */
    public function setPostalcode($postalcode)
    {
        $this->postalcode = $postalcode;
    }

    /**
     * Get postalcode
     *
     * @return string
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set type
     *
     * @param smallint $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return smallint
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set nipvat
     *
     * @param string $nipvat
     */
    public function setNipvat($nipvat)
    {
        $this->nipvat = $nipvat;
    }

    /**
     * Get nipvat
     *
     * @return string
     */
    public function getNipvat()
    {
        return $this->nipvat;
    }

    /**
     * Add ownedPapers
     *
     * @param Zpi\PaperBundle\Entity\Paper $ownedPapers
     */
    public function addOwnedPaper(\Zpi\PaperBundle\Entity\Paper $ownedPapers)
    {
        $this->ownedPapers[] = $ownedPapers;
    }

    /**
     * Get ownedPapers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOwnedPapers()
    {
        return $this->ownedPapers;
    }

    /**
     * Add authorPapers
     *
     * @param Zpi\PaperBundle\Entity\Paper $paper
     */
    public function addPaper(\Zpi\PaperBundle\Entity\Paper $paper) // tego nie ruszam męcz się po swojemu @lyzkov
    {
        $this->papers[] = new UserPaper($this, $paper, 0);
    }

    /**
     * Get authorPapers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPapers()
    {
        return $this->papers; // jakis warunek where type = 1? // @quba na razie tego nie ruszam bo ci wszystkie odwołania polecą @lyzkov
    }

    /**
     * Zwraca wszystkie papiery użytkownika.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAuthorsPapers()
    {
        $papers = new \Doctrine\Common\Collections\ArrayCollection();
        $papers_up = $this->papers->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_AUTHOR);
        });

        foreach ($papers_up as $up)
        {
            $papers->add($up->getPaper());
        }
        return $papers;
    }
    
    /**
     * Zwraca papier użytkownika o podanym id.
     * @param unknown_type $id
     * @return NULL
     */
    public function getAuthorsPaper($id)
    {
        $paper = $this->papers[$id];
        if ($paper->isType(UserPaper::TYPE_AUTHOR))
        {
            return $paper->getPaper();
        }
        return null;
    }
    
    /**
     * Zwraca wszystkie papiery przypisane użytkownikowi do recenzji.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEditorsPapers()
    {
        $papers = new \Doctrine\Common\Collections\ArrayCollection();
        $papers_up = $this->papers->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_EDITOR);
        });
        
        foreach ($papers_up as $up)
        {
            $papers->add($up->getPaper());
        }
        return $papers;
    }
    
    /**
     * Zwraca papier o zadanym id przypisany użytkownikowi do recenzji.
     * @param unknown_type $id
     * @return NULL
     */
    public function getEditorsPaper($id)
    {
        $paper = $this->papers[$id];
        if ($paper->isType(UserPaper::TYPE_EDITOR))
        {
            return $paper->getPaper();
        }
        return null;
    }
    
    /**
     * Zwraca wszystkie papiery przypisane użytkownikowi do technicznej recenzji.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTechEditorsPapers()
    {
        $papers = new \Doctrine\Common\Collections\ArrayCollection();
        $papers_up = $this->papers->filter(function ($el) {
            return $el->isType(UserPaper::TYPE_TECH_EDITOR);
        });

        foreach ($papers_up as $up)
        {
            $papers->add($up->getPaper());
        }
        return $papers;
    }

    /**
     * Zwraca papier o zadanym id przypisany użytkownikowi do technicznej recenzji.
     * @param unknown_type $id
     * @return NULL
     */
    public function getTechEditorsPaper($id)
    {
        $paper = $this->papers[$id];
        if ($paper->isType(UserPaper::TYPE_TECH_EDITOR))
        {
            return $paper->getPaper();
        }
        return null;
    }


    /**
     * Add conferences
     *
     * @param Zpi\ConferenceBundle\Entity\Conference $conferences
     */
    public function addConference(\Zpi\ConferenceBundle\Entity\Conference $conferences)
    {
        $this->conferences[] = $conferences;
    }

    /**
     * Get conferences
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getConferences()
    {
        return $this->conferences;
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

    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
    }


    /**
     * Add papers
     *
     * @param Zpi\PaperBundle\Entity\UserPaper $papers
     */
    public function addUserPaper(\Zpi\PaperBundle\Entity\UserPaper $papers)
    {
        $this->papers[] = $papers;
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
}