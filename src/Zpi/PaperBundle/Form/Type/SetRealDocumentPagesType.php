<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class SetRealDocumentPagesType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $builder->add('realPagesCount', 'number', array('label' => 'document.realpages',
            'required' => false));
    }

    public function getName()
    {
        return 'set_real_document_pages_type';
    }
}