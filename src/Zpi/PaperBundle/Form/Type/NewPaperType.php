<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewPaperType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title');
        $builder->add('abstract');
        $builder->add('authors', 'collection', array(
                'type'          => new NewAuthorType(),
                'allow_add'     => true,
                'allow_delete'  => true,
            ));
        $builder->add('authorsExisting', 'collection', array(
                'type'          => new NewAuthorExistingType(),
                'allow_add'     => true,
                'allow_delete'  => true,
            ));
        //$builder->add('dueDate', null, array('widget' => 'single_text'));
    }

    public function getName()
    {
        return 'new_paper';
    }
}