<?php    

namespace Zpi\PaperBundle\Form\Type;

use Zpi\PaperBundle\Entity\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
 
/**
 * Wzorzec formularza dla encji Review.
 * @author lyzkov
 */
class ReviewType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('mark', 'choice', array(
                'label'		=> 'review.form.mark',
                'required' => true,
        		'expanded' => true,
        		'multiple' => false,
        		'choices' => array(
        			Review::MARK_REJECTED => 'review.form.mark.rejected',
        			Review::MARK_CONDITIONALLY_ACCEPTED => 'review.form.mark.conditionally_accepted',
        			Review::MARK_ACCEPTED => 'review.form.mark.accepted')))
            ->add('content', null, array('label' => 'review.form.content'));
    }

    public function getName()
    {
        return 'review';
    }
}