<?php

namespace Zpi\PageBundle\Entity;

use Zpi\ConferenceBundle\Entity\Conference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PageBundle\Entity\SubPage
 *
 * @ORM\Table(name="subpages")
 * @ORM\Entity
 */
class SubPage
{
    const POSITION_TOP = 0;
    const POSITION_LEFT = 1;
    
    // TODO dodanie id konferencji
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zpi\ConferenceBundle\Entity\Conference", inversedBy="subpages")
     * @ORM\JoinColumn(name="conference_id", referencedColumnName="id")
     */
    private $conference;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
        
    /**
     * @var text $content
     * 
     * @ORM\Column(name="content", type="text", nullable="true")
     */
    private $content;
    
    /**
     * @var string $title_canonical;
     * 
     * @ORM\Column(name="title_canonical", type="string", unique="true", length=255)
     */
    private $title_canonical;
    
    /**
     * @var string $position;
     * 
     * @ORM\Column(name="position", type="smallint", nullable="false")
     */
    private $position;


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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {	
    	$polishChars = array("ą","ć","ę","ł","ń","ó","ś","ź","ż","Ą","Ć","Ę","Ł","Ń","Ó","Ś","Ź","Ż");
    	$englishChars = array("a","c","e","l","n","o","s","z","z","A","C","E","L","N","O","S","Z","Z");
    	$titleCanonical = str_replace($polishChars, $englishChars, $title);
    	//$pattern = '/[^A-Za-z0-9_-]+/';
		//$replacement = '-';
		$RemoveChars  = array( '/\s/' , '/[^A-Za-z0-9_-]+/');
    	$ReplaceWith = array("-", ""); 
		$titleCanonical = strtolower(preg_replace($RemoveChars, $ReplaceWith, $titleCanonical));
		$this->setTitleCanonical($titleCanonical);
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
    	
        return $this->title;
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
     * Get title_canonical
     *
     * @return text 
     */
    public function getTitleCanonical()
    {
        return $this->title_canonical;
    }

    

    /**
     * Set title_canonical
     *
     * @param string $titleCanonical
     */
    public function setTitleCanonical($titleCanonical)
    {
        $this->title_canonical = $titleCanonical;
    }

    /**
     * Set position
     *
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set conference
     *
     * @param Zpi\PageBundle\Entity\Conference $conference
     */
    public function setConference(Conference $conference)
    {
        $this->conference = $conference;
    }

    /**
     * Get conference
     *
     * @return Zpi\PageBundle\Entity\Conference 
     */
    public function getConference()
    {
        return $this->conference;
    }
}