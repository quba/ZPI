<?php

namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zpi\ConferenceBundle\Repository\RegistrationRepository;

/**
 * Zpi\ConferenceBundle\Entity\Registration
 *
 * @ORM\Table(name="registrations", uniqueConstraints={@ORM\UniqueConstraint(name="registrations_unique", columns={"user_id", "conference_id"})})
 * @ORM\Entity
 */
class Registration
{
	const TYPE_FULL_PARTICIPATION = 0;
	const TYPE_LIMITED_PARTICIPATION = 1;
	const TYPE_CEDED = 2;
	
	private static $type_names = array(Registration::TYPE_FULL_PARTICIPATION => 'reg.type_full',
									Registration::TYPE_LIMITED_PARTICIPATION => 'reg.type_limited',
									Registration::TYPE_CEDED => 'reg.type_ceded');
	
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint $type
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;

    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startDate;
    
    /**
	 * Czy rejestracja jest potwierdzona.
	 * @ORM\Column(name="arrivalBeforeLunch", type="boolean", nullable=true)
	 */
    private $arrivalBeforeLunch;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;
    
    /**
	 * Czy rejestracja jest potwierdzona.
	 * @ORM\Column(name="leaveBeforeLunch", type="boolean", nullable=true)
	 */
    private $leaveBeforeLunch;

    /**
     * @var datetime $submissionDeadline
     *
     * @ORM\Column(name="submission_deadline", type="datetime", nullable=true)
     * 
     * Prywatny deadline dla danej na przesłanie pracy dla danej rejestracji. 
     * Domyślnie równy deadlinowi konferencji - ułatwia walidację.
     */
    private $submissionDeadline;
    
    /**
     * @var datetime $camerareadyDeadline
     *
     * @ORM\Column(name="cameraready_deadline", type="datetime", nullable=true)
     * 
     * Prywatny deadline dla danej rejestracji na przesłanie zaakceptowanej pracy.
     * Domyślnie równy deadlinowi konferencji.
     */
    private $camerareadyDeadline;

    /**
     * @var text $comment
     *     
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;
    
    /**
     * @ORM\ManyToOne(targetEntity="Conference", inversedBy="registrations")
     * @ORM\JoinColumn(name="conference_id", referencedColumnName="id")
     */
    private $conference;
    
    /**
     * @ORM\OneToOne(targetEntity="Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id")
     */
    private $payment;
    
    /**
     * @ORM\ManyToMany(targetEntity="Zpi\PaperBundle\Entity\Paper", inversedBy="registrations")
     * @ORM\JoinTable(name="registrations_papers")
     */
    private $papers;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="registrations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $participant;
    
    /**
	 * Suma wszystkich oplat dla tej rejestracji
	 * @ORM\Column(name="total_payment", type="decimal", scale=2, nullable=true)
	 */
    private $totalPayment;
    
    /**
	 * Poprawna suma wszystkich oplat dla tej rejestracji
	 * @ORM\Column(name="correcttotal_payment", type="decimal", scale=2, nullable=true)
	 */
    private $correctTotalPayment;
    
    /**
	 * Wniesiona oplata
	 * @ORM\Column(name="amount_paid", type="decimal", scale=2, nullable=true)
	 */
    private $amountPaid;
    
    /**
	 * Czy rejestracja jest potwierdzona.
	 * @ORM\Column(name="confirmed", type="boolean", nullable=true)
	 */
    private $confirmed;
    
    /**
	 * Czy złożył deklarację.
	 * @ORM\Column(name="declared", type="boolean", nullable=true)
	 */
    private $declared;
    
    /**
     * @var text $notes
     *     
     * @ORM\Column(name="notes", type="text", nullable=true)
     */    
    private $notes;
    
    /**
	 * Czy chce książkę z konferencji.
	 * @ORM\Column(name="enable_book", type="boolean", nullable=true)
	 */
    private $enableBook;
    
    /**
	 * Ile chce książek z konferencji.
	 * @ORM\Column(name="book_quantity", type="integer", nullable=true)
	 */
    private $bookQuantity;
    
