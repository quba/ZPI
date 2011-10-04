<?php

namespace Zpi\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->remove('username');
        $builder->add('title', 'choice', array('choices' => array(0 => 'Mr.', 1 => 'Ms.', 2 => 'B.Sc.', 3 => 'M.Sc.', 4 => 'Ph.D.', 5 => 'Prof.'))); 
        $builder->add('name');
        $builder->add('surname');
        $builder->add('type', 'choice', array(
    'choices' => array(0 => 'Private participation', 0 => 'Participation for the Institution'),
    'expanded' => true)); // TODO: langi
        $builder->add('institution');
        $builder->add('nipvat');
        $builder->add('address');
        $builder->add('city');
        $builder->add('postalcode');
        $builder->add('country');
        $builder->add('phone');
        
        
    }

    public function getName()
    {
        return 'zpi_user_registration';
    }
}