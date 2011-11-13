<?php	
namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
	
/**
 *
 * @ORM\Entity
 * @ORM\Table(name="conferences")
 * @author lyzkov
 */
class Conference
{
	const STATUS_OPEN = 0;
	const STATUS_CLOSED = 1;
	
	private static $status_names = array(Conference::STATUS_OPEN => 'conf.status_open',
										Conference::STATUS_CLOSED => 'conf.status_closed');
	
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;
	
        /**
	 * @ORM\Column(name="prefix", type="string", length=255)
	 */
	private $prefix;
        
	/**
	 * @ORM\Column(name="start_date", type="date")
	 */
	private $startDate;

	/**
	 * @ORM\Column(name="end_date", type="date")
	 */
	private $endDate;
	
	/**
	 * Deadline, po którym nie będzie można już przesłać prac.
	 * @ORM\Column(name="paper_deadline", type="date", nullable=true)
	 */
	private $paperDeadline;
    
        /**
             * Deadline, do którego można przesyłać poprawione wersje pracy.
             * @ORM\Column(name="correctedpaper_deadline", type="date", nullable=true)
             */
        private $correctedPaperDeadline;

        /**
	 * Deadline, po którym nie będzie można już potwierdzić rejestracji.
	 * @ORM\Column(name="confirmation_deadline", type="date", nullable=true)
	 */
	private $confirmationDeadline;
	
	/**
	 * Globalna minimalna ilość stron jaką musi mieć zgłaszany dokument .
	 * @ORM\Column(name="min_page_size", type="integer", nullable=true)
	 */
	private $minPageSize;
	
	/**
	 * Ulica i nr domu konferencji.
	 * @ORM\Column(name="address", type="string", nullable=true)
	 */
	private $address;
	
	/**
	 * Miasto.
	 * @ORM\Column(name="city", type="string", nullable=true)
	 */
	private $city;
	
	/**
	 * Kod pocztowy
	 * @ORM\Column(name="postal", type="string", nullable=true)
	 */
	private $postalCode;
	
	/**
	 * Możliwe wartości: {closed, open}
	 * @ORM\Column(name="status", type="integer")
	 */
	private $status;
	
	/**
	 * Krótki opis konferencji
	 * @var string $description
	 * @ORM\Column(name="description", type="text")
	 */
	private $description;
	
	/**
	 * @ORM\OneToMany(targetEntity="Zpi\ConferenceBundle\Entity\Registration", mappedBy="conference")
	 */
	private $registrations;
    
    /**
	 * Ustalona cena za jeden dzień pobytu
	 * @ORM\Column(name="oneday_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $onedayPrice;
    
    /**
	 * Ustalona cena za referat
	 * @ORM\Column(name="paper_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $paperPrice;
    
    /**
	 * Ustalona cena za referat
	 * @ORM\Column(name="extrapage_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $extrapagePrice;
	

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
     * Set startDate
     *
     * @param date $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return date 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param date $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return date 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    
    /**
     * Set minPageSize
     *
     * @param integer $minPageSize
     */
    public function setMinPageSize($minPageSize)
    {
        $this->minPageSize = $minPageSize;
    }

    /**
     * Get minPageSize
     *
     * @return integer 
     */
    public function getMinPageSize()
    {
        return $this->minPageSize;
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
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
    public function __construct()
    {
        $this->registrations = new \Doctrine\Common\Collections\ArrayCollection();
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
    
    public function __toString()
    {
    	return $this->getPrefix();
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set postalCode
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Get postalCode
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get prefix
     *
     * @return string 
     */
    public function getPrefix()
    {
        return $this->prefix;
    }


    /**
     * Set onedayPrice
     *
     * @param decimal $onedayPrice
     */
    public function setOnedayPrice($onedayPrice)
    {
        $this->onedayPrice = $onedayPrice;
    }

    /**
     * Get onedayPrice
     *
     * @return decimal 
     */
    public function getOnedayPrice()
    {
        return $this->onedayPrice;
    }

    /**
     * Set paperPrice
     *
     * @param decimal $paperPrice
     */
    public function setPaperPrice($paperPrice)
    {
        $this->paperPrice = $paperPrice;
    }

    /**
     * Get paperPrice
     *
     * @return decimal 
     */
    public function getPaperPrice()
    {
        return $this->paperPrice;
    }

    /**
     * Set extrapagePrice
     *
     * @param decimal $extrapagePrice
     */
    public function setExtrapagePrice($extrapagePrice)
    {
        $this->extrapagePrice = $extrapagePrice;
    }

    /**
     * Get extrapagePrice
     *
     * @return decimal 
     */
    public function getExtrapagePrice()
    {
        return $this->extrapagePrice;
    }


    /**
     * Set paperDeadline
     *
     * @param date $paperDeadline
     */
    public function setPaperDeadline($paperDeadline)
    {
        $this->paperDeadline = $paperDeadline;
    }

    /**
     * Get paperDeadline
     *
     * @return date 
     */
    public function getPaperDeadline()
    {
        return $this->paperDeadline;
    }

    /**
     * Set confirmationDeadline
     *
     * @param date $confirmationDeadline
     */
    public function setConfirmationDeadline($confirmationDeadline)
    {
        $this->confirmationDeadline = $confirmationDeadline;
    }

    /**
     * Get confirmationDeadline
     *
     * @return date 
     */
    public function getConfirmationDeadline()
    {
        return $this->confirmationDeadline;
    }

    /**
     * Set correctedPaperDeadline
     *
     * @param date $correctedPaperDeadline
     */
    public function setCorrectedPaperDeadline($correctedPaperDeadline)
    {
        $this->correctedPaperDeadline = $correctedPaperDeadline;
    }

    /**
     * Get correctedPaperDeadline
     *
     * @return date 
     */
    public function getCorrectedPaperDeadline()
    {
        return $this->correctedPaperDeadline;
    }
}
