<?php


namespace Zpi\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\ResettingFormType as Base;
use Symfony\Component\Form\FormBuilder;

class ConfirmAccFormType extends Base
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('new', 'repeated', array('type' => 'password'));
        
        $child = $builder->create('user', 'form');
        $this->buildUserForm($child, $options);

        $builder
            ->add($child)
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Zpi\UserBundle\Form\Model\ConfirmAcc',
                    );
    }

    public function getName()
    {
        return 'fos_user_createacc';
    }

    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilder $builder
     * @param array $options
     */
    protected function buildUserForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'choice', array('choices' => array(0 => 'register.title.mr', 1 => 'register.title.ms', 2 => 'register.title.bsc', 3 => 'register.title.msc', 4 => 'register.title.phd', 5 => 'register.title.prof'))); 
        $builder->add('name');
        $builder->add('surname');
        $builder->add('type', 'choice', array('choices' => array(0 => 'register.private', 1 => 'register.institution'), 'expanded' => true));
        $builder->add('institution');
        $builder->add('nipvat');
        $builder->add('address');
        $builder->add('city');
        $builder->add('postalcode');
        $builder->add('country');
        $builder->add('phone');
    }
}