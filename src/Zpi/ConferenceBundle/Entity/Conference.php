<?php	
namespace Zpi\ConferenceBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
	
/**
 *
 * @ORM\Entity
 * @ORM\Table(name="conferences")
 * @ORM\HasLifecycleCallbacks
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
        
    
    // Daty te są potrzebne do wyliczania cen za extra dni
	/**
	 * @ORM\Column(name="start_date", type="datetime")
     * 
     * Czas od kiedy konferencja rezerwuje miejsca hotelowe.
	 */
	private $startDate;

	/**
	 * @ORM\Column(name="end_date", type="datetime")
     * 
     * Data ostatniej rezerwacji miejsca hotelowego. A więc zakończenie konferencji w następny dzień.
	 */
	private $endDate;
    
        
    /**
	 * @ORM\Column(name="bookingstart_date", type="datetime")
     * 
     * Data od kiedy można bookować pobyt na konferencji, np 2 dni przed rozpoczęciem.
	 */
	private $bookingstartDate;
    
    /**
	 * @ORM\Column(name="bookingend_date", type="datetime")
     * 
     * Data do kiedy można bookować pobyt na konferencji, np 2 dni po zakończeniu konferencji.
	 */
	private $bookingendDate;
    
    /**
	 * Deadline, po którym nie będzie można już przesyłać abstraktów prac.
	 * @ORM\Column(name="abstract_deadline", type="datetime", nullable=true)
	 */
	private $abstractDeadline;
	
	/**
	 * Deadline, po którym nie będzie można już przesłać prac.Tzw submission deadline.
	 * @ORM\Column(name="paper_deadline", type="datetime", nullable=true)
	 */
	private $paperDeadline;
    
    /**
      * Deadline, do którego można przesyłać poprawione wersje pracy. Tzw camera-ready papers.
      * @ORM\Column(name="correctedpaper_deadline", type="datetime", nullable=true)
      */
    private $correctedPaperDeadline;

     /**
	 * Deadline, po którym nie będzie można już potwierdzić rejestracji.
	 * @ORM\Column(name="confirmation_deadline", type="datetime", nullable=true)
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
	* @ORM\ManyToMany(targetEntity="Zpi\UserBundle\Entity\User", mappedBy="conferences")
	*/
	private $organizers;
    
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
	 * Cena za conference kit.
	 * @ORM\Column(name="conferencekit_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $conferencekitPrice;
    
    /**
	 * Cena za książkę z konferencji.
	 * @ORM\Column(name="conferencebook_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $conferencebookPrice;
    
    /**
	 * Czy wymagana oplata za wszystkie dni trwania konferencji. 
     * Nie zdołałem wymyślić lepszej nazwy. Nie, nie zmienie jej.
	 * @ORM\Column(name="demand_allday_payment", type="boolean", nullable=true)
	 */    
    private $demandAlldayPayment;
    
    /**
	 * Czy konferencja posiada książkę, którą można kupić.
	 * @ORM\Column(name="contain_book", type="boolean", nullable=true)
	 */    
    private $containBook;
    
    /**
	 * Cena bazowa za full participation.
	 * @ORM\Column(name="fullparticipation_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $fullParticipationPrice;
    
    /**
	 * Cena bazowa za limited participation.
	 * @ORM\Column(name="limitedparticipation_price", type="decimal", scale=2, precision=10, nullable=true)
	 */
    private $limitedParticipationPrice;
	
	 /**
     *
     * @ORM\Column(name="registrationMail_content", type="text", nullable=true)
     */

    private $registrationMailContent;

     /**
     *
     * @ORM\Column(name="confirmationMail_content", type="text", nullable=true)
     */

    private $confirmationMailContent;
    
    /**
     * @ORM\Column(name="logo_path", type="text")
     */
    public $logoPath;
    
    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;

    public function getAbsolutePath()
    {
        return null === $this->logoPath ? null : $this->getUploadRootDir().'/'.$this->logoPath;
    }

    public function getWebPath()
    {
        return null === $this->logoPath ? null : $this->getUploadDir().'/'.$this->logoPath;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory logoPath where uploaded documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/logos';
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name
            $this->logoPath = uniqid().'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does automatically
        $this->file->move($this->getUploadRootDir(), $this->logoPath);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
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

    /**
     * Set abstractDeadline
     *
     * @param datetime $abstractDeadline
     */
    public function setAbstractDeadline($abstractDeadline)
    {
        $this->abstractDeadline = $abstractDeadline;
    }

    /**
     * Get abstractDeadline
     *
     * @return datetime 
     */
    public function getAbstractDeadline()
    {
        return $this->abstractDeadline;
    }

    /**
     * Set bookingstartDate
     *
     * @param datetime $bookingstartDate
     */
    public function setBookingstartDate($bookingstartDate)
    {
        $this->bookingstartDate = $bookingstartDate;
    }

    /**
     * Get bookingstartDate
     *
     * @return datetime 
     */
    public function getBookingstartDate()
    {
        return $this->bookingstartDate;
    }

    /**
     * Set bookingendDate
     *
     * @param datetime $bookingendDate
     */
    public function setBookingendDate($bookingendDate)
    {
        $this->bookingendDate = $bookingendDate;
    }

    /**
     * Get bookingendDate
     *
     * @return datetime 
     */
    public function getBookingendDate()
    {
        return $this->bookingendDate;
    }

    /**
     * Set conferencekitPrice
     *
     * @param decimal $conferencekitPrice
     */
    public function setConferencekitPrice($conferencekitPrice)
    {
        $this->conferencekitPrice = $conferencekitPrice;
    }

    /**
     * Get conferencekitPrice
     *
     * @return decimal 
     */
    public function getConferencekitPrice()
    {
        return $this->conferencekitPrice;
    }

    /**
     * Set conferencebookPrice
     *
     * @param decimal $conferencebookPrice
     */
    public function setConferencebookPrice($conferencebookPrice)
    {
        $this->conferencebookPrice = $conferencebookPrice;
    }

    /**
     * Get conferencebookPrice
     *
     * @return decimal 
     */
    public function getConferencebookPrice()
    {
        return $this->conferencebookPrice;
    }

    /**
     * Set demandAlldayPayment
     *
     * @param boolean $demandAlldayPayment
     */
    public function setDemandAlldayPayment($demandAlldayPayment)
    {
        $this->demandAlldayPayment = $demandAlldayPayment;
    }

    /**
     * Get demandAlldayPayment
     *
     * @return boolean 
     */
    public function getDemandAlldayPayment()
    {
        return $this->demandAlldayPayment;
    }

    /**
     * Set containBook
     *
     * @param boolean $containBook
     */
    public function setContainBook($containBook)
    {
        $this->containBook = $containBook;
    }

    /**
     * Get containBook
     *
     * @return boolean 
     */
    public function getContainBook()
    {
        return $this->containBook;
    }

    /**
     * Set fullParticipationPrice
     *
     * @param decimal $fullParticipationPrice
     */
    public function setFullParticipationPrice($fullParticipationPrice)
    {
        $this->fullParticipationPrice = $fullParticipationPrice;
    }

    /**
     * Get fullParticipationPrice
     *
     * @return decimal 
     */
    public function getFullParticipationPrice()
    {
        return $this->fullParticipationPrice;
    }

    /**
     * Set limitedParticipationPrice
     *
     * @param decimal $limitedParticipationPrice
     */
    public function setLimitedParticipationPrice($limitedParticipationPrice)
    {
        $this->limitedParticipationPrice = $limitedParticipationPrice;
    }
    
    

    /**
     * Get limitedParticipationPrice
     *
     * @return decimal 
     */
    public function getLimitedParticipationPrice()
    {
        return $this->limitedParticipationPrice;
    }
    
    /* Add organizers
     *
     * @param Zpi\UserBundle\Entity\User $organizers
     */
    public function addUser(\Zpi\UserBundle\Entity\User $organizers)
    {
        $this->organizers[] = $organizers;
    }
    
    /**
     * Get organizers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOrganizers()
    {
        return $this->organizers;

    }

     /**
     * Set registrationMailContent
     *
     * @param text $registrationMailContent
     */
    public function setRegistrationMailContent($registrationMailContent)
    {
        $this->registrationMailContent = $registrationMailContent;
    }

    /**
     * Get registrationMailContent
     *
     * @return text
     */
    public function getRegistrationMailContent()
    {
        return $this->registrationMailContent;
    }
	
	    /**
     * Set confirmationMailContent
     *
     * @param text $confirmationMailContent
     */
    public function setConfirmationMailContent($confirmationMailContent)
    {
        $this->confirmationMailContent = $confirmationMailContent;
    }

    /**
     * Get confirmationMailContent
     *
     * @return text 
     */
    public function getConfirmationMailContent()
    {
        return $this->confirmationMailContent;
    }
    
    // Funkcja pobierająca wszystkie uploadnięte prace dla danej konferencji 
    // inne nie są potrzebne do sprawdzania opłat
    public function getSubmittedPapers()
    {
        $submittedPapers = array();
        foreach($this->registrations as $registration)
        {
            foreach($registration->getPapers() as $paper)
            {
                if($paper->isSubmitted())
                    $submittedPapers[] = $paper;
            }
        }
        return $submittedPapers;
    }



    

    /**
     * Set logoPath
     *
     * @param text $logoPath
     */
    public function setLogoPath($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    /**
     * Get logoPath
     *
     * @return text 
     */
    public function getLogoPath()
    {
        return $this->logoPath;
    }
}