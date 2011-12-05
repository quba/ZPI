<?php

namespace Zpi\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\CallbackValidator;
use Symfony\Component\Form\FormValidatorInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;


class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
       parent::buildForm($builder, $options);

        // add your custom field
        $builder->remove('username');
        $builder->remove('plainPassword');
        $builder->add('plainPassword', 'password', array(
        'label' => 'form.user.passwordNew.label'
        ));
        $builder->add('plainPasswordConfirm', 'password', array(
        'label' => 'form.user.passwordConfirm.label',
        'property_path' => false
        ));
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
//        $builder->addValidator(new CallbackValidator(function($form)
//        {
//        if($form['plainPasswordConfirm']->getData() != $form['plainPassword']->getData())
//            {
//        $form['plainPasswordConfirm']->addError(new FormError('form.user.passwordConfirm.validators.equal'));
//        }
//        }));
        
        
    }

    public function getName()
    {
        return 'zpi_user_registration';
    }
}