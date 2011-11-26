<?php

namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MailContent extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('Registration mail');
        $builder->add('Confirmation mail');
    }

}