<?php

namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class SetAmountPaidType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder->add('amountPaid', 'money', array('label' => 'registration.amountpaid',
            'required' => false, 'currency' => 'PLN'));
    }

    public function getName()
    {
        return 'set_amount_paid_type';
    }
}