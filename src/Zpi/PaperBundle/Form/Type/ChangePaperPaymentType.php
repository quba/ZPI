<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;


class ChangePaperPaymentType extends AbstractType
{
    private $qb;
    private $papers;
    private $i = 0;
    private $reg_id;
    
    public function __construct(QueryBuilder $qb, $papers, $reg_id)
    {
        $this->qb = $qb;
        $this->papers = $papers;
        $this->reg_id = $reg_id;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $paper = $this->papers[$this->i];
        if (!$paper->isSubmitted())
            return;
        $this->i = ($this->i+1) % count($this->papers);
        $qb = clone $this->qb;
        $qb->andWhere('p.id = :paper_id')->setParameter('paper_id', $paper->getId());
        $registrations = $qb->getQuery()->getResult();
        $ceded = $paper->getCeded();
        $paymentTypeFieldName = 'paymentType';
        $choices = array('Full payment', 'Extra pages', 'Ceded');
        if (empty($registrations))
            unset($choices[2]);
        if (!is_null($ceded))
        {
            if($ceded->getId() == $this->reg_id)
                $paymentTypeFieldName .= 'Ceded';
            else
                unset($choices[2]);
        }
        $builder
            ->add($paymentTypeFieldName, 'choice', array(
            	'label' => 'reg.form.paper.payment_type',
            	'required' => false,
            	'expanded' => true, 'choices' => $choices))
            ->add('registrationCeded', 'entity', array(
    			'label' => 'reg.form.paper.ceded_users',
    			'multiple' => false,
                'class' => 'ZpiConferenceBundle:Registration',
                'query_builder' => $qb));
    }

    public function getName()
    {
        return 'change_paper_payment_type';
    }
    
}