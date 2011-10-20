<?php	
namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
	
/**
 *
 * @ORM\Entity
 * @ORM\Table(name="conference")
 * @author lyzkov
 */
class Conference {
	
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	protected $name;
	
	/**
	 * @ORM\Column(name="start_date", type="date")
	 */
	protected $startDate;

	/**
	 * @ORM\Column(name="end_date", type="date")
	 */
	protected $endDate;
	
	/**
	 * Globalny deadline po osiągnięciu którego nie można już wysyłać nowych wersji prac.
	 * @ORM\Column(name="deadline", type="date")
	 */
	protected $deadline;
	
	/**
	 * Globalna minimalna ilość stron jaką musi mieć zgłaszany dokument .
	 * @ORM\Column(name="min_page_size", type="integer")
	 */
	protected $minPageSize;
	
	/**
	 * Dane adresowe konferencji.
	 * @ORM\Column(name="address", type="string")
	 */
	protected $address;
	
	/**
	 * Możliwe wartości: {closed, open}
	 * @ORM\Column(name="status", type="integer")
	 */
	protected $status;
	

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
}