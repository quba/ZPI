<?php

namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class ChangeSubmissionDeadlineType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder->add('submissionDeadline', 'datetime', array('label' => 'reg.subdeadline', 
				  'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
                ->add('_token', 'csrf');
    }

    public function getName()
    {
        return 'change_submission_deadline';
    }
}