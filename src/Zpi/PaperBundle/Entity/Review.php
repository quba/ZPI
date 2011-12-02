<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PaperBundle\Entity\Review
 *
 * @ORM\Table(name="reviews", uniqueConstraints={@ORM\UniqueConstraint(name="reviews_unique", columns={"user_id", "document_id", "type"})})
 * @ORM\Entity
 */
class Review
{
	const TYPE_NORMAL = 0;
	const TYPE_TECHNICAL = 1;
	const MARK_REJECTED = 0;
	const MARK_CONDITIONALLY_ACCEPTED = 1;
	const MARK_ACCEPTED = 2;
	const MARK_NO_MARK = 3;
	
	const NOT_APPROVED = 0;
	const APPROVED = 1;
	
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
     * @var smallint $mark
     * 
     * @ORM\Column(name="mark", type="smallint")
     */
    private $mark;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;
    
    /**
     * Data dodania recenzji
     * @var unknown_type
     * 
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * Czy recenzja jest zatwierdzona?
     * @var unknown_type
     * 
     * @ORM\Column(name="approved", type="smallint")
     */
    private $approved;
    
    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="reviews")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=false)
     */
    private $document;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="reviews")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $editor;
    
    /**
     * @ORM\OneToMany(targetEntity="Zpi\PaperBundle\Entity\Comment", mappedBy="review")
     */
    private $comments;

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
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set mark
     *
     * @param smallint $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * Get mark
     *
     * @return smallint 
     */
    public function getMark()
    {
        return $this->mark;
    }
    
    // Pobranie nazwy oceny do wyświetlenia
    public function getMarkText()
    {
        switch($this->mark)
        {
            case Review::MARK_ACCEPTED:
                return 'paper.mark.accepted';
                break;
            case Review::MARK_CONDITIONALLY_ACCEPTED:
                return 'paper.mark.conditionally';
                break;
            case Review::MARK_REJECTED:
                return 'paper.mark.rejected';
                break;
            case Review::MARK_NO_MARK:
                return 'paper.mark.no_mark';
                break;
        }
    }

    /**
     * Set document
     *
     * @param Zpi\PaperBundle\Entity\Document $document
     */
    public function setDocument(\Zpi\PaperBundle\Entity\Document $document)
    {
        $this->document = $document;
    }

    /**
     * Get document
     *
     * @return Zpi\PaperBundle\Entity\Document 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set editor
     *
     * @param Zpi\UserBundle\Entity\User $editor
     */
    public function setEditor(\Zpi\UserBundle\Entity\User $editor)
    {
        $this->editor = $editor;
    }

    /**
     * Get editor
     *
     * @return Zpi\UserBundle\Entity\User 
     */
    public function getEditor()
    {
        return $this->editor;
    }
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add comments
     *
     * @param Zpi\PaperBundle\Entity\ReviewComment $comments
     */
    public function addComment(\Zpi\PaperBundle\Entity\ReviewComment $comment)
    {
        $this->comments[] = $comment;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add comments
     *
     * @param Zpi\PaperBundle\Entity\ReviewComment $comments
     */
    public function addReviewComment(\Zpi\PaperBundle\Entity\ReviewComment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
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