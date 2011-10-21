<?php

namespace Zpi\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\PageBundle\Entity\SubPage
 *
 * @ORM\Table(name="subpages")
 * @ORM\Entity
 */
class SubPage
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
}
