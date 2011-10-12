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
     * @var string $page_title
     *
     * @ORM\Column(name="page_title", type="string", length=255)
     */
    private $page_title;
        
    /**
     * @var text $page_content
     * 
     * @ORM\Column(name="page_content", type="text")
     */
    private $page_content;
    
    /**
     * @var text $page_canonical;
     * 
     * @ORM\Column(name="page_canonical", type="text")
     */
    private $page_canonical;


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
     * Set page_title
     *
     * @param string $pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->page_title = $pageTitle;
    }

    /**
     * Get page_title
     *
     * @return string 
     */
    public function getPageTitle()
    {
        return $this->page_title;
    }

    /**
     * Set page_content
     *
     * @param text $pageContent
     */
    public function setPageContent($pageContent)
    {
        $this->page_content = $pageContent;
    }

    /**
     * Get page_content
     *
     * @return text 
     */
    public function getPageContent()
    {
        return $this->page_content;
    }

    /**
     * Set page_canonical
     *
     * @param text $pageCanonical
     */
    public function setPageCanonical($pageCanonical)
    {
        $this->page_canonical = $pageCanonical;
    }

    /**
     * Get page_canonical
     *
     * @return text 
     */
    public function getPageCanonical()
    {
        return $this->page_canonical;
    }
}
