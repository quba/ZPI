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
     * @var string $canonical;
     * 
     * @ORM\Column(name="canonical", type="string", unique="true", length=255)
     */
    private $canonical;


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
    	$canonical = str_replace($polishChars, $englishChars, $title);
    	//$pattern = '/[^A-Za-z0-9_-]+/';
		//$replacement = '-';
		$RemoveChars  = array( '/\s/' , '/[^A-Za-z0-9_-]+/');
    	$ReplaceWith = array("-", ""); 
		$canonical = strtolower(preg_replace($RemoveChars, $ReplaceWith, $canonical));
		$this->setCanonical($canonical);
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
     * Set canonical
     *
     * @param text $canonical
     */
    public function setCanonical($canonical)
    {
        $this->canonical = $canonical;
    }

    /**
     * Get canonical
     *
     * @return text 
     */
    public function getCanonical()
    {
        return $this->canonical;
    }

    
}
