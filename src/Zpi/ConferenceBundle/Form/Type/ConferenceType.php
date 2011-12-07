<?php	
namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

	
/**
 * Formularz dla encji Conference
 *
 * @author lyzkov
 */
class ConferenceType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
        
		$builder
			->add('name', 'text',
				array('label'	=>	'conf.form.name'))         
			->add('startDate', 'datetime',
				array('label' => 'conf.form.accomodation_start',
					  'input'=>'datetime', 'widget' => 	'single_text','date_format'=>'d-m-Y'))
			->add('endDate', 'datetime',
				array('label'	=>	'conf.form.accomodation_end',
                      'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('bookingstartDate', 'datetime',
				array('label'	=>	'conf.form.booking_start',
                      'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('bookingendDate', 'datetime',
				array('label'	=>	'conf.form.booking_end',
                      'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('abstractDeadline', 'datetime',
				array('label'	=>	'conf.form.abstract_deadline',
                      'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('paperDeadline', 'datetime',
				array('label'	=>	'conf.form.paper_deadline',
                    'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('correctedPaperDeadline', 'datetime',
				array('label'	=>	'conf.form.correctedpaper_deadline',
                    'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
            ->add('confirmationDeadline', 'datetime',
				array('label'	=>	'conf.form.confirmation_deadline',
                    'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))
			->add('minPageSize', 'integer',
				array('label'	=>	'conf.form.min_page'))             
            ->add('extrapagePrice', 'number', array('label' => 'conf.form.extrapage_price', 
                    'precision' => 2))           
            ->add('containBook', 'checkbox', array('label' => 'conf.form.contain_book'))
            // ma się wyświetlać tylko po zaznaczeniu powyższego checkboxa
            ->add('conferencebookPrice', 'number', array('label' => 'conf.form.book_price', 
                    'precision' => 2))
            ->add('conferencekitPrice', 'number', array('label' => 'conf.form.kit_price', 
                    'precision' => 2))
            ->add('fullParticipationPrice', 'number', array('label' => 'conf.form.full_price',
                'precision'=>2))
            ->add('limitedParticipationPrice', 'number', array('label' => 'conf.form.limited_price',
                'precision'=>2))
            ->add('onedayPrice', 'number', array('label' => 'conf.form.oneday_price', 
                    'precision' => 2))
            ->add('demandAlldayPayment', 'checkbox', array('label' => 'conf.form.demand_allday_price'))
			->add('address', 'text',
				array('label'	=>	'conf.form.address'))
			->add('city', 'text',
				array(
								'label'	=>	'conf.form.city'))
			->add('postalCode', 'text',
				array(
								'label'	=>	'conf.form.postal_code'))
			->add('description', 'textarea',
				array(
								'label'	=>	'conf.form.description'))
                        ->add('prefix')
                        ->add('file','file', array('label' => 'conf.form.logo',))
            ->add('_token', 'csrf')
            ->add('commentsType', 'choice', array(
                'label' => 'conf.form.comments_type',
                'expanded' => true,
                'multiple' => true,
                'choices' => array(
                    1 => 'conf.form.comments_type.review',
                    2 => 'conf.form.comments_type.document'),
                'preferred_choices' => array(
                    2)))
		;
	}
	public function getName()
	{
		return 'conference';
	}
}
