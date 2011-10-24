<?php	
namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
	
/**
 *
 * @ORM\Entity
 * @ORM\Table(name="conferences")
 * @author lyzkov
 */
class Conference {
	
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
	 * @ORM\Column(name="start_date", type="date")
	 */
	private $startDate;

	/**
	 * @ORM\Column(name="end_date", type="date")
	 */
	private $endDate;
	
	/**
	 * Globalny deadline po osiągnięciu którego nie można już wysyłać nowych wersji prac.
	 * @ORM\Column(name="deadline", type="date", nullable=true)
	 */
	private $deadline;
	
	/**
	 * Globalna minimalna ilość stron jaką musi mieć zgłaszany dokument .
	 * @ORM\Column(name="min_page_size", type="integer", nullable=true)
	 */
	private $minPageSize;
	
	/**
	 * Dane adresowe konferencji.
	 * @ORM\Column(name="address", type="string", nullable=true)
	 */
	private $address;
	
	/**
	 * Możliwe wartości: {closed, open}
	 * @ORM\Column(name="status", type="integer")
	 */
	private $status;
	
	/**
	 * @ORM\OneToMany(targetEntity="Zpi\ConferenceBundle\Entity\Registration", mappedBy="conference")
	 */
	private $registrations;
	

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
     * Set deadline
     *
     * @param date $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * Get deadline
     *
     * @return date 
     */
    public function getDeadline()
    {
        return $this->deadline;
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
}