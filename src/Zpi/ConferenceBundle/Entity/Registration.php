<?php

namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zpi\ConferenceBundle\Repository\RegistrationRepository;

/**
 * Zpi\ConferenceBundle\Entity\Registration
 *
 * @ORM\Table(name="registrations")
 * @ORM\Entity
 */
class Registration
{
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
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var datetime $deadline
     *
     * @ORM\Column(name="deadline", type="datetime", nullable=true)
     */
    private $deadline;

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
     * @ORM\JoinTable(name="registrations_paper")
     */
    private $papers;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="registrations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $participant;


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
     * Set deadline
     *
     * @param datetime $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * Get deadline
     *
     * @return datetime 
     */
    public function getDeadline()
    {
        return $this->deadline;
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
}
