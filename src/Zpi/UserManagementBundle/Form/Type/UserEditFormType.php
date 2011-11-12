<?php

namespace Zpi\UserManagementBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Zpi\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserEditFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('roles', 'choice', array('choices' => array('ROLE_TECHNICAL_REVIEWER' => 'role.technical_reviewer',
                                'ROLE_NORMAL_REVIEWER' => 'role.normal_reviewer', 
                                'ROLE_ORGANIZER' => 'role.organizer',
                                'ROLE_SUPER_ADMIN' => 'role.super_admin'), 'expanded' => true, 'multiple' => true));
    }

    public function getName()
    {
        return 'zpi_user_edit';
    }
}