<?php

namespace Zpi\PaperBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class ChangePapersPaymentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('papers', 'collection', array(
                'type' => new ChangePaperPaymentType(),
            ))
            ->add('_token', 'csrf');
    }

    public function getName()
    {
        return 'change_papers_payment_type';
    }
}
