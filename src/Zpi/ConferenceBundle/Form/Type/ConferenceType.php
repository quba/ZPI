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
			->add('startDate', 'date',
				array('label'	=>	'conf.form.start',
					  'years'	=>	range(date('Y'), date('Y', strtotime('+2 years')))))
			->add('endDate', 'date',
				array('label'	=>	'conf.form.end',
                      'years'	=>	range(date('Y'),date('Y', strtotime('+2 years')))))
			->add('paperDeadline', 'date',
				array('label'	=>	'conf.form.paper_deadline',
                    'years'=>range(date('Y', strtotime('-1 years')),date('Y', strtotime('+2 years')))))
            ->add('correctedPaperDeadline', 'date',
				array('label'	=>	'conf.form.correctedpaper_deadline',
                    'years'=>range(date('Y', strtotime('-1 years')),date('Y', strtotime('+2 years'))))) 
            ->add('confirmationDeadline', 'date',
				array('label'	=>	'conf.form.confirmation_deadline',
                    'years'=>range(date('Y', strtotime('-1 years')),date('Y', strtotime('+2 years'))))) 
			->add('minPageSize', 'integer',
				array(
								'label'	=>	'conf.form.min_page'))
            ->add('paper_price')
			->add('address', 'text',
				array(
								'label'	=>	'conf.form.address'))
			->add('city', 'text',
				array(
								'label'	=>	'conf.form.city'))
			->add('postalCode', 'text',
				array(
								'label'	=>	'conf.form.postal_code'))
			->add('description', 'textarea',
				array(
								'label'	=>	'conf.form.description'))
                        ->add('prefix');
	}
	public function getName()
	{
		return 'conference';
	}
}