    /**
	 * Czy rejestracja jest potwierdzona.
	 * @ORM\Column(name="enable_kit", type="boolean", nullable=true)
	 */
    private $enableKit;
    
    


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
    
    public function getReadableType()
    {
    	return Registration::$type_names[$this->getType()];
    }

    /**
     * Set startDate
     *
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return datetime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    // Wyliczenie liczby dni pobytu
    public function getAllDaysCount()
    {
        return intval((date_timestamp_get($this->endDate) 
                - date_timestamp_get($this->startDate))/(24*60*60));
    }
    
    // Wyliczenie liczby extra dni
    // TODO jeśli niepoprawna wartość to 0
    public function getExtraDaysCount()
    {
        $confEndDate = new \DateTime(date('Y-m-d', $this->conference->getEndDate()->getTimestamp())) ;
        $bookingDiff = 0;
        $bookingBefore = intval((date_timestamp_get($this->conference->getStartDate()) 
                - date_timestamp_get($this->startDate))/(24*60*60));
        if($bookingBefore < 0)
                        $bookingBefore = 0;
        $bookingAfter = intval((date_timestamp_get($this->endDate) 
                            - date_timestamp_get($confEndDate->add(new \DateInterval('P1D'))))/(24*60*60));//
        if($bookingAfter < 0)
            $bookingAfter = 0;
        $bookingDiff = $bookingBefore + $bookingAfter;
        
        return $bookingDiff;
    }
    
    // Wyliczenie ceny za extra dni
    public function getExtraDaysPrice()
    {
        return $this->getExtraDaysCount()*$this->conference->getOnedayPrice();
    }
    
    // Wyliczenie ceny za bookowanie w zaleznosci od sposobu w jaki konferencja nalicza ceny
    public function getBookingPrice()
    {
        
        if($this->conference->getDemandAlldayPayment())
        {
            switch($this->type)
            {
                case Registration::TYPE_FULL_PARTICIPATION:
                    
                    return $this->conference->getFullParticipationPrice() + $this->getExtraDaysPrice();
                    break;
                
                case Registration::TYPE_LIMITED_PARTICIPATION:
                    
                    return $this->conference->getLimitedParticipationPrice() + $this->getExtraDaysPrice();
                    break;
                
                // Co jeżeli rejestracja jest zcedowana? @lyzkow - Twoja działka ;)
                case Registration::TYPE_CEDED:
                    break;
                    
            }
        }
        // jeżeli nie wymaga opłaty za wszystkie dni, to policzenie za każdy pojedynczy dzień
        else
            return $this->getAllDaysCount()*$this->conference->getOnedayPrice();
            
    }
    
    
    /**
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
    }    
    
    public function __construct()
    {
        $this->papers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set conference
     *
     * @param Zpi\ConferenceBundle\Entity\Conference $conference
     */
    public function setConference($conference)
    {    	
        $this->conference = $conference;
    }

    /**
     * Get conference
     *
     * @return Zpi\ConferenceBundle\Entity\Conference 
     */
    public function getConference()
    {
        return $this->conference;
    }

