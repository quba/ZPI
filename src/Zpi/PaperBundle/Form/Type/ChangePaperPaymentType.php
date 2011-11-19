<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class ChangePaperPaymentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder->add('paymentType', 'choice',array('label' => 'Payment type',
                   'expanded' => true, 'choices' => array(0 => 'Full payment', 1 => 'Extra pages')));
    }

    public function getName()
    {
        return 'change_paper_payment_type';
    }
}