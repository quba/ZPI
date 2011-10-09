<?php

namespace Zpi\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
      //  $builder->remove('user');
        
    }
    
    public function buildUserForm(FormBuilder $builder, array $options)
    {
        parent::buildUserForm($builder, $options);
        $builder->remove('username');
        $builder->add('title', 'choice', array('choices' => array(0 => 'register.title.mr', 1 => 'register.title.ms', 2 => 'register.title.bsc', 3 => 'register.title.msc', 4 => 'register.title.phd', 5 => 'register.title.prof'))); 
        $builder->add('name');
        $builder->add('surname');
        $builder->add('type', 'choice', array('choices' => array(0, 1), 'expanded' => true));
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
        return 'zpi_user_profile';
    }
}