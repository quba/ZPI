<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Zpi\PaperBundle\Form\Type\NewPaperType as BaseType;

class EditPaperType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('authorsExisting');
        $builder->add('authorsExisting', 'collection', array(
                'type'          => new EditAuthorExistingType(),
                'allow_add'     => true,
                'allow_delete'  => true,
            ));
        //$builder->add('dueDate', null, array('widget' => 'single_text'));
    }

    public function getName()
    {
        return 'edit_paper';
    }
}