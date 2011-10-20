<?php

namespace Zpi\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\UserBundle\Entity\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User extends BaseUser
{
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
    protected $title;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;
    
    /**
     * @var string $surname
     *
     * @ORM\Column(name="surname", type="string", length=50, nullable=true)
     */
    protected $surname;
    
    /**
     * @var string $institution
     *
     * @ORM\Column(name="institution", type="string", length=50, nullable=true)
     */
    protected $institution;
    
    /**
     * @var string $address
     *
     * @ORM\Column(name="address", type="string", length=50, length=50, nullable=true)
     */
    protected $address;
    
    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=50, nullable=true)
     */
    protected $city;
    
    /**
     * @var string $postalcode
     *
     * @ORM\Column(name="postalcode", type="string", length=50, nullable=true)
     */
    protected $postalcode;
    
    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=50, nullable=true)
     */
    protected $country;
    
    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=50, length=50, nullable=true)
     */
    protected $phone;
    
    /**
     * type = private participation (0) || invoice for the institution (1)
     * 
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    protected $type;
    
    /**
     * @var string $nipvat
     *
     * @ORM\Column(name="nipvat", type="string", length=50, length=50, nullable=true)
     */
    protected $nipvat;
    
    /**
     * @ORM\ManyToMany(targetEntity="Zpi\PaperBundle\Entity\Paper")
     * @ORM\JoinTable(name="users_papers",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="paper_id", referencedColumnName="id")}
     * )
     */
    
    protected $papers;
    
    /**
     * @ORM\ManyToMany(targetEntity="Zpi\ConferenceBundle\Entity\Conference")
     * @ORM\JoinTable(name="users_conferences",
     * 		joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * 		inverseJoinColumns={@ORM\JoinColumn(name="conference_id", referencedColumnName="id")}
     * )
     */
    protected $conferences;
    
    
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
    
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email); 
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
     * Set county
     *
     * @param string $county
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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
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
     * Add papers
     *
     * @param Zpi\PaperBundle\Entity\Paper $papers
     */
    public function addPaper(\Zpi\PaperBundle\Entity\Paper $papers)
    {
        $this->papers[] = $papers; // spoko papers to obiekt Doctrine\ORM\PersistentCollection (oczywiście framework tak to ubiera, że nic
                                   // nie wiadomo póki sie nie sprawdzi. Ciekawi mnie jak ten obiekt jest zrobiony, że można używać zamiast
                                   // funkcji papers->add(codysm) po prostu papers[] = costam (co jest domeną typu array wbudowanego w php).
                                   // lyzkov: Może jest jakiś mechanizm przeciążania operatorów tak jak to jest w C++?
    }

    /**
     * Get papers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPapers()
    {
        return $this->papers;
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
}