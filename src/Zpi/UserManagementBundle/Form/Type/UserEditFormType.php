<?php

namespace Zpi\UserManagementBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Zpi\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserEditFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
       
    }

    public function getName()
    {
        return 'zpi_user_edit';
    }
}