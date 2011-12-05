<?php

namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class ChangeCameraDeadlineType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder->add('camerareadyDeadline', 'datetime', array('label' => 'reg.camdeadline', 
				  'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
                ->add('_token', 'csrf');
    }

    public function getName()
    {
        return 'change_camera_deadline';
    }
}