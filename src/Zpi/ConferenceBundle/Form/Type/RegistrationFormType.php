<?php

namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'choice', 
				  'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 						date('Y', strtotime('+2 years')), 
				    date('Y', strtotime('+3 years')))))
                ->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'choice', 
			      'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 				       date('Y', strtotime('+2 years')), 
			       date('Y', strtotime('+3 years')))))
                ->add('type', 'choice', array('label' => 'reg.form.type', 'choices'=>
					array(0 => 'Limited participation', 1 => 'Full participation'),
					'expanded' => true, ));
			
    }
    
    public function getName()
    {
        return 'new_registration';
    }
}



