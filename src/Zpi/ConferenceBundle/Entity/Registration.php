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
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;

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
     * @var text $notes
     *     
     * @ORM\Column(name="notes", type="text", nullable=true)
     */    
    private $notes;


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
}