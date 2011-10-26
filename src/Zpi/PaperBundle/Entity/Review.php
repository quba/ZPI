<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PaperBundle\Entity\Review
 *
 * @ORM\Table(name="reviews", uniqueConstraints={@ORM\UniqueConstraint(name="reviews_unique", columns={"user_id", "document_id"})})
 * @ORM\Entity
 */
class Review
{
	const TYPE_NORMAL = 0;
	const TYPE_TECHNICAL = 1;
	const MARK_REJECTED = 2;
	const MARK_CONDITIONALLY_ACCEPTED = 3;
	const MARK_ACCEPTED = 4;
	
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
}