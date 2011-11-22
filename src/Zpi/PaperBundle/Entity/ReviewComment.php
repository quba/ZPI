<?php

namespace Zpi\PaperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PaperBundle\Entity\ReviewComment
 *
 * @ORM\Table(name="reviews_comments")
 * @ORM\Entity
 */
class ReviewComment
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
     * @var text $content
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\PaperBundle\Entity\Review", inversedBy="comments")
     * @ORM\JoinColumn(name="review_id", referencedColumnName="id", nullable=false)
     */
    private $review;

    /**
     * @ORM\ManyToOne(targetEntity="Zpi\UserBundle\Entity\User", inversedBy="reviewsComments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;
    
    private $editForm; // nie mam na razie sprytniejszego sposob a łeb mnie już boli od tego myślenia


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
     * Set review
     *
     * @param Zpi\PaperBundle\Entity\Review $review
     */
    public function setReview(\Zpi\PaperBundle\Entity\Review $review)
    {
        $this->review = $review;
    }

    /**
     * Get review
     *
     * @return Zpi\PaperBundle\Entity\Review 
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Set user
     *
     * @param Zpi\UserBundle\Entity\User $user
     */
    public function setUser(\Zpi\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Zpi\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function setEditForm($form)
    {
        $this->editForm = $form;
    }

    public function getEditForm()
    {
        return $this->editForm;
    }
}