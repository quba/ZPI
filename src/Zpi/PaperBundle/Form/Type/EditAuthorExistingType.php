<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Zpi\PaperBundle\Form\Type\NewAuthorExistingType as BaseType;

class EditAuthorExistingType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('email');
        //$builder->add('email');
        $builder->add('email', 'email', array('attr' => array('readonly' => 'readonly')));
    }

    public function getName()
    {
        return 'edit_author_existing';
    }
}