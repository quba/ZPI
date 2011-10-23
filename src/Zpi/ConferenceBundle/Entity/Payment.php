<?php

namespace Zpi\ConferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zpi\ConferenceBundle\Entity\Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity
 */
class Payment
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
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var string $accountNr
     *
     * @ORM\Column(name="accountNr", type="string")
     */
    private $accountNr;


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
     * Set amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set accountNr
     *
     * @param string $accountNr
     */
    public function setAccountNr($accountNr)
    {
        $this->accountNr = $accountNr;
    }

    /**
     * Get accountNr
     *
     * @return string 
     */
    public function getAccountNr()
    {
        return $this->accountNr;
    }
}