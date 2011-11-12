<?php    
namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
    
/**
 *
 *
 * @author lyzkov
 */
class AssignEditorsType extends AbstractType 
{
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('editors', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => 'conf.manage.assign_editors.editors_label',
                'class' => 'ZpiUserBundle:User'))
            ->add('techEditors', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => 'conf.manage.assign_editors.tech_editors_label',
                'class' => 'ZpiUserBundle:User'));
    }

    public function getName()
    {
        return 'assign_editors';
    }

}