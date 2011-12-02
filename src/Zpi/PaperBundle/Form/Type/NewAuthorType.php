<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewAuthorType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name')
                ->add('surname')
                ->add('email', 'email', array('label' => 'Email (optional)', 'required' => false));
    }

    public function getName()
    {
        return 'new_author';
    }
}