    /**
     * Set payment
     *
     * @param Zpi\ConferenceBundle\Entity\Payment $payment
     */
    public function setPayment(\Zpi\ConferenceBundle\Entity\Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get payment
     *
     * @return Zpi\ConferenceBundle\Entity\Payment 
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Add papers
     *
     * @param Zpi\PaperBundle\Entity\Paper $papers
     */
    public function addPaper(\Zpi\PaperBundle\Entity\Paper $papers)
    {
        $this->papers[] = $papers;
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
     * Set participant
     *
     * @param Zpi\UserBundle\Entity\User $participant
     */
    public function setParticipant(\Zpi\UserBundle\Entity\User $participant)
    {
        $this->participant = $participant;
    }

    /**
     * Get participant
     *
     * @return Zpi\UserBundle\Entity\User 
     */
    public function getParticipant()
    {
        return $this->participant;
    }
    
    public function __toString()
    {
    	return $this->getName();
    }

    /**
     * Set totalPayment
     *
     * @param decimal $totalPayment
     */
    public function setTotalPayment($totalPayment)
    {
        $this->totalPayment = $totalPayment;
    }

    /**
     * Get totalPayment
     *
     * @return decimal 
     */
    public function getTotalPayment()
    {
        return $this->totalPayment;
    }

    /**
     * Set amountPaid
     *
     * @param decimal $amountPaid
     */
    public function setAmountPaid($amountPaid)
    {
        $this->amountPaid = $amountPaid;
    }

    /**
     * Get amountPaid
     *
     * @return decimal 
     */
    public function getAmountPaid()
    {
        return $this->amountPaid;
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
     * Set submissionDeadline
     *
     * @param datetime $submissionDeadline
     */
    public function setSubmissionDeadline($submissionDeadline)
    {
        $this->submissionDeadline = $submissionDeadline;
    }

    /**
     * Get submissionDeadline
     *
     * @return datetime 
     */
    public function getSubmissionDeadline()
    {
        return $this->submissionDeadline;
    }

    /**
     * Set camerareadyDeadline
     *
     * @param datetime $camerareadyDeadline
     */
    public function setCamerareadyDeadline($camerareadyDeadline)
    {
        $this->camerareadyDeadline = $camerareadyDeadline;
    }

    /**
     * Get camerareadyDeadline
     *
     * @return datetime 
     */
    public function getCamerareadyDeadline()
    {
        return $this->camerareadyDeadline;
    }

    /**
     * Set notes
     *
     * @param text $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Get notes
     *
     * @return text 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set enableBook
     *
     * @param boolean $enableBook
     */
    public function setEnableBook($enableBook)
    {
        $this->enableBook = $enableBook;
    }

    /**
     * Get enableBook
     *
     * @return boolean 
     */
    public function getEnableBook()
    {
        return $this->enableBook;
    }

    /**
     * Set enableKit
     *
     * @param boolean $enableKit
     */
    public function setEnableKit($enableKit)
    {
        $this->enableKit = $enableKit;
    }

    /**
     * Get enableKit
     *
     * @return boolean 
     */
    public function getEnableKit()
    {
        return $this->enableKit;
    }

    /**
     * Set bookQuantity
     *
     * @param integer $bookQuantity
     */
    public function setBookQuantity($bookQuantity)
    {
        $this->bookQuantity = $bookQuantity;
    }

    /**
     * Get bookQuantity
     *
     * @return integer 
     */
    public function getBookQuantity()
    {
        return $this->bookQuantity;
    }

    /**
     * Set declared
     *
     * @param boolean $declared
     */
    public function setDeclared($declared)
    {
        $this->declared = $declared;
    }

    /**
     * Get declared
     *
     * @return boolean 
     */
    public function getDeclared()
    {
        return $this->declared;
    }

    /**
     * Set arrivalBeforeLunch
     *
     * @param boolean $arrivalBeforeLunch
     */
    public function setArrivalBeforeLunch($arrivalBeforeLunch)
    {
        $this->arrivalBeforeLunch = $arrivalBeforeLunch;
    }

    /**
     * Get arrivalBeforeLunch
     *
     * @return boolean 
     */
    public function getArrivalBeforeLunch()
    {
        return $this->arrivalBeforeLunch;
    }

    /**
     * Set leaveBeforeLunch
     *
     * @param boolean $leaveBeforeLunch
     */
    public function setLeaveBeforeLunch($leaveBeforeLunch)
    {
        $this->leaveBeforeLunch = $leaveBeforeLunch;
    }

    /**
     * Get leaveBeforeLunch
     *
     * @return boolean 
     */
    public function getLeaveBeforeLunch()
    {
        return $this->leaveBeforeLunch;
    }

    /**
     * Set correctTotalPayment
     *
     * @param decimal $correctTotalPayment
     */
    public function setCorrectTotalPayment($correctTotalPayment)
    {
        $this->correctTotalPayment = $correctTotalPayment;
    }

    /**
     * Get correctTotalPayment
     *
     * @return decimal 
     */
    public function getCorrectTotalPayment()
    {
        return $this->correctTotalPayment;
    }
}