<?php

namespace Zpi\UserManagementBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Zpi\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserEditFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('roles', 'choice', array('choices' => array('ROLE_EDITOR' => 'role.editor', 'ROLE_REVIEWER' => 'role.reviewer', 'ROLE_SUPER_ADMIN' => 'role.super_admin'), 'expanded' => true, 'multiple' => true));
    }

    public function getName()
    {
        return 'zpi_user_edit';
    }
}