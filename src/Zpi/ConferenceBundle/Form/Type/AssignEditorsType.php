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
    private $qb;
    private $qb_tech;
    
    public function __construct($qb, $qb_tech)
    {
        $this->qb = $qb;
        $this->qb_tech = $qb_tech;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('editors', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => 'conf.manage.assign_editors.editors_label',
                'class' => 'ZpiUserBundle:User',
                'query_builder' => $this->qb))
            ->add('techEditors', 'entity', array(
                'multiple' => true,
                'required' => false,
                'label' => 'conf.manage.assign_editors.tech_editors_label',
                'class' => 'ZpiUserBundle:User',
                'query_builder' => $this->qb_tech));
    }

    public function getName()
    {
        return 'assign_editors';
    }

}